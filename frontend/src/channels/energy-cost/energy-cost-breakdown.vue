<script setup>
  import {computed} from 'vue';
  import {componentBreakdown, formatDecimal} from './energy-cost-result-utils';

  const props = defineProps({result: {type: Object, required: true}, currency: String});
  const items = computed(() => componentBreakdown(props.result));
  const label = (id) => id.replace(/[-_]/g, ' ');
</script>

<template>
  <div v-if="items.length" class="energy-cost-breakdown">
    <h3>{{ $t('Cost by component') }}</h3>
    <table class="table table-condensed">
      <tbody>
        <tr v-for="item in items" :key="item.id">
          <td>{{ label(item.id) }}</td>
          <td class="text-right">{{ formatDecimal(item.amount) }} {{ currency }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>
