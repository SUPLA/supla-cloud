<template>
  <square-link class="tariff-profile-tile lift-up grey">
    <router-link :to="{name: 'tariffProfile', params: {id: model.id}}">
      <div class="tariff-profile-tile__header">
        <div>
          <h3>{{ model.name }}</h3>
        </div>
      </div>
      <dl>
        <dd>{{ $t('Tariff periods') }}</dd>
        <dt>{{ model.tariffPeriods.length }}</dt>
        <dd>{{ $t('Price periods') }}</dd>
        <dt>{{ countPricePeriods(model) }}</dt>
        <dd>{{ $t('Tariffs') }}</dd>
        <dt>{{ tariffSummary }}</dt>
      </dl>
    </router-link>
  </square-link>
</template>

<script setup>
  import {computed} from 'vue';
  import SquareLink from '@/common/tiles/square-link.vue';
  import {countPricePeriods} from './tariff-profile-utils';

  const props = defineProps({model: Object});

  const tariffSummary = computed(() => {
    const codes = [...new Set((props.model.tariffPeriods || []).map((period) => period.tariff?.code).filter(Boolean))];
    return codes.length ? codes.join(', ') : '—';
  });
</script>

<style scoped>
  .tariff-profile-tile {
    min-height: 100%;
  }

  .tariff-profile-tile__header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    align-items: flex-start;
    margin-bottom: 16px;
  }

  .tariff-profile-tile__eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 11px;
    opacity: 0.75;
    margin-bottom: 6px;
  }

  .tariff-profile-tile__id {
    border-radius: 999px;
    background: rgba(0, 0, 0, 0.08);
    padding: 4px 10px;
    font-size: 12px;
    font-weight: 700;
  }
</style>
