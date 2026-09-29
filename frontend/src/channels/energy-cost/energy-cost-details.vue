<script setup>
  import {computed} from 'vue';
  import {DateTime} from 'luxon';
  import {formatDecimal} from './energy-cost-result-utils';

  const props = defineProps({result: {type: Object, required: true}, timezone: {type: String, required: true}});
  const periodicCharges = computed(() => props.result.periodicCharges || []);
  const billingPeriods = computed(() => props.result.billingPeriods || []);
  const money = (value) => (value === null || value === undefined ? '—' : `${formatDecimal(value)} ${props.result.currency}`);
  const energy = (value) => `${formatDecimal(value)} kWh`;
  const label = (value) => String(value || '').replace(/[-_]/g, ' ');
  const date = (value) => DateTime.fromISO(value, {setZone: true}).setZone(props.timezone).toFormat('dd LLL yyyy');
  const dateRange = (from, to) => `${date(from)} - ${date(to)}`;
</script>

<template>
  <div class="energy-cost-details">
    <div v-if="periodicCharges.length" class="mb-4">
      <h3>{{ $t('Fixed charges') }}</h3>
      <div class="table-responsive">
        <table class="table table-condensed">
          <thead>
            <tr>
              <th>{{ $t('Component') }}</th>
              <th>{{ $t('Applies to') }}</th>
              <th class="text-right">{{ $t('Units') }}</th>
              <th class="text-right">{{ $t('Rate') }}</th>
              <th class="text-right">{{ $t('Net cost') }}</th>
              <th class="text-right">{{ $t('Taxes') }}</th>
              <th class="text-right">{{ $t('Gross cost') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="charge in periodicCharges" :key="`${charge.id}-${charge.appliesFrom}`">
              <td>{{ label(charge.id || charge.kind) }}</td>
              <td>{{ dateRange(charge.appliesFrom, charge.appliesTo) }}</td>
              <td class="text-right">{{ charge.calculated ? formatDecimal(charge.calculated.units) : '—' }}</td>
              <td class="text-right">{{ charge.definition?.rate ? `${formatDecimal(charge.definition.rate)} ${charge.definition.unit || ''}` : '—' }}</td>
              <td class="text-right">{{ money(charge.calculated?.amounts?.net) }}</td>
              <td class="text-right">{{ money(charge.calculated?.amounts?.taxTotal) }}</td>
              <td class="text-right">{{ money(charge.calculated?.amounts?.gross) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <div v-if="billingPeriods.length">
      <h3>{{ $t('Billing periods') }}</h3>
      <div class="table-responsive">
        <table class="table table-condensed">
          <thead>
            <tr>
              <th>{{ $t('Period') }}</th>
              <th class="text-right">{{ $t('Imported') }}</th>
              <th class="text-right">{{ $t('Exported') }}</th>
              <th class="text-right">{{ $t('Usage-based cost') }}</th>
              <th class="text-right">{{ $t('Fixed charges') }}</th>
              <th class="text-right">{{ $t('Taxes') }}</th>
              <th class="text-right">{{ $t('Gross cost') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="period in billingPeriods" :key="period.from">
              <td>
                {{ dateRange(period.from, period.to) }}
                <small v-if="!period.fullyCovered" class="text-muted">({{ $t('partial') }})</small>
              </td>
              <td class="text-right">{{ energy(period.usage?.ACTIVE_ENERGY_IMPORT) }}</td>
              <td class="text-right">{{ energy(period.usage?.ACTIVE_ENERGY_EXPORT) }}</td>
              <td class="text-right">{{ money(period.costs?.gross?.usageBased?.total) }}</td>
              <td class="text-right">{{ money(period.costs?.gross?.periodic?.total) }}</td>
              <td class="text-right">{{ money(period.costs?.taxes?.total) }}</td>
              <td class="text-right">{{ money(period.costs?.gross?.total) }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
