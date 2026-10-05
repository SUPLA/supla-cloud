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
  const custom = ref(false);
  const selectedPreset = ref(null);
  const from = ref('');
  const to = ref('');
  const range = computed(() => ({
    from: DateTime.fromISO(props.modelValue.from, {zone: props.timezone}),
    to: DateTime.fromISO(props.modelValue.to, {zone: props.timezone}),
  }));
  const displayedRange = ref(range.value);
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
    custom: 'Custom',
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
    'custom',
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

  function setRange(kind) {
    const now = DateTime.now().setZone(props.timezone);
    const current = now.startOf('day');
    const latestCompleteHour = now.startOf('hour');
    const currentBillingCycle = billingCycleRange(now);
    const ranges = {
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
    const selectedRange = ranges[kind];
    if (!selectedRange) return;
    const [nextFrom, nextTo] = selectedRange;
    custom.value = false;
    selectedPreset.value = kind;
    displayedRange.value = {from: nextFrom, to: nextTo};
    emit('update:modelValue', {from: nextFrom.toISO(), to: nextTo.toISO()});
  }
  function selectPeriod(kind) {
    if (kind === 'custom') {
      custom.value = true;
      selectedPreset.value = kind;
      return;
    }
    setRange(kind);
  }
  function applyCustom() {
    const nextFrom = DateTime.fromFormat(from.value, "yyyy-LL-dd'T'HH:mm", {zone: props.timezone});
    const nextTo = DateTime.fromFormat(to.value, "yyyy-LL-dd'T'HH:mm", {zone: props.timezone});
    if (nextFrom.isValid && nextTo > nextFrom) {
      selectedPreset.value = 'custom';
      displayedRange.value = {from: nextFrom, to: nextTo};
      emit('update:modelValue', {from: nextFrom.toISO(), to: nextTo.toISO()});
    }
  }
  watch(
    range,
    ({from: nextFrom, to: nextTo}) => {
      displayedRange.value = {from: nextFrom, to: nextTo};
      from.value = nextFrom.toFormat("yyyy-LL-dd'T'HH:mm");
      to.value = nextTo.toFormat("yyyy-LL-dd'T'HH:mm");
    },
    {immediate: true}
  );
</script>

<template>
  <div class="energy-cost-range-selector mb-3">
    <div class="energy-cost-period-picker">
      <label>{{ $t('Period') }}</label>
      <!-- i18n:["Select period", "Today", "Yesterday", "This week", "Previous week", "This month", "Previous month", "Last 3 months", "This year", "Previous year", "Current billing cycle", "Previous billing cycle", "Custom"] -->
      <SimpleDropdown :value="selectedPreset" :options="periodOptions" @input="selectPeriod">
        <template #button="{value}">{{ $t(periodLabels[value] || 'Select period') }}</template>
        <template #default="{value}">{{ $t(periodLabels[value]) }}</template>
      </SimpleDropdown>
    </div>
    <div v-if="custom" class="row mt-2">
      <div class="col-sm-5"><input v-model="from" type="datetime-local" class="form-control" @change="applyCustom" /></div>
      <div class="col-sm-5"><input v-model="to" type="datetime-local" class="form-control" @change="applyCustom" /></div>
    </div>
    <div class="energy-cost-selected-period">
      <span>{{ $t('Selected period') }}</span>
      <strong>{{ displayedRange.from.toFormat('dd LLL yyyy, HH:mm') }} - {{ displayedRange.to.toFormat('dd LLL yyyy, HH:mm') }}</strong>
      <small>{{ timezone }}</small>
    </div>
  </div>
</template>

<style lang="scss">
  @use '@/styles/variables' as *;

  .energy-cost-range-selector {
    .energy-cost-period-picker {
      display: flex;
      align-items: center;
      gap: 0.75em;
      max-width: 25em;
      margin-bottom: 0.75em;

      label {
        margin: 0;
        white-space: nowrap;
      }

      .dropdown {
        flex: 1;
      }
    }

    .energy-cost-selected-period {
      display: grid;
      grid-template-columns: auto 1fr auto;
      gap: 0.5em 1em;
      align-items: baseline;
      padding: 0.75em 1em;
      border-left: 4px solid $supla-green;
      background: $supla-grey-light;

      span,
      small {
        color: $supla-grey-dark;
      }

      strong {
        font-family: $supla-font-special;
      }
    }

    @media (max-width: 575px) {
      .energy-cost-period-picker {
        max-width: none;
      }

      .energy-cost-selected-period {
        grid-template-columns: 1fr;
        gap: 0.25em;
      }
    }
  }
</style>
