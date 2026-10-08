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

  const props = defineProps({
    modelValue: {type: Object, required: true},
    timezone: {type: String, default: 'Europe/Warsaw'},
    billingCycles: {type: Array, default: () => []},
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

  function billingCycleRange(now, previous = false) {
    const cycle = props.billingCycles.find((item) => {
      const validFrom = item.validFrom && DateTime.fromISO(item.validFrom, {zone: props.timezone});
      const validTo = item.validTo && DateTime.fromISO(item.validTo, {zone: props.timezone});
      return (!validFrom || validFrom <= now) && (!validTo || now < validTo);
    });
    if (!cycle) return null;
    const duration = {[`${cycle.unit.toLowerCase()}s`]: Number(cycle.length)};
    let start = DateTime.fromISO(cycle.anchor, {zone: props.timezone}).startOf('day');
    while (start.plus(duration) <= now) start = start.plus(duration);
    if (previous) start = start.minus(duration);
    return [start, start.plus(duration)];
  }

  function rangesFor(now) {
    const current = now.startOf('day');
    const latestCompleteHour = now.startOf('hour');
    const currentBillingCycle = billingCycleRange(now);
    return {
      today: [current, latestCompleteHour],
      yesterday: [current.minus({days: 1}), current],
      week: [now.startOf('week'), latestCompleteHour],
      previousWeek: [now.startOf('week').minus({weeks: 1}), now.startOf('week')],
      month: [now.startOf('month'), latestCompleteHour],
      previousMonth: [now.startOf('month').minus({months: 1}), now.startOf('month')],
      threeMonths: [now.startOf('month').minus({months: 2}), latestCompleteHour],
      year: [now.startOf('year'), latestCompleteHour],
      previousYear: [now.startOf('year').minus({years: 1}), now.startOf('year')],
      billingCycle: currentBillingCycle && [currentBillingCycle[0], currentBillingCycle[1] < latestCompleteHour ? currentBillingCycle[1] : latestCompleteHour],
      previousBillingCycle: billingCycleRange(now, true),
    };
  }

  function setRange(kind) {
    const ranges = rangesFor(DateTime.now().setZone(props.timezone));
    const selectedRange = ranges[kind];
    if (!selectedRange) return;
    const [nextFrom, nextTo] = selectedRange;
    emit('update:modelValue', {from: nextFrom.toISO(), to: nextTo.toISO()});
  }
  function applyRange() {
    const nextFrom = DateTime.fromFormat(from.value, "yyyy-LL-dd'T'HH:mm", {zone: props.timezone});
    const nextTo = DateTime.fromFormat(to.value, "yyyy-LL-dd'T'HH:mm", {zone: props.timezone});
    if (nextFrom.isValid && nextTo > nextFrom) {
      emit('update:modelValue', {from: nextFrom.toISO(), to: nextTo.toISO()});
    }
  }
  function shiftRange(direction) {
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
      from.value = nextFrom.toFormat("yyyy-LL-dd'T'HH:mm");
      to.value = nextTo.toFormat("yyyy-LL-dd'T'HH:mm");
    },
    {immediate: true}
  );
</script>

<template>
  <div class="energy-cost-range-selector mb-3">
    <!-- i18n:["Predefined time ranges", "Today", "Yesterday", "This week", "Previous week", "This month", "Previous month", "Last 3 months", "This year", "Previous year", "Current billing cycle", "Previous billing cycle", "From", "To", "Previous period", "Next period"] -->
    <SimpleDropdown :options="periodOptions" @input="setRange">
      <template #button>{{ $t('Predefined time ranges') }}</template>
      <template #default="{value}">{{ $t(periodLabels[value]) }}</template>
    </SimpleDropdown>
    <div class="energy-cost-date-range">
      <div class="energy-cost-date-range-navigation form-group">
        <button type="button" class="btn btn-default" :title="$t('Previous period')" @click="shiftRange(-1)">
          <fa icon="chevron-left" />
        </button>
      </div>
      <div class="row flex-grow-1">
        <div class="col-sm-6 form-group">
          <label>{{ $t('From') }}</label>
          <input v-model="from" type="datetime-local" class="form-control" @change="applyRange" />
        </div>
        <div class="col-sm-6 form-group">
          <label>{{ $t('To') }}</label>
          <input v-model="to" type="datetime-local" class="form-control" @change="applyRange" />
        </div>
      </div>
      <div class="energy-cost-date-range-navigation form-group">
        <button type="button" class="btn btn-default" :title="$t('Next period')" @click="shiftRange(1)">
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
