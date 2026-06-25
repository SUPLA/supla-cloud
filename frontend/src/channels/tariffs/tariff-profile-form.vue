<template>
  <page-container :error="error" class="container">
    <BreadcrumbList v-if="profile" :current="isNew ? $t('Create tariff profile') : $t('Edit')">
      <RouterLink :to="{name: 'tariffProfiles'}">{{ $t('Tariff profiles') }}</RouterLink>
      <RouterLink v-if="!isNew" :to="{name: 'tariffProfile', params: {id}}">{{ profile.name }}</RouterLink>
    </BreadcrumbList>

    <loading-cover :loading="loading || !profile || submitting">
      <pending-changes-page v-if="profile" :header="isNew ? $t('Create tariff profile') : profile.name" :cancellable="false" @save="submit()">
        <template #buttons>
          <div class="btn-toolbar wizard-buttons wizard-buttons--desktop">
            <router-link :to="cancelRoute" class="btn btn-grey">
              {{ $t('Cancel') }}
            </router-link>
            <button v-if="step > 1" class="btn btn-default" type="button" @click="step--">{{ $t('Back') }}</button>
            <button v-if="step < 2" class="btn btn-default" type="button" @click="step++">{{ $t('Next') }}</button>
            <button v-else class="btn btn-white" type="submit" :disabled="validation.errors.length > 0">
              {{ isNew ? $t('Create profile') : $t('Save profile') }}
            </button>
          </div>
        </template>

        <div class="wizard-shell">
          <div class="wizard-intro details-page-block">
            <div>
              <div class="wizard-intro__eyebrow">{{ $t('Wizard') }}</div>
              <h2>{{ $t('Tariff profile setup') }}</h2>
              <p>{{ $t('Start with one tariff and one price period. Split the timeline only when billing actually changes.') }}</p>
            </div>
            <div class="wizard-intro__stats">
              <div>
                <span>{{ $t('Tariff periods') }}</span>
                <strong>{{ profile.tariffPeriods.length }}</strong>
              </div>
              <div>
                <span>{{ $t('Price periods') }}</span>
                <strong>{{ countPricePeriods(profile) }}</strong>
              </div>
            </div>
          </div>

          <div class="wizard-steps">
            <button type="button" :class="['wizard-step', {active: step === 1}]" @click="step = 1">
              <span>1</span>
              <strong>{{ $t('Tariff base') }}</strong>
              <small>{{ $t('Pick tariffs and date ranges') }}</small>
            </button>
            <button type="button" :class="['wizard-step', {active: step === 2}]" @click="step = 2">
              <span>2</span>
              <strong>{{ $t('Price periods') }}</strong>
              <small>{{ $t('Set prices and components') }}</small>
            </button>
          </div>

          <div v-if="currentStepErrors.length" class="alert alert-danger compact-alert">
            <strong>{{ $t('Please fix these issues before saving:') }}</strong>
            <ul class="mb-0 mt-2">
              <li v-for="message in currentStepErrors" :key="message">{{ message }}</li>
            </ul>
          </div>

          <div v-if="currentStepWarnings.length" class="alert alert-warning compact-alert">
            <strong>{{ $t('Review these warnings:') }}</strong>
            <ul class="mb-0 mt-2">
              <li v-for="message in currentStepWarnings" :key="message">{{ message }}</li>
            </ul>
          </div>

          <section v-show="step === 1" class="details-page-block wizard-panel">
            <div class="row g-3 align-items-end">
              <div class="col-lg-8">
                <div class="form-group mb-0">
                  <label>{{ $t('Profile name') }}</label>
                  <input v-model="profile.name" class="form-control" type="text" :placeholder="$t('e.g. Home 2026 tariff profile')" />
                </div>
              </div>
              <div class="col-lg-4">
                <div class="helper-card">
                  <strong>{{ $t('Default path') }}</strong>
                  <div>{{ $t('Keep one tariff period unless the tariff itself changes in time.') }}</div>
                </div>
              </div>
            </div>

            <div class="section-toolbar mt-4">
              <div>
                <h3 class="m-0">{{ $t('Tariff periods') }}</h3>
                <div class="text-muted small">{{ $t('By default one tariff period covers the profile. Add more only when the tariff changes by date.') }}</div>
              </div>
              <button class="btn btn-default btn-sm" type="button" @click="addTariffPeriod()">{{ $t('Split timeline') }}</button>
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
                  @click="removeTariffPeriod(tariffPeriodIndex)"
                >
                  {{ $t('Delete') }}
                </button>
              </div>

              <ul v-if="issuesFor(validation.tariffPeriods, tariffPeriod._key).length" class="issue-list issue-list--danger">
                <li v-for="message in issuesFor(validation.tariffPeriods, tariffPeriod._key)" :key="`${tariffPeriod._key}-${message}`">{{ message }}</li>
              </ul>
              <ul v-if="issuesFor(validation.tariffPeriodWarnings, tariffPeriod._key).length" class="issue-list issue-list--warning">
                <li v-for="message in issuesFor(validation.tariffPeriodWarnings, tariffPeriod._key)" :key="`${tariffPeriod._key}-warning-${message}`">
                  {{ message }}
                </li>
              </ul>

              <div class="row g-3">
                <div class="col-lg-5">
                  <div class="form-group mb-0">
                    <label>{{ $t('Tariff definition') }}</label>
                    <select v-model.number="tariffPeriod.tariffId" class="form-control" @change="onTariffChange(tariffPeriod)">
                      <option :value="null">{{ $t('Choose tariff') }}</option>
                      <option v-for="tariff in tariffs" :key="tariff.id" :value="tariff.id">{{ tariff.name }} ({{ tariff.code }})</option>
                    </select>
                  </div>
                </div>
                <div v-if="profile.tariffPeriods.length > 1" class="col-lg-7">
                  <label>{{ $t('Validity') }}</label>
                  <DateRangePicker
                    :value="{dateStart: tariffPeriod.validFrom, dateEnd: tariffPeriod.validTo}"
                    @input="(value) => updateTariffPeriodRange(tariffPeriod, value)"
                  />
                </div>
              </div>

              <div v-if="profile.tariffPeriods.length === 1" class="timeline-default mt-3">
                <strong>{{ $t('Timeline') }}</strong>
                <span>{{ $t('Full timeline from 2016-01-01 until changed later.') }}</span>
              </div>

              <div v-if="selectedTariff(tariffs, tariffPeriod.tariffId)" class="tariff-hint mt-3">
                <div>
                  <strong>{{ selectedTariff(tariffs, tariffPeriod.tariffId)?.code }}</strong>
                  <span>{{ selectedTariff(tariffs, tariffPeriod.tariffId)?.config?.timezone || 'UTC' }}</span>
                </div>
                <div>{{ tariffZoneSummary(selectedTariff(tariffs, tariffPeriod.tariffId)) }}</div>
              </div>
            </div>
          </section>

          <section v-show="step === 2" class="details-page-block wizard-panel">
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
                <button class="btn btn-default btn-sm" type="button" @click="addPricePeriod(tariffPeriod)">{{ $t('Split prices') }}</button>
              </div>

              <div v-for="(pricePeriod, pricePeriodIndex) in tariffPeriod.pricePeriods" :key="pricePeriod._key" class="price-period-card">
                <div class="price-period-card__header">
                  <div>
                    <strong>{{ pricePeriod.name || `${$t('Price period')} ${pricePeriodIndex + 1}` }}</strong>
                    <div class="small text-muted">{{ formatRange(pricePeriod.validFrom, pricePeriod.validTo) }}</div>
                  </div>
                  <div class="price-period-card__actions">
                    <button class="btn btn-default btn-sm" type="button" @click="prefillPricePeriodFromTariff(pricePeriod, tariffPeriod, tariffs)">
                      {{ hasTariffDefaults(tariffs, tariffPeriod) ? $t('Use tariff defaults') : $t('Create zone rows') }}
                    </button>
                    <button
                      v-if="tariffPeriod.pricePeriods.length > 1"
                      class="btn btn-link btn-sm text-danger"
                      type="button"
                      @click="removePricePeriod(tariffPeriod, pricePeriodIndex)"
                    >
                      {{ $t('Delete') }}
                    </button>
                  </div>
                </div>

                <ul v-if="issuesFor(validation.pricePeriods, pricePeriod._key).length" class="issue-list issue-list--danger">
                  <li v-for="message in issuesFor(validation.pricePeriods, pricePeriod._key)" :key="`${pricePeriod._key}-${message}`">{{ message }}</li>
                </ul>
                <ul v-if="issuesFor(validation.pricePeriodWarnings, pricePeriod._key).length" class="issue-list issue-list--warning">
                  <li v-for="message in issuesFor(validation.pricePeriodWarnings, pricePeriod._key)" :key="`${pricePeriod._key}-warning-${message}`">
                    {{ message }}
                  </li>
                </ul>

                <div class="row g-3">
                  <div class="col-lg-5">
                    <div class="form-group mb-0">
                      <label>{{ $t('Price period name') }}</label>
                      <input v-model="pricePeriod.name" class="form-control" type="text" :placeholder="$t('Optional')" />
                    </div>
                  </div>
                  <div class="col-sm-4 col-lg-2">
                    <div class="form-group mb-0">
                      <label>{{ $t('Length') }}</label>
                      <input v-model.number="pricePeriod.billingPeriodLength" class="form-control" type="number" min="1" step="1" />
                    </div>
                  </div>
                  <div class="col-sm-4 col-lg-2">
                    <div class="form-group mb-0">
                      <label>{{ $t('Unit') }}</label>
                      <select v-model="pricePeriod.billingPeriodUnit" class="form-control">
                        <option v-for="unit in billingPeriodUnits" :key="unit" :value="unit">{{ unit }}</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-sm-4 col-lg-3">
                    <div class="form-group mb-0">
                      <label>{{ $t('Currency') }}</label>
                      <CurrencyPicker v-model="pricePeriod.currency" />
                    </div>
                  </div>
                </div>

                <div v-if="tariffPeriod.pricePeriods.length > 1" class="mt-3">
                  <label>{{ $t('Validity') }}</label>
                  <DateRangePicker
                    :value="{dateStart: pricePeriod.validFrom, dateEnd: pricePeriod.validTo}"
                    @input="(value) => updatePricePeriodRange(pricePeriod, value)"
                  />
                </div>

                <div v-if="tariffPeriod.pricePeriods.length === 1" class="timeline-default mt-3">
                  <strong>{{ $t('Timeline') }}</strong>
                  <span>{{ $t('Uses the full tariff period until you split prices.') }}</span>
                </div>

                <div class="price-period-toolbar mt-3">
                  <div>
                    <strong>{{ $t('Price components') }}</strong>
                    <div class="text-muted small">{{ $t('Add energy, distribution and fixed fees only where needed.') }}</div>
                  </div>
                  <button class="btn btn-default btn-sm" type="button" @click="addItem(pricePeriod)">{{ $t('Add component') }}</button>
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
                    <div class="col-lg-2 col-sm-4">
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
                      <button class="btn btn-link btn-sm text-danger" type="button" @click="removeItem(pricePeriod, itemIndex)">{{ $t('Delete') }}</button>
                    </div>
                  </div>

                  <ul v-if="issuesFor(validation.items, item._key).length" class="issue-list issue-list--danger mt-2 mb-0">
                    <li v-for="message in issuesFor(validation.items, item._key)" :key="`${item._key}-${message}`">{{ message }}</li>
                  </ul>
                  <ul v-if="issuesFor(validation.itemWarnings, item._key).length" class="issue-list issue-list--warning mt-2 mb-0">
                    <li v-for="message in issuesFor(validation.itemWarnings, item._key)" :key="`${item._key}-warning-${message}`">{{ message }}</li>
                  </ul>
                </div>
              </div>
            </div>
          </section>

          <div class="wizard-buttons wizard-buttons--mobile">
            <router-link :to="cancelRoute" class="btn btn-grey">{{ $t('Cancel') }}</router-link>
            <button v-if="step > 1" class="btn btn-default" type="button" @click="step--">{{ $t('Back') }}</button>
            <button v-if="step < 2" class="btn btn-default" type="button" @click="step++">{{ $t('Next') }}</button>
            <button v-else class="btn btn-white" type="submit" :disabled="validation.errors.length > 0">
              {{ isNew ? $t('Create profile') : $t('Save profile') }}
            </button>
          </div>
        </div>
      </pending-changes-page>
    </loading-cover>
  </page-container>
</template>

<script setup>
  import {computed, onMounted, ref, watch} from 'vue';
  import {useRouter} from 'vue-router';
  import DateRangePicker from '@/activity/date-range-picker.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import CurrencyPicker from '@/channels/params/currency-picker.vue';
  import PageContainer from '@/common/pages/page-container.vue';
  import PendingChangesPage from '@/common/pages/pending-changes-page.vue';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';
  import {energyTariffsApi} from '@/api/energy-tariffs-api';
  import {successNotification, warningNotification} from '@/common/notifier';
  import {
    billingPeriodUnits,
    cloneProfile,
    componentOptions,
    countPricePeriods,
    createEmptyProfile,
    createItem,
    createPricePeriod,
    createTariffPeriod,
    DEFAULT_PROFILE_START,
    formatRange,
    handleTariffChange,
    hasTariffDefaults,
    normalizeProfile,
    prefillPricePeriodFromTariff,
    selectedTariff,
    syncItemUnit,
    syncPricePeriodsToTariffRange,
    tariffPeriodSummary,
    tariffZones,
    tariffZoneSummary,
    toPayload,
    unitOptionsForItem,
    validateProfile,
  } from './tariff-profile-utils';

  const props = defineProps({id: String});
  const emit = defineEmits(['add', 'update']);
  const router = useRouter();

  const loading = ref(true);
  const submitting = ref(false);
  const error = ref(false);
  const step = ref(1);
  const profile = ref();
  const tariffs = ref([]);

  const isNew = computed(() => !props.id || props.id === 'new');
  const cancelRoute = computed(() => (isNew.value ? {name: 'tariffProfiles'} : {name: 'tariffProfile', params: {id: props.id}}));
  const validation = computed(() => validateProfile(profile.value || createEmptyProfile(), tariffs.value));

  const currentStepErrors = computed(() => (step.value === 1 ? stepOneErrors() : stepTwoErrors()));
  const currentStepWarnings = computed(() => (step.value === 1 ? stepOneWarnings() : stepTwoWarnings()));

  onMounted(loadAll);
  watch(() => props.id, loadAll);

  async function loadAll() {
    loading.value = true;
    error.value = false;
    try {
      const [loadedTariffs, loadedProfile] = await Promise.all([
        energyTariffsApi.getTariffs(),
        isNew.value ? Promise.resolve(createEmptyProfile()) : energyTariffsApi.getTariffProfile(props.id),
      ]);
      tariffs.value = loadedTariffs;
      profile.value = isNew.value ? loadedProfile : normalizeProfile(loadedProfile);
    } catch (response) {
      error.value = response.status;
    } finally {
      loading.value = false;
    }
  }

  function stepOneErrors() {
    const messages = [];
    if (!profile.value?.name?.trim()) {
      messages.push('Profile name is required.');
    }
    return [...messages, ...Object.values(validation.value.tariffPeriods).flat()];
  }

  function stepTwoErrors() {
    return [...Object.values(validation.value.pricePeriods).flat(), ...Object.values(validation.value.items).flat()];
  }

  function stepOneWarnings() {
    return Object.values(validation.value.tariffPeriodWarnings).flat();
  }

  function stepTwoWarnings() {
    return [...Object.values(validation.value.pricePeriodWarnings).flat(), ...Object.values(validation.value.itemWarnings).flat()];
  }

  function issuesFor(map, key) {
    return map[key] || [];
  }

  function addTariffPeriod() {
    const lastTariffPeriod = profile.value.tariffPeriods.at(-1);
    const splitStart = suggestTariffSplitStart(lastTariffPeriod);
    lastTariffPeriod.validTo = splitStart;
    profile.value.tariffPeriods.push(
      createTariffPeriod({
        validFrom: splitStart,
        validTo: null,
      })
    );
    syncPricePeriodsToTariffRange(lastTariffPeriod);
  }

  function removeTariffPeriod(index) {
    const removedTariffPeriod = profile.value.tariffPeriods[index];
    profile.value.tariffPeriods.splice(index, 1);
    if (index > 0 && removedTariffPeriod) {
      profile.value.tariffPeriods[index - 1].validTo = removedTariffPeriod.validTo;
      syncPricePeriodsToTariffRange(profile.value.tariffPeriods[index - 1]);
    } else if (removedTariffPeriod && profile.value.tariffPeriods.length) {
      profile.value.tariffPeriods[0].validFrom = removedTariffPeriod.validFrom;
      syncPricePeriodsToTariffRange(profile.value.tariffPeriods[0]);
    }
  }

  function addPricePeriod(tariffPeriod) {
    const lastPricePeriod = tariffPeriod.pricePeriods.at(-1);
    const splitStart = suggestPriceSplitStart(lastPricePeriod, tariffPeriod);
    lastPricePeriod.validTo = splitStart;
    tariffPeriod.pricePeriods.push(
      createPricePeriod({
        validFrom: splitStart,
        validTo: tariffPeriod.validTo,
      })
    );
    syncPricePeriodsToTariffRange(tariffPeriod);
  }

  function removePricePeriod(tariffPeriod, index) {
    const removedPricePeriod = tariffPeriod.pricePeriods[index];
    tariffPeriod.pricePeriods.splice(index, 1);
    if (index > 0 && removedPricePeriod) {
      tariffPeriod.pricePeriods[index - 1].validTo = removedPricePeriod.validTo;
    } else if (removedPricePeriod && tariffPeriod.pricePeriods.length) {
      tariffPeriod.pricePeriods[0].validFrom = removedPricePeriod.validFrom;
    }
    syncPricePeriodsToTariffRange(tariffPeriod);
  }

  function addItem(pricePeriod, overrides = {}) {
    pricePeriod.items.push(createItem(overrides));
  }

  function removeItem(pricePeriod, index) {
    pricePeriod.items.splice(index, 1);
  }

  function updateTariffPeriodRange(tariffPeriod, value) {
    tariffPeriod.validFrom = value.dateStart;
    tariffPeriod.validTo = value.dateEnd || null;
    autoAdjustTariffNeighbors(tariffPeriod);
    syncPricePeriodsToTariffRange(tariffPeriod);
  }

  function updatePricePeriodRange(pricePeriod, value) {
    pricePeriod.validFrom = value.dateStart;
    pricePeriod.validTo = value.dateEnd || null;
    autoAdjustPriceNeighbors(pricePeriod);
  }

  function onTariffChange(tariffPeriod) {
    handleTariffChange(tariffPeriod, tariffs.value);
    const firstPricePeriod = tariffPeriod.pricePeriods[0];
    if (firstPricePeriod && isInitialPricePeriod(firstPricePeriod)) {
      prefillPricePeriodFromTariff(firstPricePeriod, tariffPeriod, tariffs.value);
      syncPricePeriodsToTariffRange(tariffPeriod);
    }
  }

  function autoAdjustTariffNeighbors(tariffPeriod) {
    const index = profile.value.tariffPeriods.indexOf(tariffPeriod);
    if (index > 0) {
      profile.value.tariffPeriods[index - 1].validTo = tariffPeriod.validFrom;
      syncPricePeriodsToTariffRange(profile.value.tariffPeriods[index - 1]);
    }
    if (index < profile.value.tariffPeriods.length - 1 && tariffPeriod.validTo) {
      profile.value.tariffPeriods[index + 1].validFrom = tariffPeriod.validTo;
      syncPricePeriodsToTariffRange(profile.value.tariffPeriods[index + 1]);
    }
  }

  function autoAdjustPriceNeighbors(pricePeriod) {
    const tariffPeriod = profile.value.tariffPeriods.find((entry) => entry.pricePeriods.includes(pricePeriod));
    if (!tariffPeriod) {
      return;
    }

    const index = tariffPeriod.pricePeriods.indexOf(pricePeriod);
    if (index > 0) {
      tariffPeriod.pricePeriods[index - 1].validTo = pricePeriod.validFrom;
    }
    if (index < tariffPeriod.pricePeriods.length - 1 && pricePeriod.validTo) {
      tariffPeriod.pricePeriods[index + 1].validFrom = pricePeriod.validTo;
    }
  }

  function isInitialPricePeriod(pricePeriod) {
    if (!pricePeriod || pricePeriod.items.length !== 1) {
      return false;
    }

    const [item] = pricePeriod.items;
    return (
      !pricePeriod.name &&
      pricePeriod.billingPeriodLength === 1 &&
      pricePeriod.billingPeriodUnit === 'month' &&
      pricePeriod.currency === 'PLN' &&
      item.componentCode === 'FORWARD_ACTIVE_ENERGY' &&
      item.zoneCode === null &&
      Number(item.amount) === 0 &&
      item.unit === 'kWh'
    );
  }

  function suggestTariffSplitStart(tariffPeriod) {
    const start = new Date(tariffPeriod?.validFrom || DEFAULT_PROFILE_START);
    if (tariffPeriod?.validTo) {
      const end = new Date(tariffPeriod.validTo);
      return new Date(start.getTime() + (end.getTime() - start.getTime()) / 2).toISOString();
    }

    const splitStart = new Date(start);
    splitStart.setFullYear(splitStart.getFullYear() + 1);
    return splitStart.toISOString();
  }

  function suggestPriceSplitStart(pricePeriod, tariffPeriod) {
    const start = new Date(pricePeriod?.validFrom || tariffPeriod?.validFrom || DEFAULT_PROFILE_START);
    if (pricePeriod?.validTo) {
      const end = new Date(pricePeriod.validTo);
      return new Date(start.getTime() + (end.getTime() - start.getTime()) / 2).toISOString();
    }

    const splitStart = new Date(start);
    splitStart.setMonth(splitStart.getMonth() + 1);
    return splitStart.toISOString();
  }

  async function submit() {
    if (validation.value.errors.length) {
      warningNotification('Please fix the highlighted tariff profile issues first.');
      return;
    }

    submitting.value = true;
    try {
      const payload = toPayload(profile.value);
      const savedProfile = isNew.value ? await energyTariffsApi.createTariffProfile(payload) : await energyTariffsApi.updateTariffProfile(props.id, payload);
      const normalized = normalizeProfile(savedProfile);

      successNotification('Tariff profile saved.');
      if (isNew.value) {
        emit('add', normalized);
      } else {
        emit('update', normalized);
        profile.value = cloneProfile(normalized);
        await router.push({name: 'tariffProfile', params: {id: normalized.id}});
      }
    } finally {
      submitting.value = false;
    }
  }
</script>

<style scoped>
  .wizard-shell {
    display: grid;
    gap: 20px;
  }

  .wizard-intro {
    border-radius: 24px;
    background: linear-gradient(135deg, #153f2f, #1f7a4f);
    color: #fff;
    display: flex;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
  }

  .wizard-intro__eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 12px;
    opacity: 0.75;
    margin-bottom: 8px;
  }

  .wizard-intro h2 {
    margin: 0 0 10px;
  }

  .wizard-intro p {
    margin: 0;
    color: rgba(255, 255, 255, 0.86);
    max-width: 720px;
  }

  .wizard-intro__stats {
    display: grid;
    grid-template-columns: repeat(2, minmax(120px, 1fr));
    gap: 12px;
  }

  .wizard-intro__stats > div,
  .helper-card {
    border: 1px solid rgba(31, 122, 79, 0.12);
    border-radius: 16px;
    padding: 12px 14px;
    background: rgba(255, 255, 255, 0.08);
  }

  .wizard-intro__stats span {
    display: block;
    color: rgba(255, 255, 255, 0.8);
    font-size: 13px;
  }

  .wizard-intro__stats strong {
    display: block;
    font-size: 22px;
  }

  .helper-card {
    background: rgba(31, 122, 79, 0.06);
    color: #2b2f33;
  }

  .wizard-steps {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }

  .wizard-step {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 18px;
    background: #fff;
    padding: 16px 18px;
    text-align: left;
    display: grid;
    gap: 4px;
  }

  .wizard-step span {
    width: 28px;
    height: 28px;
    border-radius: 999px;
    background: rgba(31, 122, 79, 0.12);
    color: #1f7a4f;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
  }

  .wizard-step small {
    color: #6f7781;
  }

  .wizard-step.active {
    border-color: #1f7a4f;
    box-shadow: 0 0 0 2px rgba(31, 122, 79, 0.08);
  }

  .wizard-panel,
  .tariff-period-card,
  .price-period-card,
  .price-item-card {
    border-radius: 20px;
  }

  .section-toolbar,
  .tariff-period-card__header,
  .price-period-card__header,
  .price-period-card__actions,
  .price-period-toolbar,
  .tariff-hint,
  .wizard-buttons {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .section-toolbar {
    align-items: center;
    margin-bottom: 16px;
  }

  .compact-alert,
  .issue-list,
  .tariff-hint,
  .tariff-period-card,
  .price-period-card {
    margin-top: 16px;
  }

  .tariff-period-card,
  .price-period-card,
  .price-item-card {
    border: 1px solid rgba(0, 0, 0, 0.08);
    padding: 18px;
    background: #fcfdfd;
  }

  .price-period-card,
  .price-item-card {
    background: #fff;
  }

  .price-item-card {
    margin-top: 12px;
  }

  .issue-list {
    padding-left: 18px;
    margin-bottom: 0;
  }

  .issue-list--danger {
    color: #b04c56;
  }

  .issue-list--warning {
    color: #9a6a17;
  }

  .tariff-hint {
    border-radius: 14px;
    background: rgba(31, 122, 79, 0.08);
    padding: 12px 14px;
    align-items: center;
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

  .wizard-buttons--mobile {
    display: none;
    margin-top: 20px;
    justify-content: flex-end;
  }

  @media (max-width: 991px) {
    .wizard-steps,
    .wizard-intro__stats {
      grid-template-columns: 1fr;
    }

    .wizard-buttons--desktop {
      display: none;
    }

    .wizard-buttons--mobile {
      display: flex;
    }
  }
</style>
