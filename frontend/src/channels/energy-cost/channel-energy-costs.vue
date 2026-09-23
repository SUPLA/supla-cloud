<script setup>
  import {computed, nextTick, ref, watch} from 'vue';
  import {storeToRefs} from 'pinia';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import ChannelFunction from '@/common/enums/channel-function';
  import EnergyCostPlanDialog from './energy-cost-plan-dialog.vue';
  import EnergyCostPlanToolbar from './energy-cost-plan-toolbar.vue';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';

  const props = defineProps({channel: {type: Object, required: true}});
  const store = useEnergyCostStore();
  const {plans, plansReady, assignmentsByChannelId, assignmentReadyByChannelId} = storeToRefs(store);
  const selectedPlanId = ref();
  const dialogPlan = ref(null);
  const dialogMounted = ref(false);
  const dialogOpened = ref(false);
  const loading = ref(false);
  const error = ref(null);

  const supported = computed(() => props.channel.functionId === ChannelFunction.ELECTRICITYMETER);
  const assignment = computed(() => assignmentsByChannelId.value[props.channel.id]);
  const loaded = computed(() => plansReady.value && assignmentReadyByChannelId.value[props.channel.id]);
  const pageLoading = computed(() => !loaded.value && !error.value);
  const selectedPlan = computed(() => plans.value.find((plan) => plan.id === selectedPlanId.value));

  async function load() {
    if (!supported.value) return;
    error.value = null;
    try {
      await Promise.all([store.fetchPlans(), store.fetchAssignment(props.channel.id)]);
      selectedPlanId.value = assignment.value?.planId;
    } catch (requestError) {
      error.value = requestError.body?.message || 'Could not load cost plans.'; // i18n
    }
  }

  async function assign() {
    const previousPlanId = assignment.value?.planId;
    if (!selectedPlanId.value) return;
    loading.value = true;
    error.value = null;
    try {
      await store.assignPlan(props.channel.id, selectedPlanId.value);
    } catch (requestError) {
      selectedPlanId.value = previousPlanId;
      error.value = requestError.body?.message || 'Could not assign the cost plan.'; // i18n
    } finally {
      loading.value = false;
    }
  }

  async function switchPlan(planId) {
    const previousPlanId = assignment.value?.planId;
    selectedPlanId.value = planId;
    if (!planId && assignment.value) {
      await unassign();
      return;
    }
    if (planId && planId !== previousPlanId) await assign();
  }

  async function unassign() {
    loading.value = true;
    error.value = null;
    try {
      await store.unassignPlan(props.channel.id);
      selectedPlanId.value = undefined;
    } catch (requestError) {
      error.value = requestError.body?.message || 'Could not unassign the cost plan.'; // i18n
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
    selectedPlanId.value = plan.id;
  }

  function deleted(planId) {
    if (!assignment.value || Number(selectedPlanId.value) === Number(planId)) selectedPlanId.value = undefined;
  }

  watch(() => props.channel.id, load, {immediate: true});
</script>

<template>
  <div class="container channel-energy-costs">
    <loading-cover :loading="pageLoading">
      <div v-if="supported">
        <div v-if="error" class="alert alert-danger">{{ $t(error) }}</div>
        <div v-if="!plans.length" class="well text-center">
          <p>{{ $t('No cost plan is assigned to this electricity meter.') }}</p>
          <button type="button" class="btn btn-green" @click="createPlan">{{ $t('Create cost plan') }}</button>
        </div>
        <energy-cost-plan-toolbar
          v-else
          :plans="plans"
          :selected-plan-id="selectedPlanId"
          :loading="loading"
          @update:selected-plan-id="switchPlan"
          @create="createPlan"
          @edit="editPlan"
        />
      </div>
    </loading-cover>
    <energy-cost-plan-dialog
      v-if="dialogMounted"
      :model-value="dialogOpened"
      :plan="dialogPlan"
      :channel-id="channel.id"
      @update:model-value="setDialogOpened"
      @saved="saved"
      @deleted="deleted"
    />
  </div>
</template>
