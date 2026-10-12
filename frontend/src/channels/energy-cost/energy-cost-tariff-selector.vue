<script>
  export default {compatConfig: {MODE: 3}};
</script>

<script setup>
  import {computed, ref, watch} from 'vue';
  import EnergyCostTariffChoice from './energy-cost-tariff-choice.vue';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';
  import {configurationFromTariff} from './energy-cost-plan-utils';
  import {validateCostPlanDraft} from './energy-cost-plan-draft';
  import DialogWindow from '@/common/gui/dialog/DialogWindow.vue';
  import DialogContent from '@/common/gui/dialog/DialogContent.vue';

  const props = defineProps({modelValue: Boolean, tariffs: {type: Array, required: true}, loading: Boolean, configuration: Object});
  const emit = defineEmits(['update:modelValue', 'select', 'compose', 'unassign']);
  const store = useEnergyCostStore();
  const opened = computed({get: () => props.modelValue, set: (value) => emit('update:modelValue', value)});
  const selectedId = ref('');
  const mode = ref('tariff');
  const draft = ref(configurationFromTariff({components: []}));
  const composerLoading = ref(false);
  const catalogsReady = ref(false);
  const error = ref('');
  let requestVersion = 0;
  let activeDialog;
  watch(
    opened,
    (value) => {
      requestVersion++;
      activeDialog = null;
      composerLoading.value = false;
      if (value) {
        selectedId.value = '';
        mode.value = 'tariff';
        error.value = '';
        catalogsReady.value = false;
        draft.value = configurationFromTariff({components: []});
        if (props.configuration) {
          const {currency, timezone, taxContext} = props.configuration;
          draft.value = {...draft.value, currency, timezone, taxContext};
        }
      }
    },
    {immediate: true}
  );
  watch(mode, async (value) => {
    error.value = '';
    composerLoading.value = false;
    if (value !== 'custom' || catalogsReady.value) return;
    const version = requestVersion;
    try {
      await Promise.all([store.fetchPresets(), store.fetchTariffs()]);
      await Promise.all(store.tariffs.map((tariff) => store.fetchTariff(tariff.id)));
      if (version === requestVersion) catalogsReady.value = true;
    } catch (reason) {
      if (version === requestVersion) error.value = reason.body?.message || 'Could not load tariff presets.'; // i18n
    }
  });
  watch(
    () => props.loading,
    (loading) => activeDialog?.setLoading(loading)
  );
  function confirm(dialog) {
    if (mode.value === 'custom') {
      if (props.loading || composerLoading.value || !catalogsReady.value) return dialog.setLoading(props.loading);
      if (validateCostPlanDraft(draft.value, store.presetDetailsById).invalid) {
        error.value = 'Choose a compatible tariff.'; // i18n
        return dialog.setLoading(false);
      }
      activeDialog = dialog;
      emit('compose', draft.value.periods[0].components);
      return;
    }
    if (!selectedId.value || props.loading) return dialog.setLoading(props.loading);
    activeDialog = dialog;
    if (selectedId.value === '__no_tariff__') emit('unassign');
    else emit('select', selectedId.value);
  }
</script>

<template>
  <dialog-window v-model="opened" cancellable :close-on-backdrop="false" @confirm="confirm">
    <dialog-content>
      <template #header>
        <h4>{{ $t('Choose tariff') }}</h4>
      </template>
      <p v-if="mode === 'tariff' && selectedId === '__no_tariff__'">
        {{ $t('The tariff assignment will be removed from this meter. Other meters keep their tariff plan.') }}
      </p>
      <energy-cost-tariff-choice
        v-model:mode="mode"
        v-model:selected-id="selectedId"
        :tariffs="tariffs"
        :period="draft.periods[0]"
        :configuration="draft"
        :disabled="loading"
        :composer-ready="catalogsReady"
        :composer-error="error"
        show-more-settings-info
        allow-no-tariff
        id-prefix="energy-cost-tariff-selection"
        @update:period="
          draft = {...draft, periods: [$event]};
          error = '';
        "
        @update:busy="composerLoading = $event"
      />
    </dialog-content>
  </dialog-window>
</template>
