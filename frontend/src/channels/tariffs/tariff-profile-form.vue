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

        <div class="form-group">
          <label>{{ $t('Profile name') }}</label>
          <input v-model="profile.name" class="form-control" type="text" :placeholder="$t('e.g. Home 2026 tariff profile')" />
        </div>

        <div class="wizard-shell">
          <div class="wizard-steps">
            <button type="button" :class="['wizard-step', {active: step === 1}]" @click="step = 1">
              <strong>{{ $t('Tariff base') }}</strong>
              <small>{{ $t('Pick tariffs and date ranges') }}</small>
            </button>
            <button type="button" :class="['wizard-step', {active: step === 2}]" @click="step = 2">
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

          <TariffProfileTariffStep
            v-show="step === 1"
            :profile="profile"
            :tariffs="tariffs"
            :validation="validation"
            @add-tariff-period="addTariffPeriod()"
            @remove-tariff-period="removeTariffPeriod($event)"
            @tariff-change="onTariffChange($event)"
            @update-tariff-period-range="updateTariffPeriodRange($event.tariffPeriod, $event.value)"
          />

          <TariffProfilePriceStep
            v-show="step === 2"
            :profile="profile"
            :tariffs="tariffs"
            :validation="validation"
            @add-price-period="addPricePeriod($event)"
            @remove-price-period="removePricePeriod($event.tariffPeriod, $event.index)"
            @prefill-price-period="prefillPricePeriodFromTariff($event.pricePeriod, $event.tariffPeriod, tariffs)"
            @update-price-period-range="updatePricePeriodRange($event.pricePeriod, $event.value)"
            @add-item="addItem($event)"
            @remove-item="removeItem($event.pricePeriod, $event.index)"
          />

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
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import PageContainer from '@/common/pages/page-container.vue';
  import PendingChangesPage from '@/common/pages/pending-changes-page.vue';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';
  import {energyTariffsApi} from '@/api/energy-tariffs-api';
  import {successNotification, warningNotification} from '@/common/notifier';
  import TariffProfileTariffStep from './tariff-profile-tariff-step.vue';
  import TariffProfilePriceStep from './tariff-profile-price-step.vue';
  import {
    cloneProfile,
    createEmptyProfile,
    createItem,
    createPricePeriod,
    createTariffPeriod,
    handleTariffChange,
    normalizeProfile,
    prefillPricePeriodFromTariff,
    syncPricePeriodsToTariffRange,
    toPayload,
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
      if (isNew.value && loadedTariffs.length) {
        profile.value.tariffPeriods[0].tariffId = loadedTariffs[0].id;
        prefillPricePeriodFromTariff(profile.value.tariffPeriods[0].pricePeriods[0], profile.value.tariffPeriods[0], loadedTariffs);
      }
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
    const start = tariffPeriod?.validFrom ? new Date(tariffPeriod.validFrom) : new Date();
    if (tariffPeriod?.validTo) {
      const end = new Date(tariffPeriod.validTo);
      return new Date(start.getTime() + (end.getTime() - start.getTime()) / 2).toISOString();
    }

    const splitStart = new Date(start);
    splitStart.setHours(0, 0, 0, 0);
    splitStart.setMonth(splitStart.getMonth() + 1);
    return splitStart.toISOString();
  }

  function suggestPriceSplitStart(pricePeriod, tariffPeriod) {
    const start = pricePeriod?.validFrom ? new Date(pricePeriod.validFrom) : tariffPeriod?.validFrom ? new Date(tariffPeriod.validFrom) : new Date();
    if (pricePeriod?.validTo) {
      const end = new Date(pricePeriod.validTo);
      return new Date(start.getTime() + (end.getTime() - start.getTime()) / 2).toISOString();
    }

    const splitStart = new Date(start);
    splitStart.setHours(0, 0, 0, 0);
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

  .wizard-buttons {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .compact-alert {
    margin-top: 16px;
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
