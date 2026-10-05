<script setup>
  import {computed, ref, watch} from 'vue';
  import {DateTime} from 'luxon';

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
  function applyCustom() {
    const nextFrom = DateTime.fromFormat(from.value, "yyyy-LL-dd'T'HH:mm", {zone: props.timezone});
    const nextTo = DateTime.fromFormat(to.value, "yyyy-LL-dd'T'HH:mm", {zone: props.timezone});
    if (nextFrom.isValid && nextTo > nextFrom) {
      selectedPreset.value = null;
      displayedRange.value = {from: nextFrom, to: nextTo};
      emit('update:modelValue', {from: nextFrom.toISO(), to: nextTo.toISO()});
    }
  }
  function toggleCustom() {
    custom.value = !custom.value;
    if (custom.value) selectedPreset.value = null;
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
    <div class="btn-group mr-2 mb-2">
      <button
        v-for="option in [
          ['today', 'Today'],
          ['yesterday', 'Yesterday'],
          ['week', 'This week'],
          ['previousWeek', 'Previous week'],
          ['month', 'This month'],
          ['previousMonth', 'Previous month'],
          ['threeMonths', 'Last 3 months'],
          ['year', 'This year'],
          ['previousYear', 'Previous year'],
        ]"
        :key="option[0]"
        type="button"
        class="btn btn-default"
        :class="{active: selectedPreset === option[0]}"
        @click="setRange(option[0])"
      >
        {{ $t(option[1]) }}
      </button>
      <button
        v-if="billingCycles.length"
        type="button"
        class="btn btn-default"
        :class="{active: selectedPreset === 'billingCycle'}"
        @click="setRange('billingCycle')"
      >
        {{ $t('Current billing cycle') }}
      </button>
      <button
        v-if="billingCycles.length"
        type="button"
        class="btn btn-default"
        :class="{active: selectedPreset === 'previousBillingCycle'}"
        @click="setRange('previousBillingCycle')"
      >
        {{ $t('Previous billing cycle') }}
      </button>
    </div>
    <button type="button" class="btn btn-default mb-2" :class="{active: custom}" @click="toggleCustom">{{ $t('Custom') }}</button>
    <div v-if="custom" class="row mt-2">
      <div class="col-sm-5"><input v-model="from" type="datetime-local" class="form-control" @change="applyCustom" /></div>
      <div class="col-sm-5"><input v-model="to" type="datetime-local" class="form-control" @change="applyCustom" /></div>
    </div>
    <p class="text-muted mb-0">
      {{ $t('Selected period') }}: {{ displayedRange.from.toFormat('dd LLL yyyy, HH:mm') }} - {{ displayedRange.to.toFormat('dd LLL yyyy, HH:mm') }} ({{
        timezone
      }})
    </p>
  </div>
</template>
