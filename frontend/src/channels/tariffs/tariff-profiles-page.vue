<template>
  <div class="container tariff-profiles-page">
    <div class="profiles-hero">
      <div>
        <div class="eyebrow">{{ $t('Energy tariffs') }}</div>
        <h1 v-title>{{ $t('Tariff profiles') }}</h1>
        <p>
          {{ $t('Build reusable billing profiles with tariff periods, price periods and price components, then assign them to channels from the costs tab.') }}
        </p>
      </div>
      <div class="profiles-hero__actions">
        <div class="btn-group wrapped-mode-group">
          <button
            :class="['btn', editorMode === 'simple' ? 'btn-orange' : 'btn-default']"
            type="button"
            :disabled="!simpleModeAvailable"
            @click="editorMode = 'simple'"
          >
            {{ $t('Simple mode') }}
          </button>
          <button :class="['btn', editorMode === 'advanced' ? 'btn-white' : 'btn-default']" type="button" @click="editorMode = 'advanced'">
            {{ $t('Advanced mode') }}
          </button>
        </div>
        <button class="btn btn-white btn-lg" type="button" @click="startCreatingProfile()">
          {{ $t('Create new profile') }}
        </button>
      </div>
    </div>

    <loading-cover :loading="loading">
      <div class="row g-4" v-if="ready">
        <div class="col-xl-4">
          <div class="details-page-block h-100 profile-list-block">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
              <h3 class="m-0">{{ $t('Profiles') }}</h3>
              <span class="profile-count">{{ profiles.length }}</span>
            </div>

            <div v-if="!profiles.length" class="empty-state text-center text-muted py-4">
              {{ $t('No tariff profiles yet. Start with a simple profile and refine it later if needed.') }}
            </div>

            <button
              v-for="profile in profiles"
              :key="profile.id"
              type="button"
              :class="['profile-card', {'profile-card--active': profile.id === editorProfile.id}]"
              @click="selectProfile(profile)"
            >
              <div class="profile-card__header">
                <strong>{{ profile.name }}</strong>
                <span class="profile-chip">#{{ profile.id }}</span>
              </div>
              <div class="profile-card__meta">
                <span>{{ profile.tariffPeriods.length }} {{ $t('tariff periods') }}</span>
                <span>{{ countPricePeriods(profile) }} {{ $t('price periods') }}</span>
              </div>
              <div class="profile-card__tariffs">
                {{ profileTariffSummary(profile) }}
              </div>
            </button>
          </div>
        </div>

        <div class="col-xl-8">
          <div class="details-page-block editor-block">
            <div class="editor-block__header">
              <div>
                <h3 class="m-0">{{ editorProfile.id ? $t('Edit tariff profile') : $t('Create tariff profile') }}</h3>
                <div class="text-muted small">
                  {{
                    editorMode === 'simple'
                      ? $t('Focused flow for one tariff period and one price period.')
                      : $t('Use multiple tariff periods and price periods when billing rules change over time.')
                  }}
                </div>
              </div>
              <div class="editor-block__actions">
                <button class="btn btn-grey btn-sm" type="button" @click="resetEditorToLastSaved()">{{ $t('Reset') }}</button>
                <button v-if="editorProfile.id" class="btn btn-default btn-sm text-danger" type="button" @click="deleteCurrentProfile()">
                  {{ $t('Delete') }}
                </button>
              </div>
            </div>

            <div v-if="!simpleModeAvailable" class="alert alert-info compact-alert">
              {{ $t('This profile uses multiple tariff or price periods, so advanced mode stays enabled.') }}
            </div>

            <div v-if="validation.errors.length" class="alert alert-danger compact-alert">
              <strong>{{ $t('Please fix the following problems before saving:') }}</strong>
              <ul class="mb-0 mt-2">
                <li v-for="message in validation.errors" :key="`error-${message}`">{{ message }}</li>
              </ul>
            </div>

            <div v-if="validation.warnings.length" class="alert alert-warning compact-alert">
              <strong>{{ $t('Review these warnings:') }}</strong>
              <ul class="mb-0 mt-2">
                <li v-for="message in validation.warnings" :key="`warning-${message}`">{{ message }}</li>
              </ul>
            </div>

            <form @submit.prevent="saveProfile()">
              <div class="row g-3 align-items-end">
                <div class="col-lg-8">
                  <div class="form-group mb-0">
                    <label>{{ $t('Profile name') }}</label>
                    <input v-model="editorProfile.name" class="form-control" type="text" :placeholder="$t('e.g. Home 2026 tariff profile')" />
                  </div>
                </div>
                <div class="col-lg-4">
                  <div class="profile-stats-grid">
                    <div>
                      <span>{{ $t('Tariff periods') }}</span>
                      <strong>{{ editorProfile.tariffPeriods.length }}</strong>
                    </div>
                    <div>
                      <span>{{ $t('Price periods') }}</span>
                      <strong>{{ countPricePeriods(editorProfile) }}</strong>
                    </div>
                  </div>
                </div>
              </div>

              <div class="section-toolbar mt-4">
                <div>
                  <h4 class="m-0">{{ $t('Tariff periods') }}</h4>
                  <div class="text-muted small">{{ $t('Each tariff period selects a tariff definition and a time range when it should apply.') }}</div>
                </div>
                <button v-if="editorMode === 'advanced'" class="btn btn-default btn-sm" type="button" @click="addTariffPeriod()">
                  {{ $t('Add tariff period') }}
                </button>
              </div>

              <div v-for="(tariffPeriod, tariffPeriodIndex) in visibleTariffPeriods" :key="tariffPeriod._key" class="tariff-period-card">
                <div class="tariff-period-card__header">
                  <div>
                    <strong>{{ $t('Tariff period') }} {{ tariffPeriodIndex + 1 }}</strong>
                    <div class="small text-muted">{{ tariffPeriodSummary(tariffPeriod) }}</div>
                  </div>
                  <div class="tariff-period-card__actions">
                    <button class="btn btn-default btn-sm" type="button" @click="syncPricePeriodsToTariffRange(tariffPeriod)">
                      {{ $t('Cover full range') }}
                    </button>
                    <button
                      v-if="editorMode === 'advanced' && editorProfile.tariffPeriods.length > 1"
                      class="btn btn-link btn-sm text-danger"
                      type="button"
                      @click="removeTariffPeriod(tariffPeriodIndex)"
                    >
                      {{ $t('Delete') }}
                    </button>
                  </div>
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
                      <select v-model.number="tariffPeriod.tariffId" class="form-control" @change="handleTariffChange(tariffPeriod)">
                        <option :value="null">{{ $t('Choose tariff') }}</option>
                        <option v-for="tariff in tariffs" :key="tariff.id" :value="tariff.id">{{ tariff.name }} ({{ tariff.code }})</option>
                      </select>
                    </div>
                  </div>
                  <div class="col-lg-7">
                    <label>{{ $t('Validity') }}</label>
                    <DateRangePicker
                      :value="{dateStart: tariffPeriod.validFrom, dateEnd: tariffPeriod.validTo}"
                      @input="(value) => updateTariffPeriodRange(tariffPeriod, value)"
                    />
                  </div>
                </div>

                <div v-if="selectedTariff(tariffPeriod.tariffId)" class="tariff-hint mt-3">
                  <div>
                    <strong>{{ selectedTariff(tariffPeriod.tariffId)?.code }}</strong>
                    <span>{{ selectedTariff(tariffPeriod.tariffId)?.config?.timezone || 'UTC' }}</span>
                  </div>
                  <div>
                    {{ tariffZoneSummary(selectedTariff(tariffPeriod.tariffId)) }}
                  </div>
                </div>

                <div class="section-toolbar section-toolbar--nested mt-4">
                  <div>
                    <h5 class="m-0">{{ $t('Price periods') }}</h5>
                    <div class="text-muted small">{{ $t('Price periods inside a tariff period must cover the full range without gaps.') }}</div>
                  </div>
                  <button v-if="editorMode === 'advanced'" class="btn btn-default btn-sm" type="button" @click="addPricePeriod(tariffPeriod)">
                    {{ $t('Add price period') }}
                  </button>
                </div>

                <div v-for="(pricePeriod, pricePeriodIndex) in visiblePricePeriods(tariffPeriod)" :key="pricePeriod._key" class="price-period-card">
                  <div class="price-period-card__header">
                    <strong>{{ $t('Price period') }} {{ pricePeriodIndex + 1 }}</strong>
                    <button
                      v-if="editorMode === 'advanced' && tariffPeriod.pricePeriods.length > 1"
                      class="btn btn-link btn-sm text-danger"
                      type="button"
                      @click="removePricePeriod(tariffPeriod, pricePeriodIndex)"
                    >
                      {{ $t('Delete') }}
                    </button>
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
                        <input v-model="pricePeriod.name" class="form-control" type="text" :placeholder="$t('e.g. Winter billing 2026')" />
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

                  <div class="mt-3">
                    <label>{{ $t('Validity') }}</label>
                    <DateRangePicker
                      :value="{dateStart: pricePeriod.validFrom, dateEnd: pricePeriod.validTo}"
                      @input="(value) => updatePricePeriodRange(pricePeriod, value)"
                    />
                  </div>

                  <div class="price-period-toolbar mt-3">
                    <div>
                      <strong>{{ $t('Price components') }}</strong>
                      <div class="text-muted small">{{ $t('Combine energy, distribution and fixed-fee components as needed.') }}</div>
                    </div>
                    <div class="price-period-toolbar__actions">
                      <button class="btn btn-default btn-sm" type="button" @click="prefillPricePeriodFromTariff(pricePeriod, tariffPeriod)">
                        {{ hasTariffDefaults(tariffPeriod) ? $t('Prefill from tariff defaults') : $t('Create zone rows') }}
                      </button>
                      <button class="btn btn-default btn-sm" type="button" @click="addItem(pricePeriod)">{{ $t('Add component') }}</button>
                    </div>
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
                          <option v-for="zone in tariffZones(tariffPeriod)" :key="zone.code" :value="zone.code">{{ zone.name || zone.code }}</option>
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

              <div class="text-end mt-4">
                <button class="btn btn-white" type="submit" :disabled="saving || validation.errors.length > 0">
                  {{ saving ? $t('Saving') : editorProfile.id ? $t('Save profile') : $t('Create profile') }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </loading-cover>
  </div>
</template>

<script setup>
  import {computed, ref} from 'vue';
  import {DateTime} from 'luxon';
  import DateRangePicker from '@/activity/date-range-picker.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import CurrencyPicker from '@/channels/params/currency-picker.vue';
  import {energyTariffsApi} from '@/api/energy-tariffs-api';
  import {successNotification, warningNotification} from '@/common/notifier';
  import {formatDateTime} from '@/common/filters-date';

  const billingPeriodUnits = ['day', 'week', 'month', 'year'];
  const componentOptions = [
    {value: 'FORWARD_ACTIVE_ENERGY', label: 'FORWARD_ACTIVE_ENERGY'},
    {value: 'DISTRIBUTION_VARIABLE', label: 'DISTRIBUTION_VARIABLE'},
    {value: 'DISTRIBUTION_FIXED', label: 'DISTRIBUTION_FIXED'},
    {value: 'FEE_VARIABLE', label: 'FEE_VARIABLE'},
    {value: 'FEE_FIXED', label: 'FEE_FIXED'},
  ];
  const componentUnitMap = {
    FORWARD_ACTIVE_ENERGY: ['kWh'],
    DISTRIBUTION_VARIABLE: ['kWh'],
    DISTRIBUTION_FIXED: ['day', 'week', 'month', 'period'],
    FEE_VARIABLE: ['day', 'week', 'month', 'period'],
    FEE_FIXED: ['day', 'week', 'month', 'period'],
  };

  const loading = ref(true);
  const ready = ref(false);
  const saving = ref(false);
  const profiles = ref([]);
  const tariffs = ref([]);
  const editorMode = ref('simple');
  const editorProfile = ref(createEmptyProfile());
  const lastSavedSnapshot = ref(createEmptyProfile());

  const simpleModeAvailable = computed(() => isSimpleCompatible(editorProfile.value));
  const visibleTariffPeriods = computed(() =>
    editorMode.value === 'simple' ? editorProfile.value.tariffPeriods.slice(0, 1) : editorProfile.value.tariffPeriods
  );

  const validation = computed(() => validateProfile(editorProfile.value, tariffs.value));

  loadAll();

  function createKey() {
    return globalThis.crypto?.randomUUID?.() || `${Date.now()}-${Math.random().toString(16).slice(2)}`;
  }

  async function loadAll() {
    loading.value = true;
    try {
      const [loadedProfiles, loadedTariffs] = await Promise.all([energyTariffsApi.getTariffProfiles(), energyTariffsApi.getTariffs()]);
      profiles.value = loadedProfiles.map(normalizeProfile);
      tariffs.value = loadedTariffs;

      if (profiles.value.length) {
        selectProfile(profiles.value[0]);
      } else {
        startCreatingProfile();
      }
      ready.value = true;
    } finally {
      loading.value = false;
    }
  }

  function createEmptyProfile() {
    return {
      id: null,
      name: '',
      tariffPeriods: [createTariffPeriod()],
    };
  }

  function createTariffPeriod(overrides = {}) {
    const now = DateTime.now().startOf('month').toISO();
    return {
      _key: createKey(),
      id: null,
      tariffId: null,
      validFrom: now,
      validTo: null,
      pricePeriods: [createPricePeriod({validFrom: now})],
      ...overrides,
    };
  }

  function createPricePeriod(overrides = {}) {
    return {
      _key: createKey(),
      id: null,
      name: '',
      billingPeriodLength: 1,
      billingPeriodUnit: 'month',
      currency: 'PLN',
      validFrom: DateTime.now().startOf('month').toISO(),
      validTo: null,
      items: [createItem()],
      ...overrides,
    };
  }

  function createItem(overrides = {}) {
    return {
      _key: createKey(),
      id: null,
      componentCode: 'FORWARD_ACTIVE_ENERGY',
      zoneCode: null,
      amount: 0,
      unit: 'kWh',
      ...overrides,
    };
  }

  function normalizeProfile(profile) {
    return {
      id: profile.id,
      name: profile.name,
      tariffPeriods: (profile.tariffPeriods || []).map((tariffPeriod) => ({
        _key: createKey(),
        id: tariffPeriod.id || null,
        tariffId: tariffPeriod.tariffId || tariffPeriod.tariff?.id || null,
        validFrom: tariffPeriod.validFrom || null,
        validTo: tariffPeriod.validTo || null,
        pricePeriods: (tariffPeriod.pricePeriods || []).map((pricePeriod) => ({
          _key: createKey(),
          id: pricePeriod.id || null,
          name: pricePeriod.name || '',
          billingPeriodLength: pricePeriod.billingPeriodLength || 1,
          billingPeriodUnit: pricePeriod.billingPeriodUnit || 'month',
          currency: pricePeriod.currency || 'PLN',
          validFrom: pricePeriod.validFrom || tariffPeriod.validFrom || null,
          validTo: pricePeriod.validTo || null,
          items: (pricePeriod.items || []).map((item) => ({
            _key: createKey(),
            id: item.id || null,
            componentCode: item.componentCode || '',
            zoneCode: item.zoneCode ?? null,
            amount: item.amount ?? 0,
            unit: item.unit || 'kWh',
          })),
        })),
      })),
    };
  }

  function cloneProfile(profile) {
    return normalizeProfile(JSON.parse(JSON.stringify(profile)));
  }

  function startCreatingProfile() {
    editorProfile.value = createEmptyProfile();
    lastSavedSnapshot.value = createEmptyProfile();
    editorMode.value = 'simple';
  }

  function selectProfile(profile) {
    editorProfile.value = cloneProfile(profile);
    lastSavedSnapshot.value = cloneProfile(profile);
    editorMode.value = isSimpleCompatible(editorProfile.value) ? 'simple' : 'advanced';
  }

  function resetEditorToLastSaved() {
    editorProfile.value = cloneProfile(lastSavedSnapshot.value);
    editorMode.value = isSimpleCompatible(editorProfile.value) ? 'simple' : 'advanced';
  }

  function countPricePeriods(profile) {
    return (profile.tariffPeriods || []).reduce((sum, tariffPeriod) => sum + (tariffPeriod.pricePeriods?.length || 0), 0);
  }

  function profileTariffSummary(profile) {
    const names = [...new Set(profile.tariffPeriods.map((period) => selectedTariff(period.tariffId)?.code).filter(Boolean))];
    return names.length ? names.join(', ') : '—';
  }

  function tariffPeriodSummary(tariffPeriod) {
    const tariff = selectedTariff(tariffPeriod.tariffId);
    return `${tariff?.name || '—'} · ${formatRange(tariffPeriod.validFrom, tariffPeriod.validTo)}`;
  }

  function selectedTariff(tariffId) {
    return tariffs.value.find((tariff) => tariff.id === tariffId);
  }

  function tariffZones(tariffPeriod) {
    return selectedTariff(tariffPeriod.tariffId)?.config?.zones || [];
  }

  function tariffZoneSummary(tariff) {
    const zones = tariff?.config?.zones || [];
    return zones.length ? zones.map((zone) => zone.name || zone.code).join(', ') : '—';
  }

  function formatRange(validFrom, validTo) {
    if (!validFrom) {
      return '—';
    }
    return `${formatDateTime(validFrom)} – ${validTo ? formatDateTime(validTo) : '∞'}`;
  }

  function addTariffPeriod() {
    const lastTariffPeriod = editorProfile.value.tariffPeriods.at(-1);
    editorProfile.value.tariffPeriods.push(
      createTariffPeriod({
        validFrom: lastTariffPeriod?.validTo || lastTariffPeriod?.validFrom || DateTime.now().toISO(),
      })
    );
    editorMode.value = 'advanced';
  }

  function removeTariffPeriod(index) {
    editorProfile.value.tariffPeriods.splice(index, 1);
  }

  function addPricePeriod(tariffPeriod) {
    const lastPricePeriod = tariffPeriod.pricePeriods.at(-1);
    tariffPeriod.pricePeriods.push(
      createPricePeriod({
        validFrom: lastPricePeriod?.validTo || tariffPeriod.validFrom || DateTime.now().toISO(),
        validTo: tariffPeriod.validTo,
      })
    );
    editorMode.value = 'advanced';
  }

  function removePricePeriod(tariffPeriod, index) {
    tariffPeriod.pricePeriods.splice(index, 1);
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
  }

  function updatePricePeriodRange(pricePeriod, value) {
    pricePeriod.validFrom = value.dateStart;
    pricePeriod.validTo = value.dateEnd || null;
  }

  function visiblePricePeriods(tariffPeriod) {
    return editorMode.value === 'simple' ? tariffPeriod.pricePeriods.slice(0, 1) : tariffPeriod.pricePeriods;
  }

  function issuesFor(map, key) {
    return map[key] || [];
  }

  function unitOptionsForItem(item) {
    return componentUnitMap[item.componentCode] || ['kWh', 'day', 'week', 'month', 'period'];
  }

  function syncItemUnit(item) {
    const allowedUnits = unitOptionsForItem(item);
    if (!allowedUnits.includes(item.unit)) {
      item.unit = allowedUnits[0];
    }
  }

  function handleTariffChange(tariffPeriod) {
    tariffPeriod.pricePeriods.forEach((pricePeriod) => {
      pricePeriod.items.forEach((item) => {
        if (item.zoneCode && !tariffZones(tariffPeriod).some((zone) => zone.code === item.zoneCode)) {
          item.zoneCode = null;
        }
      });
    });
  }

  function hasTariffDefaults(tariffPeriod) {
    return extractTariffDefaults(selectedTariff(tariffPeriod.tariffId)).items.length > 0;
  }

  function prefillPricePeriodFromTariff(pricePeriod, tariffPeriod) {
    const defaults = extractTariffDefaults(selectedTariff(tariffPeriod.tariffId));
    if (defaults.items.length) {
      pricePeriod.items = defaults.items.map((item) => createItem(item));
      if (!pricePeriod.name) {
        pricePeriod.name = defaults.name || '';
      }
      if (defaults.currency) {
        pricePeriod.currency = defaults.currency;
      }
      if (defaults.billingPeriodLength) {
        pricePeriod.billingPeriodLength = defaults.billingPeriodLength;
      }
      if (defaults.billingPeriodUnit) {
        pricePeriod.billingPeriodUnit = defaults.billingPeriodUnit;
      }
      return;
    }

    const zones = tariffZones(tariffPeriod);
    pricePeriod.items = zones.length ? zones.map((zone) => createItem({zoneCode: zone.code})) : [createItem()];
  }

  function extractTariffDefaults(tariff) {
    const config = tariff?.config || {};
    const defaults =
      [config.defaultPrices, config.profileDefaults, config.priceDefaults, config.defaults].find(
        (candidate) => candidate && !Array.isArray(candidate) && typeof candidate === 'object'
      ) || {};
    const itemSource =
      [
        defaults.items,
        defaults.priceItems,
        Array.isArray(config.defaultPrices?.items) ? config.defaultPrices.items : null,
        Array.isArray(config.defaultPriceItems) ? config.defaultPriceItems : null,
        Array.isArray(config.suggestedPriceItems) ? config.suggestedPriceItems : null,
      ].find(Array.isArray) || [];

    return {
      name: defaults.name || defaults.pricePeriodName || '',
      currency: defaults.currency || 'PLN',
      billingPeriodLength: Number(defaults.billingPeriodLength || 1),
      billingPeriodUnit: defaults.billingPeriodUnit || 'month',
      items: itemSource
        .map((item) => ({
          componentCode: item.componentCode || item.component || '',
          zoneCode: item.zoneCode ?? item.zone ?? null,
          amount: Number(item.amount || 0),
          unit: item.unit || 'kWh',
        }))
        .filter((item) => item.componentCode),
    };
  }

  function syncPricePeriodsToTariffRange(tariffPeriod) {
    if (!tariffPeriod.pricePeriods.length) {
      tariffPeriod.pricePeriods = [createPricePeriod({validFrom: tariffPeriod.validFrom, validTo: tariffPeriod.validTo})];
      return;
    }

    tariffPeriod.pricePeriods.forEach((pricePeriod, index) => {
      if (index === 0) {
        pricePeriod.validFrom = tariffPeriod.validFrom;
      } else {
        pricePeriod.validFrom = tariffPeriod.pricePeriods[index - 1].validTo;
      }

      if (index === tariffPeriod.pricePeriods.length - 1) {
        pricePeriod.validTo = tariffPeriod.validTo;
      }
    });
  }

  function isSimpleCompatible(profile) {
    return profile.tariffPeriods.length <= 1 && profile.tariffPeriods.every((tariffPeriod) => tariffPeriod.pricePeriods.length <= 1);
  }

  async function saveProfile() {
    if (validation.value.errors.length) {
      warningNotification('Please fix the highlighted tariff profile issues first.');
      return;
    }

    saving.value = true;
    try {
      const payload = toPayload(editorProfile.value);
      const savedProfile = editorProfile.value.id
        ? await energyTariffsApi.updateTariffProfile(editorProfile.value.id, payload)
        : await energyTariffsApi.createTariffProfile(payload);

      const normalized = normalizeProfile(savedProfile);
      const existingIndex = profiles.value.findIndex((profile) => profile.id === normalized.id);
      if (existingIndex >= 0) {
        profiles.value.splice(existingIndex, 1, normalized);
      } else {
        profiles.value.push(normalized);
      }
      profiles.value.sort((left, right) => left.id - right.id);
      selectProfile(normalized);
      successNotification('Tariff profile saved.');
    } finally {
      saving.value = false;
    }
  }

  async function deleteCurrentProfile() {
    if (!editorProfile.value.id || !window.confirm('Delete this tariff profile?')) {
      return;
    }

    await energyTariffsApi.deleteTariffProfile(editorProfile.value.id);
    profiles.value = profiles.value.filter((profile) => profile.id !== editorProfile.value.id);
    successNotification('Tariff profile deleted.');

    if (profiles.value.length) {
      selectProfile(profiles.value[0]);
    } else {
      startCreatingProfile();
    }
  }

  function toPayload(profile) {
    return {
      name: profile.name,
      tariffPeriods: profile.tariffPeriods.map((tariffPeriod) => ({
        tariffId: tariffPeriod.tariffId,
        validFrom: tariffPeriod.validFrom,
        validTo: tariffPeriod.validTo,
        pricePeriods: tariffPeriod.pricePeriods.map((pricePeriod) => ({
          name: pricePeriod.name,
          billingPeriodLength: Number(pricePeriod.billingPeriodLength),
          billingPeriodUnit: pricePeriod.billingPeriodUnit,
          currency: pricePeriod.currency,
          validFrom: pricePeriod.validFrom,
          validTo: pricePeriod.validTo,
          items: pricePeriod.items.map((item) => ({
            componentCode: item.componentCode,
            zoneCode: item.zoneCode,
            amount: Number(item.amount),
            unit: item.unit,
          })),
        })),
      })),
    };
  }

  function validateProfile(profile, availableTariffs) {
    const result = {
      errors: [],
      warnings: [],
      tariffPeriods: {},
      tariffPeriodWarnings: {},
      pricePeriods: {},
      pricePeriodWarnings: {},
      items: {},
      itemWarnings: {},
    };

    if (!profile.name?.trim()) {
      result.errors.push('Profile name is required.');
    }
    if (!profile.tariffPeriods.length) {
      result.errors.push('Add at least one tariff period.');
      return result;
    }

    const sortedTariffPeriods = [...profile.tariffPeriods].sort(compareByStart);
    let previousTariffPeriod = null;

    sortedTariffPeriods.forEach((tariffPeriod) => {
      const tariff = availableTariffs.find((entry) => entry.id === tariffPeriod.tariffId);
      const tariffErrors = [];
      const tariffWarnings = [];
      const periodStart = parseDateTime(tariffPeriod.validFrom);
      const periodEnd = parseDateTime(tariffPeriod.validTo);

      if (!tariffPeriod.tariffId) {
        tariffErrors.push('Choose a tariff definition.');
      }
      if (!periodStart) {
        tariffErrors.push('Tariff period start is required.');
      }
      if (periodStart && periodEnd && periodEnd <= periodStart) {
        tariffErrors.push('Tariff period end must be later than start.');
      }
      if (previousTariffPeriod?.end && periodStart) {
        if (periodStart < previousTariffPeriod.end) {
          tariffErrors.push(`Overlaps with the previous tariff period ending ${formatDateTime(previousTariffPeriod.end.toISO())}.`);
        } else if (periodStart > previousTariffPeriod.end) {
          tariffWarnings.push(
            `No tariff profile coverage between ${formatDateTime(previousTariffPeriod.end.toISO())} and ${formatDateTime(periodStart.toISO())}.`
          );
        }
      }

      validatePricePeriods(tariffPeriod, tariff, periodStart, periodEnd, result);

      if (tariffErrors.length) {
        result.tariffPeriods[tariffPeriod._key] = tariffErrors;
        result.errors.push(...tariffErrors.map((message) => `${profile.name || 'Profile'}: ${message}`));
      }
      if (tariffWarnings.length) {
        result.tariffPeriodWarnings[tariffPeriod._key] = tariffWarnings;
        result.warnings.push(...tariffWarnings);
      }

      previousTariffPeriod = {end: periodEnd};
    });

    result.errors = [...new Set(result.errors)];
    result.warnings = [...new Set(result.warnings)];
    return result;
  }

  function validatePricePeriods(tariffPeriod, tariff, tariffStart, tariffEnd, result) {
    const zones = tariff?.config?.zones?.map((zone) => zone.code) || [];
    if (!tariffPeriod.pricePeriods.length) {
      pushIssue(result.pricePeriods, tariffPeriod._key, 'Add at least one price period.');
      result.errors.push('Each tariff period needs at least one price period.');
      return;
    }

    const sortedPricePeriods = [...tariffPeriod.pricePeriods].sort(compareByStart);
    let previousPricePeriod = null;

    sortedPricePeriods.forEach((pricePeriod, pricePeriodIndex) => {
      const errors = [];
      const warnings = [];
      const priceStart = parseDateTime(pricePeriod.validFrom);
      const priceEnd = parseDateTime(pricePeriod.validTo);

      if (!pricePeriod.name?.trim()) {
        errors.push('Price period name is required.');
      }
      if (!Number.isInteger(Number(pricePeriod.billingPeriodLength)) || Number(pricePeriod.billingPeriodLength) <= 0) {
        errors.push('Billing period length must be greater than 0.');
      }
      if (!billingPeriodUnits.includes(pricePeriod.billingPeriodUnit)) {
        errors.push('Choose a valid billing period unit.');
      }
      if (!/^[A-Z]{3}$/.test(pricePeriod.currency || '')) {
        errors.push('Currency must use a 3-letter ISO code.');
      }
      if (!priceStart) {
        errors.push('Price period start is required.');
      }
      if (priceStart && priceEnd && priceEnd <= priceStart) {
        errors.push('Price period end must be later than start.');
      }
      if (tariffStart && priceStart && priceStart < tariffStart) {
        errors.push('Price period cannot start before its tariff period.');
      }
      if (tariffEnd && !priceEnd) {
        errors.push('Price period must end inside the tariff period.');
      }
      if (tariffEnd && priceEnd && priceEnd > tariffEnd) {
        errors.push('Price period cannot end after its tariff period.');
      }
      if (pricePeriodIndex === 0 && tariffStart && priceStart && priceStart > tariffStart) {
        errors.push('Price periods do not cover the tariff period start.');
      }
      if (previousPricePeriod?.end && priceStart) {
        if (priceStart < previousPricePeriod.end) {
          errors.push('Price periods overlap.');
        } else if (priceStart > previousPricePeriod.end) {
          errors.push('Price periods leave an uncovered gap.');
        }
      }

      if (!pricePeriod.items.length) {
        errors.push('Add at least one price component.');
      }

      const duplicateSignatures = new Set();
      pricePeriod.items.forEach((item) => {
        const signature = `${item.componentCode}::${item.zoneCode || 'none'}`;
        if (duplicateSignatures.has(signature)) {
          warnings.push(`Repeated component/zone pair: ${signature}.`);
        }
        duplicateSignatures.add(signature);
        validateItem(item, zones, result);
      });

      if (errors.length) {
        result.pricePeriods[pricePeriod._key] = errors;
        result.errors.push(...errors);
      }
      if (warnings.length) {
        result.pricePeriodWarnings[pricePeriod._key] = warnings;
        result.warnings.push(...warnings);
      }
      previousPricePeriod = {end: priceEnd};
    });

    const lastPricePeriod = sortedPricePeriods.at(-1);
    const lastPricePeriodEnd = parseDateTime(lastPricePeriod?.validTo);
    if (tariffEnd && lastPricePeriodEnd && lastPricePeriodEnd < tariffEnd) {
      pushIssue(result.pricePeriods, lastPricePeriod._key, 'Price periods do not cover the tariff period end.');
      result.errors.push('Price periods do not cover the tariff period end.');
    }
    if (!tariffEnd && lastPricePeriod && lastPricePeriod.validTo) {
      pushIssue(result.pricePeriods, lastPricePeriod._key, 'Last price period should stay open-ended because the tariff period is open-ended.');
      result.errors.push('Open-ended tariff periods require an open-ended last price period.');
    }
  }

  function validateItem(item, zones, result) {
    const errors = [];
    const warnings = [];
    const allowedUnits = componentUnitMap[item.componentCode];

    if (!item.componentCode || !allowedUnits) {
      errors.push('Choose a valid component.');
    }
    if (item.amount === null || item.amount === undefined || Number.isNaN(Number(item.amount)) || Number(item.amount) < 0) {
      errors.push('Amount must be 0 or greater.');
    }
    if (!allowedUnits?.includes(item.unit)) {
      errors.push('Selected unit does not match the component.');
    }
    if (item.zoneCode && zones.length && !zones.includes(item.zoneCode)) {
      errors.push('Selected zone does not exist in the tariff.');
    }
    if (!item.zoneCode && zones.length > 1 && item.unit === 'kWh') {
      warnings.push('Consider selecting a zone for kWh-based pricing on multi-zone tariffs.');
    }

    if (errors.length) {
      result.items[item._key] = errors;
      result.errors.push(...errors);
    }
    if (warnings.length) {
      result.itemWarnings[item._key] = warnings;
      result.warnings.push(...warnings);
    }
  }

  function pushIssue(collection, key, message) {
    collection[key] = [...(collection[key] || []), message];
  }

  function compareByStart(left, right) {
    const leftStart = parseDateTime(left.validFrom)?.toMillis() || 0;
    const rightStart = parseDateTime(right.validFrom)?.toMillis() || 0;
    return leftStart - rightStart;
  }

  function parseDateTime(value) {
    if (!value) {
      return null;
    }
    const parsed = DateTime.fromISO(value);
    return parsed.isValid ? parsed : null;
  }
</script>

<style scoped>
  .profiles-hero {
    background: linear-gradient(135deg, #153f2f, #1f7a4f);
    color: #fff;
    border-radius: 24px;
    padding: 28px;
    margin: 24px 0;
    display: flex;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
  }

  .eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    opacity: 0.75;
    font-size: 12px;
    margin-bottom: 8px;
  }

  .profiles-hero h1 {
    margin: 0 0 12px;
    font-size: 34px;
  }

  .profiles-hero p {
    max-width: 760px;
    margin: 0;
    color: rgba(255, 255, 255, 0.85);
  }

  .profiles-hero__actions {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 12px;
  }

  .profile-list-block,
  .editor-block {
    border-radius: 20px;
  }

  .profile-count,
  .profile-chip {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 32px;
    padding: 4px 10px;
    border-radius: 999px;
    background: rgba(31, 122, 79, 0.12);
    color: #1f7a4f;
    font-size: 12px;
    font-weight: 700;
  }

  .profile-card {
    width: 100%;
    text-align: left;
    border: 1px solid rgba(31, 122, 79, 0.12);
    border-radius: 18px;
    background: #fff;
    padding: 16px;
    margin-bottom: 12px;
  }

  .profile-card--active {
    border-color: #1f7a4f;
    box-shadow: 0 0 0 2px rgba(31, 122, 79, 0.12);
  }

  .profile-card__header,
  .profile-card__meta,
  .editor-block__header,
  .editor-block__actions,
  .section-toolbar,
  .tariff-period-card__header,
  .tariff-period-card__actions,
  .price-period-card__header,
  .price-period-toolbar,
  .price-period-toolbar__actions,
  .tariff-hint {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .profile-card__meta,
  .profile-card__tariffs,
  .compact-alert,
  .issue-list,
  .tariff-hint {
    margin-top: 10px;
  }

  .profile-card__meta,
  .profile-card__tariffs,
  .tariff-hint,
  .profile-stats-grid span {
    color: #6f7781;
    font-size: 13px;
  }

  .profile-stats-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
  }

  .profile-stats-grid > div {
    border: 1px solid rgba(31, 122, 79, 0.12);
    border-radius: 14px;
    padding: 10px 12px;
  }

  .profile-stats-grid strong {
    display: block;
    font-size: 20px;
    color: #2b2f33;
  }

  .section-toolbar {
    align-items: center;
    margin-bottom: 16px;
  }

  .section-toolbar--nested {
    margin-bottom: 12px;
  }

  .tariff-period-card,
  .price-period-card,
  .price-item-card {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 18px;
    padding: 18px;
    background: #fcfdfd;
  }

  .tariff-period-card,
  .price-period-card {
    margin-top: 16px;
  }

  .price-item-card {
    margin-top: 12px;
    background: #fff;
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

  .btn-orange {
    background: #ffb703;
    color: #000;
  }

  @media (max-width: 991px) {
    .profiles-hero {
      border-radius: 20px;
      padding: 24px;
    }

    .profiles-hero__actions {
      align-items: stretch;
      width: 100%;
    }
  }
</style>
