<script setup>
  import {computed} from 'vue';

  const props = defineProps({
    plans: {type: Array, required: true},
    tariffs: {type: Array, required: true},
    modelValue: String,
    assignedPlanId: Number,
    loading: Boolean,
    assignDisabled: Boolean,
  });
  defineEmits(['update:modelValue', 'assign', 'unassign', 'create', 'edit']);
  const selectedPlanId = computed(() => (props.modelValue?.startsWith('plan:') ? Number(props.modelValue.slice(5)) : undefined));
  const selectedStarter = computed(() => props.modelValue?.startsWith('starter:'));
  const assignedPlan = computed(() => props.plans.find((plan) => plan.id === props.assignedPlanId));
  const unassignedPlans = computed(() => props.plans.filter((plan) => plan.id !== props.assignedPlanId));
  const groupedTariffs = computed(() => {
    const groups = new Map();
    props.tariffs.forEach((tariff) => {
      const label = tariff.operator?.label || tariff.operator || '';
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
        <option v-if="assignedPlan" :value="`plan:${assignedPlan.id}`">{{ assignedPlan.name }} ({{ $t('Assigned') }})</option>
        <optgroup v-if="unassignedPlans.length" :label="$t('Your tariff plans')">
          <option v-for="plan in unassignedPlans" :key="plan.id" :value="`plan:${plan.id}`">{{ plan.name }}</option>
        </optgroup>
        <optgroup v-for="[operator, operatorTariffs] in groupedTariffs" :key="operator" :label="operator">
          <option v-for="tariff in operatorTariffs" :key="tariff.id" :value="`starter:${tariff.id}`">{{ tariff.label }}</option>
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
