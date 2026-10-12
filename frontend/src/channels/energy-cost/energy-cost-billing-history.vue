<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import {computed, ref} from 'vue';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {dateFromDatetime} from './energy-cost-plan-utils';
  import {changeTransition, insertTransition, removeTransition, timelineError} from './energy-cost-history';
  const props = defineProps({configuration: Object, errors: Array});
  const emit = defineEmits(['update:cycles']);
  const adding = ref(false);
  const newDate = ref('');
  const error = ref('');
  const invalid = computed(() => timelineError(props.configuration.billingCycles));
  function update(index, key, value) {
    if (key === 'validFrom') {
      emit('update:cycles', changeTransition(props.configuration.billingCycles, index, value, props.configuration.timezone));
      return;
    }
    emit(
      'update:cycles',
      props.configuration.billingCycles.map((cycle, position) => (position === index ? {...cycle, [key]: value} : cycle))
    );
  }
  function add() {
    try {
      emit('update:cycles', insertTransition(props.configuration.billingCycles, newDate.value, props.configuration.timezone));
      cancel();
    } catch (reason) {
      error.value = reason.message;
    }
  }
  function cancel() {
    adding.value = false;
    newDate.value = '';
    error.value = '';
  }
</script>
<template>
  <div>
    <h5>{{ $t('Billing periods') }}</h5>
    <div v-for="(cycle, index) in configuration.billingCycles" :key="index">
      <div v-if="index > 0" class="row">
        <div class="col-sm-6 form-group">
          <label>{{ $t('From date') }}</label>
          <input
            type="date"
            class="form-control"
            :value="dateFromDatetime(cycle.validFrom, configuration.timezone)"
            @input="update(index, 'validFrom', $event.target.value)"
          />
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6 form-group">
          <label :for="`billing-anchor-${index}`">{{ $t('Anchor date') }}</label>
          <input
            :id="`billing-anchor-${index}`"
            type="date"
            class="form-control"
            :value="cycle.anchor"
            :aria-describedby="`billing-anchor-${index}-help`"
            @input="update(index, 'anchor', $event.target.value)"
          />
          <span :id="`billing-anchor-${index}-help`" class="help-block">{{ $t('This can be the date of your last invoice.') }}</span>
        </div>
        <div class="col-sm-6 form-group">
          <label>{{ $t('Length') }}</label
          ><input type="number" min="1" class="form-control" :value="cycle.length" @input="update(index, 'length', $event.target.value)" />
        </div>
      </div>
      <div class="row">
        <div class="col-sm-6 form-group">
          <label>{{ $t('Unit') }}</label>
          <select class="form-control" :value="cycle.unit" @change="update(index, 'unit', $event.target.value)">
            <option value="DAY">{{ $t('Day') }}</option>
            <option value="WEEK">{{ $t('Week') }}</option>
            <option value="MONTH">{{ $t('Month') }}</option>
            <option value="YEAR">{{ $t('Year') }}</option>
          </select>
        </div>
      </div>
      <div v-for="(error, key) in errors?.[index]" :key="key" class="text-danger">{{ $t(error) }}</div>
      <button
        v-if="index > 0"
        type="button"
        class="btn btn-link text-danger"
        @click="emit('update:cycles', removeTransition(configuration.billingCycles, index))"
      >
        {{ $t('Remove billing period') }}
      </button>
    </div>
    <button v-if="!adding" type="button" class="btn btn-default" @click="adding = true">{{ $t('Add billing period') }}</button>
    <transition-expand>
      <div v-if="adding" class="form-group">
        <div class="row">
          <div class="col-sm-6">
            <label for="new-billing-period-date">{{ $t('From date') }}</label>
            <input id="new-billing-period-date" v-model="newDate" type="date" class="form-control" />
          </div>
        </div>
        <div class="billing-actions">
          <button type="button" class="btn btn-default" @click="add">{{ $t('Add billing period') }}</button>
          <button type="button" class="btn btn-link" @click="cancel">{{ $t('Cancel') }}</button>
        </div>
      </div>
    </transition-expand>
    <div v-if="invalid || error" class="text-danger">{{ $t(invalid || error) }}</div>
  </div>
</template>
<style scoped>
  .billing-actions {
    margin-top: 15px;
  }
</style>
