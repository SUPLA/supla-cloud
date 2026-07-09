<template>
  <section class="wizard-panel">
    <div class="section-toolbar mt-4">
      <div>
        <h3 class="m-0">{{ $t('Tariff periods') }}</h3>
        <div class="text-muted small">{{ $t('By default one tariff period covers the profile. Add more only when the tariff changes by date.') }}</div>
      </div>
      <button class="btn btn-default btn-sm" type="button" @click="$emit('add-tariff-period')">{{ $t('Split timeline') }}</button>
    </div>

    <div v-for="(tariffPeriod, tariffPeriodIndex) in profile.tariffPeriods" :key="tariffPeriod._key" class="tariff-period-card">
      <div class="tariff-period-card__header">
        <div>
          <strong>{{ $t('Tariff period') }} {{ tariffPeriodIndex + 1 }}</strong>
          <div class="small text-muted">{{ tariffPeriodSummary(tariffPeriod, tariffs) }}</div>
        </div>
        <button
          v-if="profile.tariffPeriods.length > 1"
          class="btn btn-link btn-sm text-danger"
          type="button"
          @click="$emit('remove-tariff-period', tariffPeriodIndex)"
        >
          {{ $t('Delete') }}
        </button>
      </div>

      <TariffProfileIssues :messages="issuesFor(validation.tariffPeriods, tariffPeriod._key)" />
      <TariffProfileIssues :messages="issuesFor(validation.tariffPeriodWarnings, tariffPeriod._key)" variant="warning" />

      <div class="row g-3">
        <div class="col-lg-5">
          <div class="form-group mb-0">
            <label>{{ $t('Tariff definition') }}</label>
            <select v-model.number="tariffPeriod.tariffId" class="form-control" @change="$emit('tariff-change', tariffPeriod)">
              <option :value="null">{{ $t('Choose tariff') }}</option>
              <option v-for="tariff in tariffs" :key="tariff.id" :value="tariff.id">{{ tariff.name }} ({{ tariff.code }})</option>
            </select>
          </div>
        </div>
        <div v-if="profile.tariffPeriods.length > 1" class="col-lg-7">
          <label>{{ $t('Validity') }}</label>
          <DateRangePicker
            date-only
            :value="{dateStart: tariffPeriod.validFrom, dateEnd: tariffPeriod.validTo}"
            @input="(value) => emitRangeUpdate(tariffPeriod, value)"
          />
        </div>
      </div>

      <div v-if="profile.tariffPeriods.length === 1" class="timeline-default mt-3">
        <strong>{{ $t('Timeline') }}</strong>
        <span>{{ $t('No date limits until you split the timeline.') }}</span>
      </div>

      <div v-if="currentTariff(tariffPeriod)" class="tariff-hint mt-3">
        <div class="tariff-hint__identity">
          <strong>{{ currentTariff(tariffPeriod)?.name }}</strong>
          <span>{{ currentTariff(tariffPeriod)?.code }}</span>
        </div>
        <div class="tariff-hint__details">
          <span>{{ $t('Timezone') }}: {{ currentTariff(tariffPeriod)?.config?.timezone || 'UTC' }}</span>
          <span>{{ $t('Type') }}: {{ tariffTypeLabel(currentTariff(tariffPeriod)) }}</span>
          <span>{{ $t('Zones') }}: {{ tariffZoneSummary(currentTariff(tariffPeriod)) }}</span>
          <span>{{ $t('Default billing') }}: {{ tariffDefaultsSummary(tariffPeriod) }}</span>
          <span>{{ $t('Default components') }}: {{ tariffDefaultItemsSummary(tariffPeriod) }}</span>
          <span v-if="currentTariff(tariffPeriod)?.config?.dynamicPriceSource?.source"
            >{{ $t('Dynamic source') }}: {{ currentTariff(tariffPeriod).config.dynamicPriceSource.source }}</span
          >
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
  import DateRangePicker from '@/activity/date-range-picker.vue';
  import {energyPriceComponentLabel, extractTariffDefaults, isDynamicTariff, tariffPeriodSummary, tariffType, tariffZoneSummary} from './tariff-profile-utils';
  import TariffProfileIssues from './tariff-profile-issues.vue';

  const emit = defineEmits(['add-tariff-period', 'remove-tariff-period', 'tariff-change', 'update-tariff-period-range']);

  const props = defineProps({
    profile: Object,
    tariffs: Array,
    validation: Object,
  });

  function issuesFor(map, key) {
    return map?.[key] || [];
  }

  function currentTariff(tariffPeriod) {
    return props.tariffs?.find((tariff) => String(tariff.id) === String(tariffPeriod?.tariffId));
  }

  function tariffDefaultsSummary(tariffPeriod) {
    const defaults = extractTariffDefaults(currentTariff(tariffPeriod));
    return `${defaults.billingPeriodLength} ${defaults.billingPeriodUnit} · ${defaults.currency}`;
  }

  function tariffDefaultItemsSummary(tariffPeriod) {
    const items = extractTariffDefaults(currentTariff(tariffPeriod)).items;
    return items.length ? items.map((item) => energyPriceComponentLabel(item.componentCode)).join(', ') : '—';
  }

  function tariffTypeLabel(tariff) {
    return isDynamicTariff(tariff) ? 'dynamic_15m' : tariffType(tariff);
  }

  function emitRangeUpdate(tariffPeriod, value) {
    emit('update-tariff-period-range', {tariffPeriod, value});
  }
</script>

<style scoped>
  .wizard-panel,
  .tariff-period-card {
    border-radius: 20px;
  }

  .section-toolbar,
  .tariff-period-card__header,
  .tariff-hint {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .section-toolbar {
    align-items: center;
    margin-bottom: 16px;
  }

  .tariff-period-card {
    border: 1px solid rgba(0, 0, 0, 0.08);
    padding: 18px;
    background: #fcfdfd;
    margin-top: 16px;
  }

  .tariff-hint {
    border-radius: 14px;
    background: rgba(31, 122, 79, 0.08);
    padding: 12px 14px;
    align-items: flex-start;
  }

  .tariff-hint__identity,
  .tariff-hint__details {
    display: grid;
    gap: 4px;
  }

  .tariff-hint__identity span,
  .tariff-hint__details span {
    color: #4e5863;
  }

  .timeline-default {
    border-radius: 14px;
    background: rgba(0, 0, 0, 0.04);
    padding: 12px 14px;
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
    color: #4e5863;
  }
</style>
