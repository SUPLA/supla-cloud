<script setup>
  defineProps({plans: {type: Array, required: true}, selectedPlanId: Number, loading: Boolean});
  defineEmits(['update:selectedPlanId', 'create', 'edit']);
</script>

<template>
  <div class="energy-cost-plan-toolbar">
    <div class="form-inline">
      <label for="energy-cost-plan-selector">{{ $t('Cost plan') }}</label>
      <select
        id="energy-cost-plan-selector"
        :value="selectedPlanId || ''"
        class="form-control"
        :disabled="loading"
        @change="$emit('update:selectedPlanId', $event.target.value ? Number($event.target.value) : undefined)"
      >
        <option value="">{{ $t('No cost plan') }}</option>
        <option v-for="plan in plans" :key="plan.id" :value="plan.id">{{ plan.name }}</option>
      </select>
      <span class="pull-right">
        <button v-if="selectedPlanId" type="button" class="btn btn-default" :disabled="loading" @click="$emit('edit')">
          {{ $t('Edit current cost plan') }}
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
