<script>
  export default {compatConfig: {MODE: 3}};
</script>

<script setup>
  import {computed, nextTick, ref, watch} from 'vue';
  import {storeToRefs} from 'pinia';
  import {useI18n} from 'vue-i18n';
  import {DateTime} from 'luxon';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import ChannelFunction from '@/common/enums/channel-function';
  import EnergyCostPlanDialog from './energy-cost-plan-dialog.vue';
  import EnergyCostDashboard from './energy-cost-dashboard.vue';
  import EnergyCostTariffSelector from './energy-cost-tariff-selector.vue';
  import {cloneTariffComponents, configurationFromTariff} from './energy-cost-plan-utils';
  import {copyDraft, replacePeriodComponents} from './energy-cost-history';
  import {presentCostPlan} from './energy-cost-plan-presentation';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';

  const props = defineProps({channel: {type: Object, required: true}});
  const store = useEnergyCostStore();
  const {t, locale} = useI18n();
  const {plans, plansReady, tariffs, presets, tariffDetailsById, assignmentsByChannelId, assignmentReadyByChannelId} = storeToRefs(store);
  const loading = ref(false);
  const error = ref(null);
  const selectorOpened = ref(false);
  const dialogPlan = ref(null);
  const dialogMounted = ref(false);
  const dialogOpened = ref(false);
  const tariffDetailsVisible = ref(false);

  const supported = computed(() => props.channel.functionId === ChannelFunction.ELECTRICITYMETER);
  const assignment = computed(() => assignmentsByChannelId.value[props.channel.id]);
  const assignedPlan = computed(() => plans.value.find((plan) => plan.id === assignment.value?.planId));
  const loaded = computed(() => plansReady.value && assignmentReadyByChannelId.value[props.channel.id]);
  const pageLoading = computed(() => !loaded.value && !error.value);
  const presentation = computed(() =>
    presentCostPlan(assignedPlan.value?.configuration, {
      starters: Object.values(tariffDetailsById.value),
      presets: presets.value,
      t,
      locale: locale.value,
    })
  );
  const currentPeriodIndex = computed(() => presentation.value.history.findIndex((period) => period.current));
  const currentPeriod = computed(() => assignedPlan.value?.configuration?.periods?.[currentPeriodIndex.value]);
  function formatDate(value, end = false) {
    if (!value) return '';
    const configuration = assignedPlan.value?.configuration;
    let date = DateTime.fromISO(value).setZone(configuration.timezone).setLocale(locale.value);
    if (end && date.equals(date.startOf('day'))) date = date.minus({days: 1});
    return date.toLocaleString({year: 'numeric', month: '2-digit', day: '2-digit'});
  }
  const assignedFromDate = computed(() => formatDate(currentPeriod.value?.validFrom));
  const assignedToDate = computed(() => formatDate(currentPeriod.value?.validTo, true));

  async function load() {
    tariffDetailsVisible.value = false;
    if (!supported.value) return;
    error.value = null;
    try {
      store.fetchPresentationCatalogs();
      await Promise.all([store.fetchPlans(), store.fetchAssignment(props.channel.id, true)]);
    } catch (requestError) {
      error.value = requestError.body?.message || 'Could not load tariff plans.'; // i18n
    }
  }

  async function applyStarter(starterId) {
    const channelId = props.channel.id;
    const plan = assignedPlan.value;
    const configuration = plan?.configuration?.periods?.length > 1 ? copyDraft(plan.configuration) : null;
    const periodIndex = currentPeriodIndex.value;
    loading.value = true;
    error.value = null;
    try {
      const starter = await store.fetchTariff(starterId);
      if (configuration) {
        if (periodIndex < 0) throw new Error('No tariff currently effective'); // i18n
        configuration.periods = replacePeriodComponents(configuration.periods, periodIndex, cloneTariffComponents(starter));
        await store.saveAssignedConfiguration(channelId, configuration, 'thisMeter', plan.id);
      } else {
        await store.assignStarter(channelId, starterId, configurationFromTariff(starter));
      }
      selectorOpened.value = false;
    } catch (requestError) {
      error.value = requestError.body?.message || 'Could not apply the tariff.'; // i18n
    } finally {
      loading.value = false;
    }
  }

  async function openAdvanced() {
    dialogPlan.value = assignedPlan.value || null;
    dialogMounted.value = true;
    await nextTick();
    dialogOpened.value = true;
  }

  function saved(plan) {
    dialogOpened.value = false;
    dialogMounted.value = false;
    if (!assignment.value) store.assignPlan(props.channel.id, plan.id);
  }

  async function removeAssignment() {
    loading.value = true;
    error.value = null;
    try {
      if (assignment.value) await store.unassignPlan(props.channel.id);
      selectorOpened.value = false;
    } catch (requestError) {
      error.value = requestError.body?.message || 'Could not remove the tariff assignment.'; // i18n
    } finally {
      loading.value = false;
    }
  }

  watch(() => props.channel.id, load, {immediate: true});
</script>

<template>
  <div class="container channel-energy-costs">
    <loading-cover :loading="pageLoading">
      <div v-if="supported">
        <div v-if="error" class="alert alert-danger">{{ $t(error) }}</div>
        <section v-if="!assignment" class="well text-center">
          <h3>{{ $t('Set up electricity costs') }}</h3>
          <p>{{ $t('Choose a tariff to calculate the cost of electricity used by this meter.') }}</p>
          <button class="btn btn-primary" :disabled="loading" @click="selectorOpened = true">{{ $t('Choose tariff') }}</button>
          <button class="btn btn-link" :disabled="loading" @click="openAdvanced">{{ $t('Create custom tariff') }}</button>
        </section>
        <template v-else-if="assignedPlan">
          <section class="well energy-cost-assigned-plan">
            <div class="pull-right">
              <button class="btn btn-default" :disabled="loading" @click="selectorOpened = true">{{ $t('Change tariff') }}</button>
              <button class="btn btn-link" :disabled="loading" @click="openAdvanced">{{ $t('Tariff history / settings') }}</button>
            </div>
            <strong>{{ $t('Tariff assigned to this meter') }}</strong>
            <div>{{ presentation.label }}</div>
            <div v-if="presentation.periodCount > 1 && assignedFromDate">{{ $t('From date') }}: {{ assignedFromDate }}</div>
            <div v-if="currentPeriodIndex < presentation.periodCount - 1 && assignedToDate">{{ $t('To date') }}: {{ assignedToDate }}</div>
            <div v-if="presentation.supplier">{{ $t('Electricity supply') }}: {{ presentation.supplier }}</div>
            <div v-if="presentation.custom && presentation.distribution">{{ $t('Distribution') }}: {{ presentation.distribution }}</div>
            <small v-if="presentation.customized">{{ $t('Customized prices') }}</small>
            <div v-if="presentation.details.length">
              <button
                type="button"
                class="btn btn-link btn-sm m-0 p-0"
                :aria-expanded="tariffDetailsVisible"
                :aria-controls="`energy-cost-tariff-details-${channel.id}`"
                @click="tariffDetailsVisible = !tariffDetailsVisible"
              >
                {{ $t('Tariff details') }}
                <i :class="tariffDetailsVisible ? 'pe-7s-angle-up' : 'pe-7s-angle-down'" aria-hidden="true"></i>
              </button>
              <transition-expand>
                <div v-if="tariffDetailsVisible" :id="`energy-cost-tariff-details-${channel.id}`">
                  <ul class="mt-2">
                    <li v-for="(detail, index) in presentation.details" :key="index">{{ detail.label }} — {{ detail.tariff }}</li>
                  </ul>
                </div>
              </transition-expand>
            </div>
            <div v-if="presentation.periodCount > 1">
              {{ $t('{count} tariff periods', {count: presentation.periodCount}) }} ·
              <button class="btn btn-link btn-xs" @click="openAdvanced">{{ $t('View history') }}</button>
            </div>
          </section>
          <energy-cost-dashboard :channel="channel" :plan="assignedPlan" :assigned-plan-id="assignment.planId" />
        </template>
      </div>
    </loading-cover>
    <energy-cost-tariff-selector v-model="selectorOpened" :tariffs="tariffs" :loading="loading" @select="applyStarter" @unassign="removeAssignment" />
    <energy-cost-plan-dialog v-if="dialogMounted" v-model="dialogOpened" :plan="dialogPlan" :channel-id="channel.id" @saved="saved" @deleted="load" />
  </div>
</template>
