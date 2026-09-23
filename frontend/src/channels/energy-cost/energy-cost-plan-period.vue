<script setup>
  import {computed} from 'vue';
  import {storeToRefs} from 'pinia';
  import EnergyCostPresetPicker from './energy-cost-preset-picker.vue';
  import EnergyCostPresetForm from './energy-cost-preset-form.vue';
  import {dateFromDatetime, dateFromPeriodEnd} from './energy-cost-plan-utils';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';

  const props = defineProps({period: {type: Object, required: true}, index: Number, count: Number, errors: {type: Object, required: true}});
  const emit = defineEmits(['update:period', 'update:boundary', 'remove']);
  const store = useEnergyCostStore();
  const {presets, presetDetailsById} = storeToRefs(store);
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
  const componentKinds = Object.fromEntries(Object.entries(componentIds).map(([kind, componentId]) => [componentId, kind]));
  const presetComponentIndex = (component, preset) =>
    preset?.document.billingDefinitionTemplate?.periods?.[0]?.components?.findIndex((item) => item.id === component.componentId) ?? -1;

  function updateComponent(index, component) {
    const components = [...props.period.components];
    components[index] = component;
    emit('update:period', {...props.period, components});
  }

  async function updatePreset(index, presetId) {
    const preset = presetId ? await store.fetchPreset(presetId) : null;
    const component = props.period.components[index];
    updateComponent(index, {...component, presetId, values: {}});
    if (preset && (!props.period.validFrom || !props.period.validTo)) {
      emit('update:period', {
        ...props.period,
        validFrom: props.period.validFrom || preset.document.validFrom,
        validTo: props.period.validTo || preset.document.validTo,
        components: props.period.components.map((item, componentIndex) => (componentIndex === index ? {...item, presetId, values: {}} : item)),
      });
    }
  }

  async function initializeComponents(presetId) {
    const preset = presetId ? await store.fetchPreset(presetId) : null;
    const components =
      preset?.document.billingDefinitionTemplate?.periods?.[0]?.components
        ?.filter((component) => componentKinds[component.id])
        .map((component) => ({kind: componentKinds[component.id], presetId, componentId: component.id, values: {}})) || [];
    emit('update:period', {
      ...props.period,
      validFrom: props.period.validFrom || preset?.document.validFrom || '',
      validTo: props.period.validTo || preset?.document.validTo || '',
      components,
    });
  }

  function addComponent(kind) {
    const component =
      kind === 'DISTRIBUTION_FIXED' || kind === 'SUPPLIER_FIXED'
        ? {kind, rate: '', per: 'BILLING_PERIOD', prorate: false}
        : {kind, presetId: '', componentId: componentIds[kind], values: {}};
    emit('update:period', {...props.period, components: [...props.period.components, component]});
  }

  function removeComponent(index) {
    emit('update:period', {...props.period, components: props.period.components.filter((_, componentIndex) => componentIndex !== index)});
  }
</script>

<template>
  <div class="energy-cost-plan-period">
    <div class="row">
      <div class="col-sm-6 form-group" :class="{'has-error': errors.validFrom}">
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
      <div class="col-sm-6 form-group" :class="{'has-error': errors.validTo}">
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
    <template v-if="!period.components.length">
      <energy-cost-preset-picker
        :model-value="''"
        :presets="presets"
        :id-prefix="`energy-cost-period-${index}-initial`"
        @update:model-value="initializeComponents"
      />
      <div v-if="errors.components?.[0]?.preset" class="text-danger">{{ $t(errors.components[0].preset) }}</div>
    </template>
    <div v-for="(component, componentIndex) in period.components" :key="component.kind" class="energy-cost-component">
      <div class="clearfix">
        <h5 class="pull-left">{{ $t(componentLabels[component.kind]) }}</h5>
        <button v-if="period.components.length > 1" type="button" class="btn btn-link text-danger pull-right" @click="removeComponent(componentIndex)">
          {{ $t('Remove component') }}
        </button>
      </div>
      <template v-if="component.presetId !== undefined">
        <energy-cost-preset-picker
          :model-value="component.presetId"
          :presets="presets"
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
      <button
        v-for="kind in Object.keys(componentLabels).filter((kind) => !period.components.some((component) => component.kind === kind))"
        :key="kind"
        type="button"
        class="btn btn-default btn-sm"
        @click="addComponent(kind)"
      >
        {{ $t('Add {component}', {component: componentLabels[kind]}) }}
      </button>
    </div>
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
