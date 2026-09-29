<script setup>
  import {computed, ref} from 'vue';
  import {storeToRefs} from 'pinia';
  import EnergyCostPresetPicker from './energy-cost-preset-picker.vue';
  import EnergyCostTariffPicker from './energy-cost-tariff-picker.vue';
  import EnergyCostPresetForm from './energy-cost-preset-form.vue';
  import {
    cloneTariffComponents,
    commonPresetValidity,
    compatiblePresetComponents,
    dateFromDatetime,
    dateFromPeriodEnd,
    inputsForComponent,
    presetDefault,
  } from './energy-cost-plan-utils';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';

  const props = defineProps({
    period: {type: Object, required: true},
    index: Number,
    count: Number,
    errors: {type: Object, required: true},
    tariffSelectable: Boolean,
  });
  const emit = defineEmits(['update:period', 'update:boundary', 'remove', 'select-tariff']);
  const store = useEnergyCostStore();
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
  const componentIds = {
    ENERGY_PURCHASE: 'energy-purchase',
    DISTRIBUTION_VARIABLE: 'distribution-variable',
    DISTRIBUTION_FIXED: 'distribution-fixed',
    SUPPLIER_FIXED: 'supplier-fixed',
  };
  const detailsVisible = ref(false);
  const selectedTariffId = ref('');
  const presetComponentIndex = (component, preset) =>
    preset?.document.billingDefinitionTemplate?.periods?.[0]?.components?.findIndex((item) => item.id === component.componentId) ?? -1;
  const componentInputs = (component) => {
    const preset = presetDetailsById.value[component.presetId];
    return inputsForComponent(preset, presetComponentIndex(component, preset));
  };
  const inputValue = (component, input) => {
    const preset = presetDetailsById.value[component.presetId];
    const index = presetComponentIndex(component, preset);
    const value = Object.hasOwn(component.values || {}, input.id) ? component.values[input.id] : presetDefault(preset, input, index);
    return input.type === 'CHOICE' ? input.options?.find((option) => option.value === value)?.label || value : value;
  };

  function updateComponent(index, component) {
    const components = [...props.period.components];
    components[index] = component;
    emit('update:period', {...props.period, components});
  }

  async function updatePreset(index, presetId) {
    const preset = presetId ? await store.fetchPreset(presetId) : null;
    const selected = preset?.components?.find(
      (candidate) => candidate.kind === props.period.components[index].kind && candidate.componentId === props.period.components[index].componentId
    );
    const components = props.period.components.map((item, componentIndex) =>
      componentIndex === index ? {...item, presetId, componentId: selected?.componentId || item.componentId, values: {}} : item
    );
    emit('update:period', {
      ...props.period,
      validFrom: props.period.validFrom || preset?.document.validFrom || '',
      validTo: props.period.validTo || preset?.document.validTo || '',
      components,
    });
  }

  async function selectTariff(tariffId) {
    if (!tariffId) return;
    if (props.period.components.length) {
      if (!window.confirm('Changing the tariff will reset custom pricing settings for this period.')) return;
    }
    const tariff = await store.fetchTariff(tariffId);
    const details = await Promise.all(cloneTariffComponents(tariff).map((component) => store.fetchPreset(component.presetId)));
    const validity = commonPresetValidity(details);
    if (!validity) return emit('select-tariff', {error: 'The selected tariff has no common validity period.'});
    selectedTariffId.value = tariffId;
    emit('update:period', {
      ...props.period,
      validFrom: props.index === 0 ? null : props.period.validFrom || validity.validFrom,
      validTo: props.index === props.count - 1 ? null : props.period.validTo || validity.validTo,
      components: cloneTariffComponents(tariff),
    });
    emit('select-tariff', {tariff});
  }

  function addComponent(kind) {
    const component =
      kind === 'DISTRIBUTION_FIXED' || kind === 'SUPPLIER_FIXED'
        ? {kind, rate: '', per: 'BILLING_PERIOD', prorate: false, taxTreatment: {included: []}}
        : {kind, presetId: '', componentId: componentIds[kind], values: {}};
    emit('update:period', {...props.period, components: [...props.period.components, component]});
  }

  function removeComponent(index) {
    emit('update:period', {...props.period, components: props.period.components.filter((_, componentIndex) => componentIndex !== index)});
  }
</script>

<template>
  <div class="energy-cost-plan-period">
    <div v-if="detailsVisible || count > 1" class="row">
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
      <div
        v-for="(component, componentIndex) in period.components"
        :key="`${component.componentId || component.kind}-${component.presetId || 'inline'}-${componentIndex}`"
        class="energy-cost-component"
      >
        <div class="clearfix">
          <h5 class="pull-left">{{ $t(componentLabels[component.kind]) }}</h5>
          <button v-if="period.components.length > 1" type="button" class="btn btn-link text-danger pull-right" @click="removeComponent(componentIndex)">
            {{ $t('Remove component') }}
          </button>
        </div>
        <template v-if="component.presetId !== undefined">
          <energy-cost-preset-picker
            :model-value="component.presetId"
            :presets="compatiblePresetComponents(presets, component)"
            :id-prefix="`energy-cost-period-${index}-${component.kind}`"
            @update:model-value="updatePreset(componentIndex, $event)"
          />
          <div v-if="errors.components?.[componentIndex]?.preset" class="text-danger">{{ $t(errors.components[componentIndex].preset) }}</div>
          <energy-cost-preset-form
            v-if="presetDetailsById[component.presetId] && presetComponentIndex(component, presetDetailsById[component.presetId]) >= 0"
            :component="component"
            :component-index="presetComponentIndex(component, presetDetailsById[component.presetId])"
            :preset="presetDetailsById[component.presetId]"
            :errors="errors.components?.[componentIndex] || {}"
            :id-prefix="`energy-cost-period-${index}-${component.kind}`"
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
      <div class="btn-group">
        <button v-for="kind in Object.keys(componentLabels)" :key="kind" type="button" class="btn btn-default btn-sm" @click="addComponent(kind)">
          {{ $t('Add {component}', {component: componentLabels[kind]}) }}
        </button>
      </div>
    </template>
    <button v-if="count > 1" type="button" class="btn btn-link text-danger" @click="$emit('remove')">{{ $t('Remove period') }}</button>
  </div>
</template>

<style scoped>
  .energy-cost-plan-period > .row {
    margin-right: 0;
    margin-left: 0;
  }
  .energy-cost-component {
    padding: 10px 0;
    border-top: 1px solid #ddd;
  }
</style>
