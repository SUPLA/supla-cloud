<script setup>
  import {computed} from 'vue';
  import {formatDecimal, summaryFromResult} from './energy-cost-result-utils';

  const props = defineProps({result: {type: Object, required: true}});
  const summary = computed(() => summaryFromResult(props.result));
  const money = (value) => (value === null ? '—' : `${formatDecimal(value)} ${summary.value.currency}`);
</script>

<template>
  <div>
    <div class="row energy-cost-summary">
      <div class="col-sm-4 col-lg">
        <div class="well">
          <small>{{ $t('Usage-based gross cost') }}</small
          ><strong>{{ money(summary.usageBased) }}</strong>
        </div>
      </div>
      <div class="col-sm-4 col-lg">
        <div class="well">
          <small>{{ $t('Usage-based net cost') }}</small
          ><strong>{{ money(summary.usageBasedNet) }}</strong>
        </div>
      </div>
      <div class="col-sm-4 col-lg">
        <div class="well">
          <small>{{ $t('Usage-based taxes') }}</small
          ><strong>{{ money(summary.usageBasedTaxes) }}</strong>
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
</style>
