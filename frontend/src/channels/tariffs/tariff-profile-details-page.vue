<template>
  <page-container :error="error">
    <tariff-profile-form v-if="id === 'new'" @add="$emit('add', $event)"></tariff-profile-form>

    <loading-cover v-else :loading="loading || !profile">
      <div v-if="profile" class="container tariff-profile-details-page">
        <BreadcrumbList :current="profile.name">
          <RouterLink :to="{name: 'tariffProfiles'}">{{ $t('Tariff profiles') }}</RouterLink>
        </BreadcrumbList>

        <div class="details-page-block tariff-profile-hero">
          <div>
            <div class="tariff-profile-hero__eyebrow">{{ $t('Energy tariffs') }}</div>
            <h1 v-title>{{ profile.name }}</h1>
            <p>{{ $t('Review tariff periods and pricing rules before assigning this profile to channels.') }}</p>
          </div>
          <div class="tariff-profile-hero__actions">
            <router-link :to="{name: 'tariffProfile.edit', params: {id}}" class="btn btn-default">
              {{ $t('Edit') }}
            </router-link>
            <button class="btn btn-danger" type="button" @click="deleteConfirm = true">{{ $t('Delete') }}</button>
          </div>
        </div>

        <div class="row g-4 mt-1">
          <div class="col-lg-4">
            <div class="details-page-block summary-card h-100">
              <h3>{{ $t('Overview') }}</h3>
              <dl>
                <dd>{{ $t('ID') }}</dd>
                <dt>{{ profile.id }}</dt>
                <dd>{{ $t('Tariff periods') }}</dd>
                <dt>{{ profile.tariffPeriods.length }}</dt>
                <dd>{{ $t('Price periods') }}</dd>
                <dt>{{ countPricePeriods(profile) }}</dt>
                <dd>{{ $t('Tariffs') }}</dd>
                <dt>{{ profileTariffSummary(profile, tariffs) }}</dt>
              </dl>
            </div>
          </div>

          <div class="col-lg-8">
            <div class="details-page-block periods-card">
              <div class="periods-card__header">
                <h3>{{ $t('Tariff periods') }}</h3>
                <span class="periods-card__count">{{ profile.tariffPeriods.length }}</span>
              </div>

              <div v-for="(tariffPeriod, tariffPeriodIndex) in profile.tariffPeriods" :key="tariffPeriod.id || tariffPeriodIndex" class="period-card">
                <div class="period-card__header">
                  <div>
                    <strong>{{ $t('Tariff period') }} {{ tariffPeriodIndex + 1 }}</strong>
                    <div class="text-muted small">{{ tariffPeriodSummary(tariffPeriod, tariffs) }}</div>
                  </div>
                  <div class="text-muted small">{{ tariffZoneSummary(selectedTariff(tariffs, tariffPeriod.tariffId)) }}</div>
                </div>

                <div v-for="(pricePeriod, pricePeriodIndex) in tariffPeriod.pricePeriods" :key="pricePeriod.id || pricePeriodIndex" class="price-period-card">
                  <div class="price-period-card__header">
                    <div>
                      <strong>{{ pricePeriod.name || `${$t('Price period')} ${pricePeriodIndex + 1}` }}</strong>
                      <div class="text-muted small">{{ formatRange(pricePeriod.validFrom, pricePeriod.validTo) }}</div>
                    </div>
                    <div class="text-muted small">{{ pricePeriod.billingPeriodLength }} {{ pricePeriod.billingPeriodUnit }} · {{ pricePeriod.currency }}</div>
                  </div>

                  <div class="components-table-wrap">
                    <table class="table components-table">
                      <thead>
                        <tr>
                          <th>{{ $t('Component') }}</th>
                          <th>{{ $t('Zone') }}</th>
                          <th>{{ $t('Amount') }}</th>
                          <th>{{ $t('Unit') }}</th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-for="(itemEntry, itemIndex) in pricePeriod.items" :key="itemEntry.id || itemIndex">
                          <td>{{ itemEntry.componentCode }}</td>
                          <td>{{ itemEntry.zoneCode || $t('No zone') }}</td>
                          <td>{{ itemEntry.amount }}</td>
                          <td>{{ itemEntry.unit }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <modal-confirm
        v-if="deleteConfirm"
        class="modal-warning"
        :header="$t('Are you sure you want to delete this tariff profile?')"
        :loading="loading"
        @confirm="deleteProfile()"
        @cancel="deleteConfirm = false"
      >
      </modal-confirm>
    </loading-cover>
  </page-container>
</template>

<script setup>
  import {onMounted, ref, watch} from 'vue';
  import PageContainer from '@/common/pages/page-container.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import {energyTariffsApi} from '@/api/energy-tariffs-api';
  import {successNotification} from '@/common/notifier';
  import TariffProfileForm from './tariff-profile-form.vue';
  import {
    countPricePeriods,
    formatRange,
    normalizeProfile,
    profileTariffSummary,
    selectedTariff,
    tariffPeriodSummary,
    tariffZoneSummary,
  } from './tariff-profile-utils';

  const props = defineProps({id: String, item: Object});
  const emit = defineEmits(['add', 'delete']);

  const loading = ref(false);
  const error = ref(false);
  const deleteConfirm = ref(false);
  const profile = ref();
  const tariffs = ref([]);

  onMounted(fetchAll);
  watch(() => props.id, fetchAll);

  async function fetchAll() {
    if (props.id === 'new') {
      return;
    }

    loading.value = true;
    error.value = false;
    try {
      const [loadedProfile, loadedTariffs] = await Promise.all([energyTariffsApi.getTariffProfile(props.id), energyTariffsApi.getTariffs()]);
      profile.value = normalizeProfile(loadedProfile);
      tariffs.value = loadedTariffs;
    } catch (response) {
      error.value = response.status;
    } finally {
      loading.value = false;
    }
  }

  async function deleteProfile() {
    loading.value = true;
    await energyTariffsApi.deleteTariffProfile(props.id);
    successNotification('Tariff profile deleted.');
    emit('delete');
  }
</script>

<style scoped>
  .tariff-profile-hero {
    margin-top: 24px;
    border-radius: 24px;
    background: linear-gradient(135deg, #153f2f, #1f7a4f);
    color: #fff;
    display: flex;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
  }

  .tariff-profile-hero h1 {
    margin: 0 0 10px;
  }

  .tariff-profile-hero p {
    margin: 0;
    color: rgba(255, 255, 255, 0.86);
  }

  .tariff-profile-hero__eyebrow {
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 12px;
    opacity: 0.75;
    margin-bottom: 8px;
  }

  .tariff-profile-hero__actions,
  .periods-card__header,
  .period-card__header,
  .price-period-card__header {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
  }

  .summary-card,
  .periods-card {
    border-radius: 20px;
  }

  .periods-card__count {
    min-width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 999px;
    background: rgba(31, 122, 79, 0.12);
    color: #1f7a4f;
    font-weight: 700;
  }

  .period-card,
  .price-period-card {
    border: 1px solid rgba(0, 0, 0, 0.08);
    border-radius: 18px;
    padding: 18px;
    background: #fcfdfd;
    margin-top: 16px;
  }

  .price-period-card {
    background: #fff;
  }

  .components-table-wrap {
    overflow-x: auto;
    margin-top: 12px;
  }

  .components-table {
    margin-bottom: 0;
  }

  @media (max-width: 991px) {
    .tariff-profile-hero {
      border-radius: 20px;
    }

    .tariff-profile-hero__actions {
      width: 100%;
    }
  }
</style>
