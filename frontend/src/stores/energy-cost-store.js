import {defineStore} from 'pinia';
import {computed, ref} from 'vue';
import {energyCostApi} from '@/api/energy-cost-api';
import EventBus from '@/common/event-bus';

function notifyCostSettingsChanged(channelIds) {
  if (channelIds.length) EventBus.$emit('channel-cost-settings-changed', channelIds);
}

export const useEnergyCostStore = defineStore('energyCost', () => {
  const presets = ref([]);
  const presetsReady = ref(false);
  const presetDetailsById = ref({});
  const tariffs = ref([]);
  const tariffsReady = ref(false);
  const tariffDetailsById = ref({});
  const plans = ref([]);
  const plansReady = ref(false);
  const assignmentsByChannelId = ref({});
  const assignmentReadyByChannelId = ref({});
  let presetRequestVersion = 0;

  const plansById = computed(() => Object.fromEntries(plans.value.map((plan) => [plan.id, plan])));

  const fetchPresets = (force = false) => {
    if (fetchPresets.promise && !force) return fetchPresets.promise;
    if (presetsReady.value && !force) return Promise.resolve(presets.value);
    const requestVersion = ++presetRequestVersion;
    fetchPresets.promise = energyCostApi
      .getPresets()
      .then((response) => {
        if (requestVersion !== presetRequestVersion) return presets.value;
        const changedIds = new Set(response.filter((preset) => presetDetailsById.value[preset.id]?.revision !== preset.revision).map((preset) => preset.id));
        if (changedIds.size) {
          presetDetailsById.value = Object.fromEntries(Object.entries(presetDetailsById.value).filter(([id]) => !changedIds.has(id)));
        }
        presets.value = response;
        presetsReady.value = true;
        return response;
      })
      .finally(() => (fetchPresets.promise = undefined));
    return fetchPresets.promise;
  };

  const fetchPreset = (id, force = false) => {
    if (presetDetailsById.value[id] && !force) return Promise.resolve(presetDetailsById.value[id]);
    if (!fetchPreset.promises) fetchPreset.promises = {};
    if (!fetchPreset.promises[id]) {
      fetchPreset.promises[id] = energyCostApi
        .getPreset(id)
        .then(async (preset) => {
          const listedRevision = presets.value.find((candidate) => candidate.id === id)?.revision;
          // A request started before a catalog refresh must not put an old document back into the store.
          if (listedRevision && preset.revision !== listedRevision) preset = await energyCostApi.getPreset(id);
          presetDetailsById.value = {...presetDetailsById.value, [id]: preset};
          return preset;
        })
        .finally(() => delete fetchPreset.promises[id]);
    }
    return fetchPreset.promises[id];
  };

  const fetchTariffs = (force = false) => {
    if (fetchTariffs.promise && !force) return fetchTariffs.promise;
    if (tariffsReady.value && !force) return Promise.resolve(tariffs.value);
    fetchTariffs.promise = energyCostApi
      .getPlanStarters()
      .then((response) => {
        tariffs.value = response;
        tariffsReady.value = true;
        return response;
      })
      .finally(() => (fetchTariffs.promise = undefined));
    return fetchTariffs.promise;
  };

  const fetchTariff = (id) => {
    if (tariffDetailsById.value[id]) return Promise.resolve(tariffDetailsById.value[id]);
    if (!fetchTariff.promises) fetchTariff.promises = {};
    if (!fetchTariff.promises[id]) {
      fetchTariff.promises[id] = energyCostApi
        .getPlanStarter(id)
        .then((tariff) => {
          tariffDetailsById.value = {...tariffDetailsById.value, [id]: tariff};
          return tariff;
        })
        .finally(() => delete fetchTariff.promises[id]);
    }
    return fetchTariff.promises[id];
  };

  // Presentation is optional: unavailable descriptive metadata must not block assigned-plan calculations.
  const fetchPresentationCatalogs = async () => {
    const [catalog] = await Promise.allSettled([fetchTariffs(), fetchPresets()]);
    if (catalog.status === 'fulfilled') await Promise.allSettled(catalog.value.map((starter) => fetchTariff(starter.id)));
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
    const channelIds = Object.values(assignmentsByChannelId.value)
      .filter((assignment) => assignment?.planId === Number(id))
      .map((assignment) => assignment.channelId);
    plans.value = plans.value.filter((plan) => plan.id !== Number(id));
    assignmentsByChannelId.value = Object.fromEntries(
      Object.entries(assignmentsByChannelId.value).map(([channelId, assignment]) => [channelId, assignment?.planId === Number(id) ? null : assignment])
    );
    notifyCostSettingsChanged(channelIds);
  };

  const assignPlan = async (channelId, planId) => {
    const assignment = await energyCostApi.assignPlan(channelId, planId);
    assignmentsByChannelId.value = {...assignmentsByChannelId.value, [channelId]: assignment};
    assignmentReadyByChannelId.value = {...assignmentReadyByChannelId.value, [channelId]: true};
    notifyCostSettingsChanged([channelId]);
    return assignment;
  };

  const unassignPlan = async (channelId) => {
    await energyCostApi.unassignPlan(channelId);
    assignmentsByChannelId.value = {...assignmentsByChannelId.value, [channelId]: null};
    assignmentReadyByChannelId.value = {...assignmentReadyByChannelId.value, [channelId]: true};
    notifyCostSettingsChanged([channelId]);
  };

  const assignStarter = async (channelId, starterId, configuration) => {
    const {plan, assignment} = await energyCostApi.assignStarter(channelId, starterId, configuration);
    plans.value = plans.value.some((item) => item.id === plan.id) ? plans.value.map((item) => (item.id === plan.id ? plan : item)) : [...plans.value, plan];
    assignmentsByChannelId.value = {...assignmentsByChannelId.value, [channelId]: assignment};
    assignmentReadyByChannelId.value = {...assignmentReadyByChannelId.value, [channelId]: true};
    notifyCostSettingsChanged([channelId]);
    return plan;
  };

  const saveAssignedConfiguration = async (channelId, configuration, scope, expectedPlanId, expectedChannelIds) => {
    const {
      plan,
      assignment,
      affectedAssignments = [assignment],
    } = await energyCostApi.saveAssignedConfiguration(channelId, configuration, scope, expectedPlanId, expectedChannelIds);
    plans.value = plans.value.some((item) => item.id === plan.id) ? plans.value.map((item) => (item.id === plan.id ? plan : item)) : [...plans.value, plan];
    assignmentsByChannelId.value = {...assignmentsByChannelId.value, ...Object.fromEntries(affectedAssignments.map((item) => [item.channelId, item]))};
    assignmentReadyByChannelId.value = {...assignmentReadyByChannelId.value, ...Object.fromEntries(affectedAssignments.map((item) => [item.channelId, true]))};
    notifyCostSettingsChanged(affectedAssignments.map((item) => item.channelId));
    return {plan, assignment};
  };

  const $reset = () => {
    presets.value = [];
    presetsReady.value = false;
    presetDetailsById.value = {};
    tariffs.value = [];
    tariffsReady.value = false;
    tariffDetailsById.value = {};
    plans.value = [];
    plansReady.value = false;
    assignmentsByChannelId.value = {};
    assignmentReadyByChannelId.value = {};
    fetchPresets.promise = undefined;
    presetRequestVersion = 0;
    fetchPlans.promise = undefined;
    fetchPreset.promises = {};
    fetchTariffs.promise = undefined;
    fetchTariff.promises = {};
    fetchAssignment.promises = {};
  };

  return {
    presets,
    presetsReady,
    presetDetailsById,
    tariffs,
    tariffsReady,
    tariffDetailsById,
    plans,
    plansById,
    plansReady,
    assignmentsByChannelId,
    assignmentReadyByChannelId,
    fetchPresets,
    fetchPreset,
    fetchTariffs,
    fetchTariff,
    fetchPresentationCatalogs,
    fetchPlans,
    fetchAssignment,
    createPlan,
    updatePlan,
    deletePlan,
    assignPlan,
    unassignPlan,
    assignStarter,
    saveAssignedConfiguration,
    $reset,
  };
});
