<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed, ref} from 'vue';
  import {storeToRefs} from 'pinia';
  import {useI18n} from 'vue-i18n';
  import EnergyCostPresetPicker from './energy-cost-preset-picker.vue';
  import EnergyCostTariffPicker from './energy-cost-tariff-picker.vue';
  import EnergyCostPresetForm from './energy-cost-preset-form.vue';
  import {
    availablePresetComponentId,
    cloneTariffComponents,
    componentInputsOutsidePricePeriods,
    commonPresetValidity,
    compatiblePresetComponents,
    dateFromDatetime,
    dateFromPeriodEnd,
    inputsForComponent,
    inputTargetsForComponent,
    legacyComponentId,
    presetDefault,
    pricePeriodsForComponent,
    uniqueComponentId,
  } from './energy-cost-plan-utils';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';
  import {presentTariffPeriod} from './energy-cost-plan-presentation';

  const props = defineProps({
    period: {type: Object, required: true},
    index: Number,
    count: Number,
    errors: {type: Object, required: true},
    tariffSelectable: Boolean,
    pricesOnly: Boolean,
  });
  const emit = defineEmits(['update:period', 'update:boundary', 'remove', 'select-tariff']);
  const store = useEnergyCostStore();
  const {t} = useI18n();
  const {presets, presetDetailsById, tariffs} = storeToRefs(store);
  const timezone = computed(
    () => props.period.components.map((component) => presetDetailsById.value[component.presetId]?.document.timezone).find(Boolean) || 'Europe/Warsaw'
  );
  const componentLabels = {
    ENERGY_PURCHASE: 'Energy purchase',
    DISTRIBUTION_VARIABLE: 'Variable distribution',
    DISTRIBUTION_FIXED: 'Fixed distribution',
    SUPPLIER_FIXED: 'Supplier fixed charge',
  };
  const detailsVisible = ref(props.pricesOnly);
  const selectedTariffId = ref('');
  const summaryDetails = computed(() => presentTariffPeriod(props.period, {presets: presets.value, t}).details);
  const perLabels = {DAY: 'Day', WEEK: 'Week', MONTH: 'Month', YEAR: 'Year', BILLING_PERIOD: 'Billing period'};
  const latestPriceInputs = (component) => {
    const preset = presetDetailsById.value[component.presetId];
    return [
      ...(pricePeriodsForComponent(preset, component.componentId).at(-1)?.inputs || []),
      ...componentInputsOutsidePricePeriods(preset, component.componentId),
    ].filter((input) =>
      inputTargetsForComponent(preset, input, component.componentId).some((target) =>
        /\/(?:rate|rates)(?:\/|$)/.test(typeof target === 'string' ? target : target.pointer)
      )
    );
  };
  const componentInputs = (component) => {
    const preset = presetDetailsById.value[component.presetId];
    return inputsForComponent(preset, component.componentId);
  };
  const inputValue = (component, input) => {
    const preset = presetDetailsById.value[component.presetId];
    const value = Object.hasOwn(component.values || {}, input.id) ? component.values[input.id] : presetDefault(preset, input, component.componentId);
    return input.type === 'CHOICE' ? input.options?.find((option) => option.value === value)?.label || value : value;
  };

  function updateComponent(index, component) {
    const components = [...props.period.components];
    components[index] = component;
    emit('update:period', {...props.period, components});
  }

  async function updatePreset(index, presetId) {
    if (
      Object.keys(props.period.components[index].values || {}).length &&
      !window.confirm(t('Changing the tariff will reset custom pricing settings for this period.'))
    )
      return;
    const preset = presetId ? await store.fetchPreset(presetId) : null;
    const selected = preset?.components?.find(
      (candidate) => candidate.kind === props.period.components[index].kind && candidate.componentId === props.period.components[index].componentId
    );
    const components = props.period.components.map((item, componentIndex) =>
      componentIndex === index ? {...item, presetId, componentId: selected?.componentId || item.componentId, values: {}} : item
    );
    emit('update:period', {
      ...props.period,
      components,
    });
  }

  async function selectTariff(tariffId) {
    if (!tariffId) return;
    if (props.period.components.length) {
      if (!window.confirm(t('Changing the tariff will reset custom pricing settings for this period.'))) return;
    }
    const tariff = await store.fetchTariff(tariffId);
    const details = await Promise.all(cloneTariffComponents(tariff).map((component) => store.fetchPreset(component.presetId)));
    const validity = commonPresetValidity(details);
    if (!validity) return emit('select-tariff', {error: 'The selected tariff has no common validity period.'});
    selectedTariffId.value = tariffId;
    emit('update:period', {
      ...props.period,
      components: cloneTariffComponents(tariff),
    });
    emit('select-tariff', {tariff});
  }

  function addComponent(kind) {
    const componentId = availablePresetComponentId(presets.value, kind, props.period.components);
    const component = componentId
      ? {kind, presetId: '', componentId, values: {}}
      : kind === 'DISTRIBUTION_FIXED' || kind === 'SUPPLIER_FIXED'
        ? {
            kind,
            componentId: uniqueComponentId(legacyComponentId(kind), props.period.components),
            rate: '',
            per: 'BILLING_PERIOD',
            prorate: false,
            taxTreatment: {included: []},
          }
        : null;
    if (!component) return;
    emit('update:period', {...props.period, components: [...props.period.components, component]});
  }

  const canAddComponent = (kind) =>
    kind === 'DISTRIBUTION_FIXED' || kind === 'SUPPLIER_FIXED' || Boolean(availablePresetComponentId(presets.value, kind, props.period.components));

  const requiredComponent = (kind) => kind === 'ENERGY_PURCHASE' || kind === 'DISTRIBUTION_VARIABLE';
  const addableKinds = computed(() =>
    Object.keys(componentLabels).filter((kind) => !requiredComponent(kind) || !props.period.components.some((component) => component.kind === kind))
  );

  function removeComponent(index) {
    if (requiredComponent(props.period.components[index].kind)) return;
    emit('update:period', {...props.period, components: props.period.components.filter((_, componentIndex) => componentIndex !== index)});
  }
</script>

<template>
  <div class="energy-cost-plan-period">
    <div v-if="!pricesOnly && (detailsVisible || count > 1)" class="row">
      <div v-if="index > 0" class="col-sm-6 form-group" :class="{'has-error': errors.validFrom}">
        <label :for="`energy-cost-period-${index}-from`">{{ $t('From date') }}</label>
        <input
          :id="`energy-cost-period-${index}-from`"
          type="date"
          class="form-control"
          :value="dateFromDatetime(period.validFrom, timezone)"
          @input="$emit('update:boundary', 'validFrom', $event.target.value, timezone)"
        />
        <span v-if="errors.validFrom" class="help-block">{{ $t(errors.validFrom) }}</span>
      </div>
      <div v-if="index < count - 1" class="col-sm-6 form-group" :class="{'has-error': errors.validTo}">
        <label :for="`energy-cost-period-${index}-to`">{{ $t('To date') }}</label>
        <input
          :id="`energy-cost-period-${index}-to`"
          type="date"
          class="form-control"
          :value="dateFromPeriodEnd(period.validTo, timezone)"
          @input="$emit('update:boundary', 'validTo', $event.target.value, timezone)"
        />
        <span v-if="errors.validTo" class="help-block">{{ $t(errors.validTo) }}</span>
      </div>
    </div>
    <div v-if="errors.billingCycles" class="text-danger">{{ $t(errors.billingCycles) }}</div>
    <template v-if="!detailsVisible">
      <energy-cost-tariff-picker
        v-if="tariffSelectable"
        :model-value="selectedTariffId"
        :tariffs="tariffs"
        :id-prefix="`energy-cost-period-${index}-initial`"
        @update:model-value="selectTariff"
      />
      <div v-if="errors.components?.[0]?.preset" class="text-danger">{{ $t(errors.components[0].preset) }}</div>
      <div v-if="period.components.length" class="energy-cost-pricing-summary">
        <div
          v-for="(component, componentIndex) in period.components"
          :key="`${component.componentId || component.kind}-${componentIndex}`"
          class="energy-cost-component"
        >
          <h5>{{ $t(componentLabels[component.kind]) }}</h5>
          <template v-if="component.presetId !== undefined">
            <div v-for="input in componentInputs(component)" :key="input.id" class="form-control-static">
              <strong>{{ input.label }}:</strong> {{ inputValue(component, input) }} <small v-if="input.unit">{{ input.unit }}</small>
            </div>
          </template>
          <div v-else class="form-control-static">
            <strong>{{ $t('Rate') }}:</strong> {{ component.rate }} <small v-if="component.per">{{ component.per }}</small>
          </div>
        </div>
      </div>
      <button v-if="period.components.length" type="button" class="btn btn-link btn-sm" @click="detailsVisible = true">
        {{ $t('Customize pricing') }}
      </button>
    </template>
    <template v-else>
      <energy-cost-tariff-picker
        v-if="tariffSelectable"
        :model-value="selectedTariffId"
        :tariffs="tariffs"
        :id-prefix="`energy-cost-period-${index}-base`"
        @update:model-value="selectTariff"
      />
      <component
        :is="pricesOnly ? 'details' : 'div'"
        v-for="(component, componentIndex) in period.components"
        :key="`${component.componentId || component.kind}-${componentIndex}`"
        class="energy-cost-component"
        :class="{'price-component-accordion': pricesOnly}"
      >
        <summary v-if="pricesOnly" class="price-component-summary">
          <span class="price-component-description">
            <strong>{{ summaryDetails[componentIndex].label }}</strong>
            <small class="text-muted">{{ $t('Pricing source') }}: {{ summaryDetails[componentIndex].tariff }}</small>
          </span>
          <span class="price-component-rates">
            <template v-if="component.presetId !== undefined">
              <span v-for="input in latestPriceInputs(component)" :key="input.id">
                {{ input.label }}: <strong>{{ inputValue(component, input) }}</strong> <small v-if="input.unit">{{ input.unit }}</small>
              </span>
            </template>
            <span v-else
              >{{ $t('Rate') }}: <strong>{{ component.rate }}</strong> <small v-if="component.per">/ {{ $t(perLabels[component.per]) }}</small></span
            >
            <small v-if="Object.keys(errors.components?.[componentIndex] || {}).length" class="text-danger">{{ $t('Check the pricing settings.') }}</small>
          </span>
        </summary>
        <div :class="{'price-component-editor': pricesOnly}">
          <div class="clearfix">
            <h5 v-if="!pricesOnly" class="pull-left">{{ $t(componentLabels[component.kind]) }}</h5>
            <button
              v-if="period.components.length > 1 && !requiredComponent(component.kind)"
              type="button"
              class="btn btn-link period-remove pull-right"
              @click="removeComponent(componentIndex)"
            >
              <i class="pe-7s-trash" aria-hidden="true"></i> {{ $t('Remove component') }}
            </button>
          </div>
          <template v-if="component.presetId !== undefined">
            <energy-cost-preset-picker
              :model-value="component.presetId"
              :presets="compatiblePresetComponents(presets, component)"
              :source="presetDetailsById[component.presetId]?.document.source"
              :id-prefix="`energy-cost-period-${index}-${componentIndex}`"
              @update:model-value="updatePreset(componentIndex, $event)"
            />
            <div v-if="errors.components?.[componentIndex]?.preset" class="text-danger">{{ $t(errors.components[componentIndex].preset) }}</div>
            <energy-cost-preset-form
              v-if="presetDetailsById[component.presetId]"
              :component="component"
              :preset="presetDetailsById[component.presetId]"
              :errors="errors.components?.[componentIndex] || {}"
              :id-prefix="`energy-cost-period-${index}-${componentIndex}`"
              @update:values="updateComponent(componentIndex, {...component, values: $event})"
            />
          </template>
          <div v-else class="row">
            <div class="col-sm-5 form-group" :class="{'has-error': errors.components?.[componentIndex]?.rate}">
              <label>{{ $t('Rate') }}</label>
              <input v-model="component.rate" class="form-control" inputmode="decimal" @input="updateComponent(componentIndex, {...component})" />
            </div>
            <div class="col-sm-5 form-group">
              <label>{{ $t('Per') }}</label>
              <select v-model="component.per" class="form-control" @change="updateComponent(componentIndex, {...component})">
                <option value="DAY">{{ $t('Day') }}</option>
                <option value="WEEK">{{ $t('Week') }}</option>
                <option value="MONTH">{{ $t('Month') }}</option>
                <option value="YEAR">{{ $t('Year') }}</option>
                <option value="BILLING_PERIOD">{{ $t('Billing period') }}</option>
              </select>
            </div>
            <div class="col-sm-2 checkbox">
              <label><input v-model="component.prorate" type="checkbox" @change="updateComponent(componentIndex, {...component})" /> {{ $t('Prorate') }}</label>
            </div>
          </div>
        </div>
      </component>
      <div class="btn-group">
        <button
          v-for="kind in addableKinds"
          :key="kind"
          type="button"
          class="btn btn-default btn-sm"
          :disabled="!canAddComponent(kind)"
          @click="addComponent(kind)"
        >
          {{ $t('Add {component}', {component: componentLabels[kind]}) }}
        </button>
      </div>
    </template>
    <button v-if="count > 1" type="button" class="btn btn-link period-remove" @click="$emit('remove')">
      <i class="pe-7s-trash" aria-hidden="true"></i> {{ $t('Remove period') }}
    </button>
  </div>
</template>

<style scoped>
  .period-remove {
    color: #a94442;
  }
  .period-remove:hover,
  .period-remove:focus {
    color: #843534;
  }
  .energy-cost-plan-period > .row {
    margin-right: 0;
    margin-left: 0;
  }
  .energy-cost-component {
    padding: 10px 0;
    border-top: 1px solid #ddd;
  }
  .price-component-accordion {
    padding: 0;
  }
  .price-component-summary {
    position: relative;
    padding: 10px 0 10px 20px;
    cursor: pointer;
    list-style: none;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
  }
  .price-component-summary::-webkit-details-marker {
    display: none;
  }
  .price-component-summary::before {
    content: '›';
    position: absolute;
    left: 0;
    font-size: 20px;
  }
  .price-component-accordion[open] > .price-component-summary::before {
    transform: rotate(90deg);
  }
  .price-component-description,
  .price-component-rates {
    display: flex;
    flex-direction: column;
  }
  .price-component-rates {
    text-align: right;
  }
  .price-component-editor {
    padding: 0 0 10px 20px;
  }
  @media (max-width: 767px) {
    .price-component-summary {
      flex-wrap: wrap;
    }
    .price-component-rates {
      text-align: left;
    }
  }
</style>
