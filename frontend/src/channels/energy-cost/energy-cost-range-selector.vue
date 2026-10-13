<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed, ref, watch} from 'vue';
  import {DateTime} from 'luxon';
  import SimpleDropdown from '@/common/gui/simple-dropdown.vue';
  import {billingCycleRange} from './energy-cost-plan-utils';

  const props = defineProps({
    modelValue: {type: Object, required: true},
    timezone: {type: String, default: 'Europe/Warsaw'},
    billingCycles: {type: Array, default: () => []},
    disabled: Boolean,
  });
  const emit = defineEmits(['update:modelValue']);
  const from = ref('');
  const to = ref('');
  const range = computed(() => ({
    from: DateTime.fromISO(props.modelValue.from, {zone: props.timezone}),
    to: DateTime.fromISO(props.modelValue.to, {zone: props.timezone}),
  }));
  const periodLabels = {
    today: 'Today',
    yesterday: 'Yesterday',
    week: 'This week',
    previousWeek: 'Previous week',
    month: 'This month',
    previousMonth: 'Previous month',
    threeMonths: 'Last 3 months',
    year: 'This year',
    previousYear: 'Previous year',
    billingCycle: 'Current billing cycle',
    previousBillingCycle: 'Previous billing cycle',
  };
  const periodOptions = computed(() => [
    'today',
    'yesterday',
    'week',
    'previousWeek',
    'month',
    'previousMonth',
    'threeMonths',
    'year',
    'previousYear',
    ...(props.billingCycles.length ? ['billingCycle', 'previousBillingCycle'] : []),
  ]);

  function rangesFor(now) {
    const current = now.startOf('day');
    const currentBillingCycle = billingCycleRange(now, props.billingCycles, props.timezone);
    return {
      today: [current, current.plus({days: 1})],
      yesterday: [current.minus({days: 1}), current],
      week: [now.startOf('week'), now.startOf('week').plus({weeks: 1})],
      previousWeek: [now.startOf('week').minus({weeks: 1}), now.startOf('week')],
      month: [now.startOf('month'), now.startOf('month').plus({months: 1})],
      previousMonth: [now.startOf('month').minus({months: 1}), now.startOf('month')],
      threeMonths: [now.startOf('month').minus({months: 2}), now.startOf('month').plus({months: 1})],
      year: [now.startOf('year'), now.startOf('year').plus({years: 1})],
      previousYear: [now.startOf('year').minus({years: 1}), now.startOf('year')],
      billingCycle: currentBillingCycle,
      previousBillingCycle: billingCycleRange(now, props.billingCycles, props.timezone, true),
    };
  }

  function setRange(kind) {
    if (props.disabled) return;
    const ranges = rangesFor(DateTime.now().setZone(props.timezone));
    const selectedRange = ranges[kind];
    if (!selectedRange) return;
    const [nextFrom, nextTo] = selectedRange;
    emit('update:modelValue', {from: nextFrom.toISO(), to: nextTo.toISO()});
  }
  function applyRange() {
    if (props.disabled) return;
    const nextFrom = DateTime.fromISO(from.value, {zone: props.timezone}).startOf('day');
    const nextTo = DateTime.fromISO(to.value, {zone: props.timezone}).plus({days: 1}).startOf('day');
    if (nextFrom.isValid && nextTo > nextFrom) {
      emit('update:modelValue', {from: nextFrom.toISO(), to: nextTo.toISO()});
    }
  }
  function shiftRange(direction) {
    if (props.disabled) return;
    const {from: currentFrom, to: currentTo} = range.value;
    const duration = currentTo.diff(currentFrom).as('milliseconds');
    if (currentFrom.isValid && currentTo.isValid && duration > 0) {
      const crossesMonth = currentFrom.year !== currentTo.year || currentFrom.month !== currentTo.month;
      const shift = crossesMonth ? {months: direction} : {milliseconds: direction * duration};
      emit('update:modelValue', {
        from: currentFrom.plus(shift).toISO(),
        to: currentTo.plus(shift).toISO(),
      });
    }
  }
  watch(
    range,
    ({from: nextFrom, to: nextTo}) => {
      from.value = nextFrom.toISODate();
      to.value = nextTo.minus({milliseconds: 1}).toISODate();
    },
    {immediate: true}
  );
</script>

<template>
  <div class="energy-cost-range-selector mb-3">
    <!-- i18n:["Predefined time ranges", "Today", "Yesterday", "This week", "Previous week", "This month", "Previous month", "Last 3 months", "This year", "Previous year", "Current billing cycle", "Previous billing cycle", "From", "To", "Previous period", "Next period"] -->
    <SimpleDropdown :options="periodOptions" :disabled="disabled" @input="setRange">
      <template #button>{{ $t('Predefined time ranges') }}</template>
      <template #default="{value}">{{ $t(periodLabels[value]) }}</template>
    </SimpleDropdown>
    <div class="energy-cost-date-range">
      <div class="energy-cost-date-range-navigation form-group">
        <button type="button" class="btn btn-default" :disabled="disabled" :title="$t('Previous period')" @click="shiftRange(-1)">
          <fa icon="chevron-left" />
        </button>
      </div>
      <div class="row flex-grow-1">
        <div class="col-sm-6 form-group">
          <label>{{ $t('From') }}</label>
          <input v-model="from" type="date" class="form-control" :disabled="disabled" @change="applyRange" />
        </div>
        <div class="col-sm-6 form-group">
          <label>{{ $t('To') }}</label>
          <input v-model="to" type="date" class="form-control" :disabled="disabled" @change="applyRange" />
        </div>
      </div>
      <div class="energy-cost-date-range-navigation form-group">
        <button type="button" class="btn btn-default" :disabled="disabled" :title="$t('Next period')" @click="shiftRange(1)">
          <fa icon="chevron-right" />
        </button>
      </div>
    </div>
  </div>
</template>

<style lang="scss">
  .energy-cost-range-selector {
    > .dropdown {
      display: table;
      width: auto;
      margin: 0 auto 1rem;
    }

    .energy-cost-date-range {
      display: flex;
      gap: 1rem;
    }

    .energy-cost-date-range-navigation {
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
    }
  }
</style>
