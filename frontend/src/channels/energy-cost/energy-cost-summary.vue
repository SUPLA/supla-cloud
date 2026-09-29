<script setup>
  import {computed} from 'vue';
  import {formatDecimal, summaryFromResult} from './energy-cost-result-utils';

  const props = defineProps({result: {type: Object, required: true}});
  const summary = computed(() => summaryFromResult(props.result));
  const money = (value) => (value === null ? '—' : `${formatDecimal(value)} ${summary.value.currency}`);
</script>

<template>
  <div>
    <div v-if="summary.incomplete" class="alert alert-info">
      {{ $t('The selected range does not contain complete billing periods. Fixed charges are not included in the complete total.') }}
    </div>
    <div class="row energy-cost-summary">
      <div class="col-sm-4 col-lg">
        <div class="well">
          <small>{{ $t(summary.incomplete ? 'Usage-based cost' : 'Total gross cost') }}</small
          ><strong>{{ money(summary.incomplete ? summary.usageBased : summary.total) }}</strong>
        </div>
      </div>
      <div class="col-sm-4 col-lg">
        <div class="well">
          <small>{{ $t('Net cost') }}</small
          ><strong>{{ money(summary.net) }}</strong>
        </div>
      </div>
      <div class="col-sm-4 col-lg">
        <div class="well">
          <small>{{ $t('Taxes') }}</small
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
    <p v-if="summary.periodic !== '0'" class="text-muted">{{ $t('Periodic charges') }}: {{ money(summary.periodic) }}</p>
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
