<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import {computed, ref, watch} from 'vue';
  import DialogWindow from '@/common/gui/dialog/DialogWindow.vue';
  import DialogContent from '@/common/gui/dialog/DialogContent.vue';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import EnergyCostHistoryEditor from './energy-cost-history-editor.vue';
  import EnergyCostSharedSave from './energy-cost-shared-save.vue';
  import EnergyCostBillingHistory from './energy-cost-billing-history.vue';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';
  import {energyCostApi} from '@/api/energy-cost-api';
  import {copyDraft, timelineError} from './energy-cost-history';
  import {validateCostPlanDraft, costPlanDraftPayload} from './energy-cost-plan-draft';
  import {configurationFromTariff} from './energy-cost-plan-utils';

  const props = defineProps({modelValue: Boolean, plan: Object, channelId: Number});
  const emit = defineEmits(['update:modelValue', 'saved', 'deleted']);
  const opened = computed({get: () => props.modelValue, set: (value) => emit('update:modelValue', value)});
  const store = useEnergyCostStore();
  const configuration = ref(configurationFromTariff({components: []}));
  const errors = ref({periods: [], billingCycles: []});
  const serverError = ref('');
  const saving = ref(false);
  const ready = ref(false);
  const activeSettings = ref('');
  const meters = ref(null);
  const scope = ref('thisMeter');
  const scopeVisible = ref(false);

  watch(
    opened,
    async (value) => {
      if (!value) return;
      configuration.value = copyDraft(props.plan?.configuration || configurationFromTariff({components: []}));
      errors.value = {periods: [], billingCycles: []};
      serverError.value = '';
      ready.value = false;
      activeSettings.value = '';
      scopeVisible.value = false;
      meters.value = null;
      scope.value = 'thisMeter';
      try {
        await Promise.all([store.fetchPresets(), store.fetchTariffs()]);
        await Promise.all(store.tariffs.map((starter) => store.fetchTariff(starter.id)));
        await Promise.all(
          configuration.value.periods.flatMap((period) =>
            period.components.filter((component) => component.presetId).map((component) => store.fetchPreset(component.presetId))
          )
        );
        ready.value = true;
      } catch (error) {
        serverError.value = error.body?.message || 'Could not load tariff presets.'; // i18n
      }
    },
    {immediate: true}
  );

  function updatePeriods(periods) {
    configuration.value = {...configuration.value, periods};
    // Any edits after confirmation require a new review of the affected meters.
    scopeVisible.value = false;
  }

  function toggleSettings(settings) {
    activeSettings.value = activeSettings.value === settings ? '' : settings;
  }

  async function save(dialog) {
    if (saving.value || !ready.value) return dialog?.setLoading?.(false);
    errors.value = validateCostPlanDraft(configuration.value, store.presetDetailsById);
    const invalid = timelineError(configuration.value.periods);
    if (invalid || errors.value.invalid) {
      serverError.value = invalid;
      if (errors.value.billingCycles.some((error) => Object.keys(error).length) || errors.value.periods.some((error) => error.billingCycles))
        activeSettings.value = 'billing';
      return dialog?.setLoading?.(false);
    }
    saving.value = true;
    serverError.value = '';
    try {
      const document = costPlanDraftPayload(configuration.value, store.presetDetailsById);
      if (props.plan && JSON.stringify(document) === JSON.stringify(costPlanDraftPayload(props.plan.configuration, store.presetDetailsById))) {
        opened.value = false;
        return;
      }
      if (props.plan && props.channelId) {
        const currentMeters = await energyCostApi.getPlanChannels(props.plan.id);
        const sameMeters =
          meters.value && JSON.stringify(meters.value.map((meter) => meter.id).sort()) === JSON.stringify(currentMeters.map((meter) => meter.id).sort());
        if (currentMeters.length > 1 && (!scopeVisible.value || !sameMeters)) {
          meters.value = currentMeters;
          scope.value = 'thisMeter';
          scopeVisible.value = true;
          return dialog?.setLoading?.(false);
        }
        meters.value = currentMeters;
      }
      const plan =
        props.plan && props.channelId
          ? (
              await store.saveAssignedConfiguration(
                props.channelId,
                document,
                scopeVisible.value ? scope.value : 'thisMeter',
                props.plan.id,
                meters.value?.map((meter) => meter.id)
              )
            ).plan
          : props.plan
            ? await store.updatePlan(props.plan.id, {name: props.plan.name, configuration: document})
            : await store.createPlan({name: 'Custom tariff', configuration: document});
      emit('saved', plan);
      opened.value = false;
    } catch (error) {
      scopeVisible.value = false;
      serverError.value = error.body?.message || 'Could not save the tariff plan.'; // i18n
      dialog?.setLoading?.(false);
    } finally {
      saving.value = false;
      dialog?.setLoading?.(false);
    }
  }
</script>
<template>
  <dialog-window v-model="opened" cancellable @confirm="save">
    <dialog-content container-class="dialog-800">
      <template #header
        ><h4>{{ $t('Tariff history / settings') }}</h4></template
      >
      <template #default>
        <energy-cost-shared-save v-if="scopeVisible" v-model="scope" :meters="meters" />
        <template v-else-if="ready">
          <energy-cost-history-editor
            :configuration="configuration"
            :errors="errors.periods"
            :adding-transition="activeSettings === 'transition'"
            @update:periods="updatePeriods"
            @update:adding-transition="activeSettings = $event ? 'transition' : ''"
          >
            <template #actions>
              <div class="btn-group settings-actions">
                <button
                  type="button"
                  class="btn btn-default"
                  :class="{active: activeSettings === 'transition'}"
                  :aria-expanded="activeSettings === 'transition'"
                  @click="toggleSettings('transition')"
                >
                  {{ $t('Add tariff change') }}
                </button>
                <button
                  type="button"
                  class="btn btn-default"
                  :class="{active: activeSettings === 'billing'}"
                  :aria-expanded="activeSettings === 'billing'"
                  @click="toggleSettings('billing')"
                >
                  {{ $t('Advanced billing settings') }}
                </button>
              </div>
            </template>
          </energy-cost-history-editor>
          <transition-expand>
            <energy-cost-billing-history
              v-if="activeSettings === 'billing'"
              :configuration="configuration"
              :errors="errors.billingCycles"
              @update:cycles="configuration = {...configuration, billingCycles: $event}"
            />
          </transition-expand>
        </template>
        <div v-if="serverError" class="text-danger">{{ $t(serverError) }}</div>
      </template>
    </dialog-content>
  </dialog-window>
</template>
<style scoped>
  .settings-actions {
    margin: 15px 0;
  }
</style>
