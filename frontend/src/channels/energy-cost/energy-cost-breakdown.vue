<script setup>
  import {computed} from 'vue';
  import {componentBreakdown, formatDecimal, taxBreakdown, zoneBreakdown} from './energy-cost-result-utils';

  const props = defineProps({result: {type: Object, required: true}, currency: String});
  const components = computed(() => componentBreakdown(props.result));
  const zones = computed(() => zoneBreakdown(props.result));
  const taxes = computed(() => taxBreakdown(props.result));
  const label = (id) => id.replace(/[-_]/g, ' ');
</script>

<template>
  <div v-if="components.length || zones.length || taxes.length" class="energy-cost-breakdown row">
    <div v-if="components.length" class="col-md-4">
      <h3>{{ $t('Cost by component') }}</h3>
      <table class="table table-condensed">
        <tbody>
          <tr v-for="item in components" :key="item.id">
            <td>{{ label(item.id) }}</td>
            <td class="text-right">{{ formatDecimal(item.amount) }} {{ currency }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="zones.length" class="col-md-4">
      <h3>{{ $t('Cost by tariff zone') }}</h3>
      <table class="table table-condensed">
        <tbody>
          <tr v-for="item in zones" :key="item.id">
            <td>{{ label(item.id) }}</td>
            <td class="text-right">{{ formatDecimal(item.amount) }} {{ currency }}</td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="taxes.length" class="col-md-4">
      <h3>{{ $t('Taxes by type') }}</h3>
      <table class="table table-condensed">
        <tbody>
          <tr v-for="item in taxes" :key="item.id">
            <td>{{ label(item.id) }}</td>
            <td class="text-right">{{ formatDecimal(item.amount) }} {{ currency }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
