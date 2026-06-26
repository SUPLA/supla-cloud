<template>
  <page-container :error="error">
    <tariff-profile-form v-if="id === 'new'" @add="$emit('add', $event)"></tariff-profile-form>

    <loading-cover v-else :loading="loading || !profile">
      <div v-if="profile" class="container tariff-profile-details-page">
        <BreadcrumbList :current="profile.name">
          <RouterLink :to="{name: 'tariffProfiles'}">{{ $t('Tariff profiles') }}</RouterLink>
        </BreadcrumbList>

        <PendingChangesPage :header="profile.name" deletable @delete="deleteProfile()">
          <template #buttons>
            <div class="btn-toolbar mr-2">
              <router-link :to="{name: 'tariffProfile.edit', params: {id}}" class="btn btn-default">
                {{ $t('Edit') }}
              </router-link>
            </div>
          </template>
          <template #deleteConfirm>
            {{ $t('Are you sure you want to delete this tariff profile?') }}
          </template>
        </PendingChangesPage>

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
                <strong>{{ `${$t('Price period')} ${pricePeriodIndex + 1}` }}</strong>
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
                    <td>{{ energyPriceComponentLabel(itemEntry.componentCode) }}</td>
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
    </loading-cover>
  </page-container>
</template>

<script setup>
  import {onMounted, ref, watch} from 'vue';
  import PageContainer from '@/common/pages/page-container.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';
  import {energyTariffsApi} from '@/api/energy-tariffs-api';
  import {successNotification} from '@/common/notifier';
  import TariffProfileForm from './tariff-profile-form.vue';
  import {energyPriceComponentLabel, formatRange, normalizeProfile, selectedTariff, tariffPeriodSummary, tariffZoneSummary} from './tariff-profile-utils';
  import PendingChangesPage from '@/common/pages/pending-changes-page.vue';

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
