<script setup>
  import {computed, nextTick, ref, watch} from 'vue';
  import {storeToRefs} from 'pinia';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import ChannelFunction from '@/common/enums/channel-function';
  import EnergyCostPlanDialog from './energy-cost-plan-dialog.vue';
  import EnergyCostPlanToolbar from './energy-cost-plan-toolbar.vue';
  import EnergyCostDashboard from './energy-cost-dashboard.vue';
  import {configurationFromTariff} from './energy-cost-plan-utils';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';

  const props = defineProps({channel: {type: Object, required: true}});
  const store = useEnergyCostStore();
  const {plans, plansReady, tariffs, tariffsReady, assignmentsByChannelId, assignmentReadyByChannelId} = storeToRefs(store);
  const selectedOption = ref();
  const starterScenario = ref(null);
  const dialogPlan = ref(null);
  const dialogMounted = ref(false);
  const dialogOpened = ref(false);
  const loading = ref(false);
  const error = ref(null);

  const supported = computed(() => props.channel.functionId === ChannelFunction.ELECTRICITYMETER);
  const assignment = computed(() => assignmentsByChannelId.value[props.channel.id]);
  const loaded = computed(() => plansReady.value && tariffsReady.value && assignmentReadyByChannelId.value[props.channel.id]);
  const pageLoading = computed(() => !loaded.value && !error.value);
  const selectedPlanId = computed(() => (selectedOption.value?.startsWith('plan:') ? Number(selectedOption.value.slice(5)) : undefined));
  const selectedStarterId = computed(() => (selectedOption.value?.startsWith('starter:') ? selectedOption.value.slice(8) : undefined));
  const selectedPlan = computed(() => plans.value.find((plan) => plan.id === selectedPlanId.value));
  const scenarioPlan = computed(() => selectedPlan.value || starterScenario.value);
  let selectionToken = 0;

  async function load() {
    if (!supported.value) return;
    selectedOption.value = undefined;
    starterScenario.value = null;
    error.value = null;
    try {
      await Promise.all([store.fetchPlans(), store.fetchTariffs(), store.fetchAssignment(props.channel.id)]);
      selectedOption.value = assignment.value ? `plan:${assignment.value.planId}` : undefined;
    } catch (requestError) {
      error.value = requestError.body?.message || 'Could not load tariff plans.'; // i18n
    }
  }

  async function assign() {
    if (!selectedOption.value || (selectedStarterId.value && !starterScenario.value)) return;
    loading.value = true;
    error.value = null;
    try {
      if (selectedStarterId.value) {
        const plan = await store.assignStarter(props.channel.id, selectedStarterId.value, starterScenario.value.configuration);
        selectedOption.value = `plan:${plan.id}`;
        starterScenario.value = null;
      } else if (selectedPlanId.value) {
        await store.assignPlan(props.channel.id, selectedPlanId.value);
      }
    } catch (requestError) {
      error.value = requestError.body?.message || 'Could not assign the tariff plan.'; // i18n
    } finally {
      loading.value = false;
    }
  }

  async function selectPlan(option) {
    const token = ++selectionToken;
    selectedOption.value = option;
    starterScenario.value = null;
    error.value = null;
    const starterId = selectedStarterId.value;
    if (!starterId) return;
    loading.value = true;
    try {
      const starter = await store.fetchTariff(starterId);
      if (token !== selectionToken) return;
      starterScenario.value = {
        id: `starter:${starter.id}`,
        name: starter.metadata?.label || starter.label || starter.id,
        configuration: configurationFromTariff(starter),
      };
    } catch (requestError) {
      if (token === selectionToken) error.value = requestError.body?.message || 'Could not load the tariff plan.'; // i18n
    } finally {
      if (token === selectionToken) loading.value = false;
    }
  }

  async function unassign() {
    loading.value = true;
    error.value = null;
    try {
      await store.unassignPlan(props.channel.id);
      selectedOption.value = undefined;
    } catch (requestError) {
      error.value = requestError.body?.message || 'Could not unassign the tariff plan.'; // i18n
    } finally {
      loading.value = false;
    }
  }

  async function openDialog(plan = null) {
    dialogPlan.value = plan;
    dialogMounted.value = true;
    await nextTick();
    dialogOpened.value = true;
  }

  function createPlan() {
    openDialog();
  }

  function editPlan() {
    openDialog(selectedPlan.value);
  }

  function setDialogOpened(opened) {
    dialogOpened.value = opened;
    if (!opened) dialogMounted.value = false;
  }

  function saved(plan) {
    selectedOption.value = `plan:${plan.id}`;
  }

  function deleted(planId) {
    if (!assignment.value || Number(selectedPlanId.value) === Number(planId)) selectedOption.value = undefined;
  }

  watch(() => props.channel.id, load, {immediate: true});
</script>

<template>
  <div class="container channel-energy-costs">
    <loading-cover :loading="pageLoading">
      <div v-if="supported">
        <div v-if="error" class="alert alert-danger">{{ $t(error) }}</div>
        <energy-cost-plan-toolbar
          :plans="plans"
          :tariffs="tariffs"
          :model-value="selectedOption"
          :assigned-plan-id="assignment?.planId"
          :loading="loading"
          :assign-disabled="Boolean(selectedStarterId && !starterScenario)"
          @update:model-value="selectPlan"
          @assign="assign"
          @unassign="unassign"
          @create="createPlan"
          @edit="editPlan"
        />
        <energy-cost-dashboard v-if="scenarioPlan" :channel="channel" :plan="scenarioPlan" :assigned-plan-id="assignment?.planId" />
      </div>
    </loading-cover>
    <energy-cost-plan-dialog
      v-if="dialogMounted"
      :model-value="dialogOpened"
      :plan="dialogPlan"
      @update:model-value="setDialogOpened"
      @saved="saved"
      @deleted="deleted"
    />
  </div>
</template>
