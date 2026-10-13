<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed} from 'vue';
  import {DateTime} from 'luxon';
  import {useI18n} from 'vue-i18n';
  import {formatDecimal, summaryFromResult} from './energy-cost-result-utils';

  const props = defineProps({result: {type: Object, required: true}, range: Object, timezone: {type: String, default: 'UTC'}});
  const emit = defineEmits(['align-billing-periods']);
  const {locale} = useI18n();
  const summary = computed(() => summaryFromResult(props.result));
  const total = computed(() => (props.result.costs?.gross?.periodic?.total === null ? null : (props.result.costs?.gross?.total ?? null)));
  const unaligned = computed(() => props.result.billingContext?.requestedRangeCoversWholePeriods === false);
  const selectedRange = computed(() => props.range || props.result.range);
  const date = (value) => DateTime.fromISO(value, {setZone: true}).setZone(props.timezone).setLocale(locale.value).toFormat('dd LLL yyyy, HH:mm');
  const money = (value) => (value === null || value === undefined ? '—' : `${formatDecimal(value)} ${summary.value.currency}`);
</script>

<template>
  <div>
    <section class="well energy-cost-total-expenses">
      <h3>{{ $t('Total expenses for the selected period') }}</h3>
      <p v-if="selectedRange" class="text-muted">{{ date(selectedRange.from) }} – {{ date(selectedRange.to) }}</p>
      <strong>{{ money(total) }}</strong>
      <small>{{ $t('Gross cost, including fixed charges') }}</small>
      <div v-if="unaligned" class="energy-cost-total-alignment-hint text-muted mt-2">
        {{ $t('The selected dates are not aligned with billing periods.') }}
        <span v-if="total === null">{{ $t('Align the dates to calculate total expenses including fixed charges.') }}</span>
        <button type="button" class="btn btn-default btn-sm ml-2" @click="emit('align-billing-periods')">
          {{ $t('Align date range with billing period') }}
        </button>
      </div>
      <small v-else-if="total === null" class="text-muted">{{ $t('Total expenses are unavailable for this period.') }}</small>
    </section>
    <div class="row energy-cost-summary">
      <div class="col-sm-4 col-lg">
        <div class="well">
          <small>{{ $t('Usage-based cost (gross)') }}</small
          ><strong>{{ money(summary.usageBased) }}</strong>
        </div>
      </div>
      <div class="col-sm-4 col-lg">
        <div class="well">
          <small>{{ $t('Fixed charges (gross)') }}</small
          ><strong>{{ money(summary.fixedCharges) }}</strong>
        </div>
      </div>
      <div class="col-sm-4 col-lg">
        <div class="well">
          <small>{{ $t('Taxes (included)') }}</small
          ><strong>{{ money(summary.taxes) }}</strong>
        </div>
      </div>
      <div class="col-sm-6 col-lg">
        <div class="well">
          <small>{{ $t('Energy imported') }}</small
          ><strong>{{ formatDecimal(summary.imported) }} kWh</strong>
        </div>
      </div>
      <div class="col-sm-6 col-lg">
        <div class="well">
          <small>{{ $t('Energy exported') }}</small
          ><strong>{{ formatDecimal(summary.exported) }} kWh</strong>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
  .well {
    min-height: 78px;
    margin-bottom: 15px;
  }
  small,
  strong {
    display: block;
  }
  strong {
    font-size: 1.25em;
  }
  .energy-cost-total-expenses {
    break-inside: avoid;
  }
  .energy-cost-total-expenses h3 {
    margin-top: 0;
  }
  .energy-cost-total-expenses strong {
    font-size: 2em;
  }
  .energy-cost-total-alignment-hint {
    font-size: 0.9em;
  }
  @media print {
    .energy-cost-total-alignment-hint button {
      display: none;
    }
  }
</style>
