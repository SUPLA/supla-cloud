<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import {computed, ref} from 'vue';
  import {useI18n} from 'vue-i18n';
  import {DateTime} from 'luxon';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {dateFromDatetime} from './energy-cost-plan-utils';
  import {changeTransition, insertTransition, removeTransition, timelineError} from './energy-cost-history';
  const props = defineProps({configuration: Object, errors: Array});
  const emit = defineEmits(['update:cycles']);
  const {locale} = useI18n();
  const adding = ref(false);
  const newDate = ref('');
  const error = ref('');
  const invalid = computed(() => timelineError(props.configuration.billingCycles));
  function startAdding() {
    newDate.value = DateTime.now().setZone(props.configuration.timezone).toISODate();
    error.value = '';
    adding.value = true;
  }
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
  <div class="billing-history">
    <section v-for="(cycle, index) in configuration.billingCycles" :key="index" class="billing-history-entry">
      <div class="billing-fields" :class="{'with-transition': index > 0}">
        <div v-if="index > 0" class="form-group">
          <label :for="`billing-from-${index}`">{{ $t('From date') }}</label>
          <input
            :id="`billing-from-${index}`"
            type="date"
            class="form-control"
            :lang="locale"
            :value="dateFromDatetime(cycle.validFrom, configuration.timezone)"
            @input="update(index, 'validFrom', $event.target.value)"
          />
        </div>
        <div class="form-group">
          <label :for="`billing-anchor-${index}`">{{ $t('Anchor date') }}</label>
          <input
            :id="`billing-anchor-${index}`"
            type="date"
            class="form-control"
            :lang="locale"
            :value="cycle.anchor"
            :aria-describedby="`billing-anchor-${index}-help`"
            @input="update(index, 'anchor', $event.target.value)"
          />
        </div>
        <div class="form-group">
          <label :for="`billing-length-${index}`">{{ $t('Length') }}</label>
          <input
            :id="`billing-length-${index}`"
            type="number"
            min="1"
            class="form-control"
            :value="cycle.length"
            @input="update(index, 'length', $event.target.value)"
          />
        </div>
        <div class="form-group">
          <label :for="`billing-unit-${index}`">{{ $t('Unit') }}</label>
          <select :id="`billing-unit-${index}`" class="form-control" :value="cycle.unit" @change="update(index, 'unit', $event.target.value)">
            <option value="DAY">{{ $t('Day') }}</option>
            <option value="WEEK">{{ $t('Week') }}</option>
            <option value="MONTH">{{ $t('Month') }}</option>
            <option value="YEAR">{{ $t('Year') }}</option>
          </select>
        </div>
      </div>
      <div v-for="(error, key) in errors?.[index]" :key="key" class="text-danger">{{ $t(error) }}</div>
      <div class="billing-entry-footer">
        <small :id="`billing-anchor-${index}-help`" class="text-muted">{{ $t('This can be the date of your last invoice.') }}</small>
        <button
          v-if="configuration.billingCycles.length > 1"
          type="button"
          class="btn btn-link btn-xs billing-remove"
          @click="emit('update:cycles', removeTransition(configuration.billingCycles, index))"
        >
          <i class="pe-7s-trash" aria-hidden="true"></i> {{ $t('Remove billing period') }}
        </button>
      </div>
    </section>
    <button v-if="!adding" type="button" class="btn btn-default btn-sm" @click="startAdding">
      <i class="pe-7s-date" aria-hidden="true"></i> {{ $t('Add billing period') }}
    </button>
    <transition-expand>
      <div v-if="adding" class="billing-add-form">
        <div class="form-group">
          <label for="new-billing-period-date">{{ $t('When the billing period changes?') }}</label>
          <input id="new-billing-period-date" v-model="newDate" type="date" class="form-control" :lang="locale" />
        </div>
        <div class="billing-actions">
          <button type="button" class="btn btn-default btn-sm" @click="add">
            <i class="pe-7s-date" aria-hidden="true"></i> {{ $t('Add billing period') }}
          </button>
          <button type="button" class="btn btn-link btn-sm" @click="cancel">{{ $t('Cancel') }}</button>
        </div>
      </div>
    </transition-expand>
    <div v-if="invalid || error" class="text-danger">{{ $t(invalid || error) }}</div>
  </div>
</template>
<style scoped>
  .billing-history-entry,
  .billing-add-form {
    padding: 10px;
    margin-bottom: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
  }
  .billing-fields {
    display: grid;
    grid-template-columns: minmax(0, 1fr) 75px 110px;
    gap: 10px;
  }
  .billing-fields.with-transition {
    grid-template-columns: repeat(2, minmax(0, 1fr)) 75px 110px;
  }
  .form-group {
    margin-bottom: 8px;
  }
  .form-group label {
    margin-bottom: 4px;
    font-size: 12px;
  }
  .form-control {
    min-width: 0;
    height: 30px;
    padding: 4px 8px;
  }
  .billing-entry-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 5px 10px;
  }
  .billing-remove {
    color: #a94442;
  }
  .billing-remove:hover,
  .billing-remove:focus {
    color: #843534;
  }
  #new-billing-period-date {
    max-width: 240px;
  }
  .billing-actions {
    margin-top: 8px;
  }
  @media (max-width: 767px) {
    .billing-fields,
    .billing-fields.with-transition {
      grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .billing-fields:not(.with-transition) > :first-child {
      grid-column: 1 / -1;
    }
  }
</style>
