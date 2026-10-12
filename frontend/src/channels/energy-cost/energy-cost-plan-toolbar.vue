<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed, onMounted} from 'vue';
  import {useI18n} from 'vue-i18n';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';
  import {presentCostPlan, tariffCatalogPresentation} from './energy-cost-plan-presentation';

  const props = defineProps({
    plans: {type: Array, required: true},
    tariffs: {type: Array, required: true},
    modelValue: String,
    assignedPlanId: Number,
    loading: Boolean,
    assignDisabled: Boolean,
  });
  defineEmits(['update:modelValue', 'assign', 'unassign', 'create', 'edit']);
  const {t} = useI18n();
  const store = useEnergyCostStore();
  onMounted(() => store.fetchPresentationCatalogs());
  const planLabel = (plan) => presentCostPlan(plan.configuration, {starters: Object.values(store.tariffDetailsById), presets: store.presets, t}).label;
  const selectedPlanId = computed(() => (props.modelValue?.startsWith('plan:') ? Number(props.modelValue.slice(5)) : undefined));
  const selectedStarter = computed(() => props.modelValue?.startsWith('starter:'));
  const assignedPlan = computed(() => props.plans.find((plan) => plan.id === props.assignedPlanId));
  const unassignedPlans = computed(() => props.plans.filter((plan) => plan.id !== props.assignedPlanId));
  const groupedTariffs = computed(() => {
    const groups = new Map();
    props.tariffs.forEach((tariff) => {
      const label = tariffCatalogPresentation(tariff, t).group;
      groups.set(label, [...(groups.get(label) || []), tariff]);
    });
    return [...groups.entries()];
  });
</script>

<template>
  <div class="energy-cost-plan-toolbar">
    <div class="form-inline">
      <label for="energy-cost-plan-selector">{{ $t('Tariff plan') }}</label>
      <select
        id="energy-cost-plan-selector"
        :value="modelValue || ''"
        class="form-control"
        :disabled="loading"
        @change="$emit('update:modelValue', $event.target.value || undefined)"
      >
        <option v-if="!assignedPlanId" value="">{{ $t('No tariff plan') }}</option>
        <option v-if="assignedPlan" :value="`plan:${assignedPlan.id}`">{{ planLabel(assignedPlan) }} ({{ $t('Assigned') }})</option>
        <optgroup v-if="unassignedPlans.length" :label="$t('Your tariff plans')">
          <option v-for="plan in unassignedPlans" :key="plan.id" :value="`plan:${plan.id}`">{{ planLabel(plan) }}</option>
        </optgroup>
        <optgroup v-for="[operator, operatorTariffs] in groupedTariffs" :key="operator" :label="operator">
          <option v-for="tariff in operatorTariffs" :key="tariff.id" :value="`starter:${tariff.id}`">{{ tariffCatalogPresentation(tariff, t).label }}</option>
        </optgroup>
      </select>
      <button
        v-if="selectedStarter || (selectedPlanId && selectedPlanId !== assignedPlanId)"
        type="button"
        class="btn btn-green"
        :disabled="loading || assignDisabled"
        @click="$emit('assign')"
      >
        {{ $t('Assign') }}
      </button>
      <button
        v-else-if="selectedPlanId && selectedPlanId === assignedPlanId"
        type="button"
        class="btn btn-default"
        :disabled="loading"
        @click="$emit('unassign')"
      >
        {{ $t('Unassign') }}
      </button>
      <span class="pull-right">
        <button v-if="selectedPlanId" type="button" class="btn btn-default" :disabled="loading" @click="$emit('edit')">
          {{ $t('Edit current tariff plan') }}
        </button>
        <button type="button" class="btn btn-green" :disabled="loading" @click="$emit('create')">{{ $t('Create new') }}</button>
      </span>
    </div>
  </div>
</template>

<style scoped>
  .energy-cost-plan-toolbar label {
    margin-right: 10px;
  }

  .energy-cost-plan-toolbar .btn {
    margin-left: 10px;
  }
</style>
