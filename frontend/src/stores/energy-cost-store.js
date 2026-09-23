import {defineStore} from 'pinia';
import {computed, ref} from 'vue';
import {energyCostApi} from '@/api/energy-cost-api';

export const useEnergyCostStore = defineStore('energyCost', () => {
  const presets = ref([]);
  const presetsReady = ref(false);
  const presetDetailsById = ref({});
  const plans = ref([]);
  const plansReady = ref(false);
  const assignmentsByChannelId = ref({});
  const assignmentReadyByChannelId = ref({});

  const plansById = computed(() => Object.fromEntries(plans.value.map((plan) => [plan.id, plan])));

  const fetchPresets = (force = false) => {
    if (fetchPresets.promise && !force) return fetchPresets.promise;
    if (presetsReady.value && !force) return Promise.resolve(presets.value);
    fetchPresets.promise = energyCostApi
      .getPresets()
      .then((response) => {
        presets.value = response;
        presetsReady.value = true;
        return response;
      })
      .finally(() => (fetchPresets.promise = undefined));
    return fetchPresets.promise;
  };

  const fetchPreset = (id) => {
    if (presetDetailsById.value[id]) return Promise.resolve(presetDetailsById.value[id]);
    if (!fetchPreset.promises) fetchPreset.promises = {};
    if (!fetchPreset.promises[id]) {
      fetchPreset.promises[id] = energyCostApi
        .getPreset(id)
        .then((preset) => {
          presetDetailsById.value = {...presetDetailsById.value, [id]: preset};
          return preset;
        })
        .finally(() => delete fetchPreset.promises[id]);
    }
    return fetchPreset.promises[id];
  };

  const fetchPlans = (force = false) => {
    if (fetchPlans.promise && !force) return fetchPlans.promise;
    if (plansReady.value && !force) return Promise.resolve(plans.value);
    fetchPlans.promise = energyCostApi
      .getPlans()
      .then((response) => {
        plans.value = response;
        plansReady.value = true;
        return response;
      })
      .finally(() => (fetchPlans.promise = undefined));
    return fetchPlans.promise;
  };

  const fetchAssignment = (channelId, force = false) => {
    if (assignmentReadyByChannelId.value[channelId] && !force) return Promise.resolve(assignmentsByChannelId.value[channelId]);
    if (!fetchAssignment.promises) fetchAssignment.promises = {};
    if (!fetchAssignment.promises[channelId]) {
      fetchAssignment.promises[channelId] = energyCostApi
        .getAssignment(channelId)
        .then((assignment) => {
          assignmentsByChannelId.value = {...assignmentsByChannelId.value, [channelId]: assignment};
          assignmentReadyByChannelId.value = {...assignmentReadyByChannelId.value, [channelId]: true};
          return assignment;
        })
        .finally(() => delete fetchAssignment.promises[channelId]);
    }
    return fetchAssignment.promises[channelId];
  };

  const createPlan = async (data) => {
    const plan = await energyCostApi.createPlan(data);
    plans.value = [...plans.value, plan];
    plansReady.value = true;
    return plan;
  };

  const updatePlan = async (id, data) => {
    const plan = await energyCostApi.updatePlan(id, data);
    plans.value = plans.value.map((item) => (item.id === plan.id ? plan : item));
    return plan;
  };

  const deletePlan = async (id) => {
    await energyCostApi.deletePlan(id);
    plans.value = plans.value.filter((plan) => plan.id !== Number(id));
    assignmentsByChannelId.value = Object.fromEntries(
      Object.entries(assignmentsByChannelId.value).map(([channelId, assignment]) => [channelId, assignment?.planId === Number(id) ? null : assignment])
    );
  };

  const assignPlan = async (channelId, planId) => {
    const assignment = await energyCostApi.assignPlan(channelId, planId);
    assignmentsByChannelId.value = {...assignmentsByChannelId.value, [channelId]: assignment};
    assignmentReadyByChannelId.value = {...assignmentReadyByChannelId.value, [channelId]: true};
    return assignment;
  };

  const unassignPlan = async (channelId) => {
    await energyCostApi.unassignPlan(channelId);
    assignmentsByChannelId.value = {...assignmentsByChannelId.value, [channelId]: null};
    assignmentReadyByChannelId.value = {...assignmentReadyByChannelId.value, [channelId]: true};
  };

  const $reset = () => {
    presets.value = [];
    presetsReady.value = false;
    presetDetailsById.value = {};
    plans.value = [];
    plansReady.value = false;
    assignmentsByChannelId.value = {};
    assignmentReadyByChannelId.value = {};
    fetchPresets.promise = undefined;
    fetchPlans.promise = undefined;
    fetchPreset.promises = {};
    fetchAssignment.promises = {};
  };

  return {
    presets,
    presetsReady,
    presetDetailsById,
    plans,
    plansById,
    plansReady,
    assignmentsByChannelId,
    assignmentReadyByChannelId,
    fetchPresets,
    fetchPreset,
    fetchPlans,
    fetchAssignment,
    createPlan,
    updatePlan,
    deletePlan,
    assignPlan,
    unassignPlan,
    $reset,
  };
});
