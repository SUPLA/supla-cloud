<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import {computed} from 'vue';
  import {useI18n} from 'vue-i18n';
  import {formatDecimal, selectedRangeLabel, summaryFromResult} from './energy-cost-result-utils';

  const props = defineProps({result: {type: Object, required: true}, range: Object, timezone: {type: String, default: 'UTC'}});
  const emit = defineEmits(['align-billing-periods']);
  const {locale} = useI18n();
  const summary = computed(() => summaryFromResult(props.result));
  const total = computed(() => props.result.reportSummary?.costs?.gross?.total ?? props.result.costs?.gross?.total ?? null);
  const missingFixedCharges = computed(() => props.result.reportSummary?.fixedChargesComplete === false);
  const partialPeriods = computed(() => props.result.billingPeriods?.some((period) => !period.fullyCovered));
  const expand = computed(() => props.result.billingPeriods?.length === 1);
  const selectedRange = computed(() => props.range || props.result.range);
  const rangeLabel = computed(() => selectedRangeLabel(selectedRange.value, props.timezone, locale.value));
  const money = (value) => (value === null || value === undefined ? '—' : `${formatDecimal(value)} ${summary.value.currency}`);
</script>

<template>
  <div>
    <section class="well energy-cost-total-expenses">
      <h3>{{ $t('Total expenses for the selected period') }}</h3>
      <p v-if="selectedRange" class="text-muted">{{ rangeLabel }}</p>
      <strong>{{ money(total) }}</strong>
      <small>{{ $t('Gross cost, including fixed charges') }}</small>
      <div v-if="missingFixedCharges" class="energy-cost-total-alignment-hint text-muted mt-2">
        {{ $t('Some fixed charges are not included because the selected date range does not cover complete billing periods.') }}
        <span v-if="partialPeriods">{{
          expand
            ? $t('Expand the selection to the entire billing period to include its fixed charges.')
            : $t('Align the selection with billing periods to include omitted fixed charges.')
        }}</span>
        <button v-if="partialPeriods" type="button" class="btn btn-default btn-sm ml-2" @click="emit('align-billing-periods')">
          {{ expand ? $t('Expand') : $t('Align') }}
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
