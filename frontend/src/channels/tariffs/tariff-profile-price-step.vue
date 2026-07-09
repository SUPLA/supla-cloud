<template>
  <section class="wizard-panel">
    <div class="section-toolbar">
      <div>
        <h3 class="m-0">{{ $t('Price periods') }}</h3>
        <div class="text-muted small">{{ $t('Start with one PLN period per tariff. Split only when prices change in time.') }}</div>
      </div>
    </div>

    <div v-for="(tariffPeriod, tariffPeriodIndex) in profile.tariffPeriods" :key="tariffPeriod._key" class="tariff-period-card">
      <div class="tariff-period-card__header">
        <div>
          <strong>{{ $t('Tariff period') }} {{ tariffPeriodIndex + 1 }}</strong>
          <div class="small text-muted">{{ tariffPeriodSummary(tariffPeriod, tariffs) }}</div>
        </div>
        <button class="btn btn-default btn-sm" type="button" @click="$emit('add-price-period', tariffPeriod)">{{ $t('Split prices') }}</button>
      </div>

      <div v-for="(pricePeriod, pricePeriodIndex) in tariffPeriod.pricePeriods" :key="pricePeriod._key" class="price-period-card">
        <div class="price-period-card__header">
          <div>
            <strong>{{ `${$t('Price period')} ${pricePeriodIndex + 1}` }}</strong>
            <div class="small text-muted">{{ formatRange(pricePeriod.validFrom, pricePeriod.validTo) }}</div>
          </div>
          <div class="price-period-card__actions">
            <button class="btn btn-default btn-sm" type="button" @click="emitPrefill(pricePeriod, tariffPeriod)">
              {{ hasTariffDefaults(tariffs, tariffPeriod) ? $t('Use tariff defaults') : dynamicButtonLabel(tariffs, tariffPeriod) }}
            </button>
            <button
              v-if="tariffPeriod.pricePeriods.length > 1"
              class="btn btn-link btn-sm text-danger"
              type="button"
              @click="emitRemovePricePeriod(tariffPeriod, pricePeriodIndex)"
            >
              {{ $t('Delete') }}
            </button>
          </div>
        </div>

        <TariffProfileIssues :messages="issuesFor(validation.pricePeriods, pricePeriod._key)" />
        <TariffProfileIssues :messages="issuesFor(validation.pricePeriodWarnings, pricePeriod._key)" variant="warning" />

        <div class="row g-3">
          <div class="col-sm-4 col-lg-3">
            <div class="form-group mb-0">
              <label>{{ $t('Length') }}</label>
              <input v-model.number="pricePeriod.billingPeriodLength" class="form-control" type="number" min="1" step="1" />
            </div>
          </div>
          <div class="col-sm-4 col-lg-3">
            <div class="form-group mb-0">
              <label>{{ $t('Unit') }}</label>
              <select v-model="pricePeriod.billingPeriodUnit" class="form-control">
                <option v-for="unit in billingPeriodUnits" :key="unit" :value="unit">{{ unit }}</option>
              </select>
            </div>
          </div>
          <div class="col-sm-4 col-lg-6">
            <div class="form-group mb-0">
              <label>{{ $t('Currency') }}</label>
              <CurrencyPicker v-model="pricePeriod.currency" />
            </div>
          </div>
        </div>

        <div v-if="tariffPeriod.pricePeriods.length > 1" class="mt-3">
          <label>{{ $t('Validity') }}</label>
          <DateRangePicker
            date-only
            :value="{dateStart: pricePeriod.validFrom, dateEnd: pricePeriod.validTo}"
            @input="(value) => emitPriceRangeUpdate(pricePeriod, value)"
          />
        </div>

        <div v-if="tariffPeriod.pricePeriods.length === 1" class="timeline-default mt-3">
          <strong>{{ $t('Timeline') }}</strong>
          <span>{{ $t('No date limits inside this tariff period until you split prices.') }}</span>
        </div>

        <div class="price-period-toolbar mt-3">
          <div>
            <strong>{{ $t('Price components') }}</strong>
            <div class="text-muted small">{{ $t('Add energy, distribution and fixed fees only where needed.') }}</div>
            <div v-if="isDynamicTariffPeriod(tariffs, tariffPeriod)" class="text-muted small mt-1">
              {{ $t('Forward active energy comes from the dynamic tariff source for each 15-minute slot.') }}
            </div>
          </div>
          <button class="btn btn-default btn-sm" type="button" @click="$emit('add-item', pricePeriod)">{{ $t('Add component') }}</button>
        </div>

        <div v-if="!pricePeriod.items.length" class="empty-state text-center text-muted py-4">
          {{ $t('No price components yet.') }}
        </div>

        <div v-for="(item, itemIndex) in pricePeriod.items" :key="item._key" class="price-item-card">
          <div class="row g-2 align-items-end">
            <div class="col-lg-4">
              <label>{{ $t('Component') }}</label>
              <select v-model="item.componentCode" class="form-control" @change="syncItemUnit(item)">
                <option :value="''">{{ $t('Choose component') }}</option>
                <option v-for="component in componentOptions" :key="component.value" :value="component.value">{{ component.label }}</option>
              </select>
            </div>
            <div v-if="!isDynamicTariffPeriod(tariffs, tariffPeriod)" class="col-lg-2 col-sm-4">
              <label>{{ $t('Zone') }}</label>
              <select v-model="item.zoneCode" class="form-control">
                <option :value="null">{{ $t('No zone') }}</option>
                <option v-for="zone in tariffZones(tariffs, tariffPeriod)" :key="zone.code" :value="zone.code">{{ zone.name || zone.code }}</option>
              </select>
            </div>
            <div class="col-lg-2 col-sm-4">
              <label>{{ $t('Amount') }}</label>
              <input v-model.number="item.amount" class="form-control" type="number" step="0.000001" min="0" />
            </div>
            <div class="col-lg-2 col-sm-4">
              <label>{{ $t('Unit') }}</label>
              <select v-model="item.unit" class="form-control">
                <option v-for="unit in unitOptionsForItem(item)" :key="unit" :value="unit">{{ unit }}</option>
              </select>
            </div>
            <div class="col-lg-2 text-end">
              <button class="btn btn-link btn-sm text-danger" type="button" @click="emitRemoveItem(pricePeriod, itemIndex)">{{ $t('Delete') }}</button>
            </div>
          </div>

          <TariffProfileIssues :messages="issuesFor(validation.items, item._key)" />
          <TariffProfileIssues :messages="issuesFor(validation.itemWarnings, item._key)" variant="warning" />
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
  import {useI18n} from 'vue-i18n';
  import DateRangePicker from '@/activity/date-range-picker.vue';
  import CurrencyPicker from '@/channels/params/currency-picker.vue';
  import {
    billingPeriodUnits,
    componentOptions,
    formatRange,
    hasTariffDefaults,
    isDynamicTariffPeriod,
    syncItemUnit,
    tariffPeriodSummary,
    tariffZones,
    unitOptionsForItem,
  } from './tariff-profile-utils';
  import TariffProfileIssues from './tariff-profile-issues.vue';

  const emit = defineEmits(['add-price-period', 'remove-price-period', 'prefill-price-period', 'update-price-period-range', 'add-item', 'remove-item']);
  const {t} = useI18n();

  defineProps({
    profile: Object,
    tariffs: Array,
    validation: Object,
  });

  function issuesFor(map, key) {
    return map?.[key] || [];
  }

  function emitPrefill(pricePeriod, tariffPeriod) {
    emit('prefill-price-period', {pricePeriod, tariffPeriod});
  }

  function emitRemovePricePeriod(tariffPeriod, index) {
    emit('remove-price-period', {tariffPeriod, index});
  }

  function emitPriceRangeUpdate(pricePeriod, value) {
    emit('update-price-period-range', {pricePeriod, value});
  }

  function emitRemoveItem(pricePeriod, index) {
    emit('remove-item', {pricePeriod, index});
  }

  function dynamicButtonLabel(tariffs, tariffPeriod) {
    return isDynamicTariffPeriod(tariffs, tariffPeriod) ? t('Start empty') : t('Create zone rows');
  }
</script>

<style scoped>
  .tariff-period-card,
  .price-period-card,
  .price-item-card {
    border-radius: 20px;
  }

  .section-toolbar,
  .tariff-period-card__header,
  .price-period-card__header,
  .price-period-card__actions,
  .price-period-toolbar {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .section-toolbar {
    align-items: center;
    margin-bottom: 16px;
  }

  .tariff-period-card,
  .price-period-card,
  .price-item-card {
    border: 1px solid rgba(0, 0, 0, 0.08);
    padding: 18px;
    background: #fcfdfd;
    margin-top: 16px;
  }

  .price-period-card,
  .price-item-card {
    background: #fff;
  }

  .price-item-card {
    margin-top: 12px;
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
