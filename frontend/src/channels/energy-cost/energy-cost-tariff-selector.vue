<script>
  export default {compatConfig: {MODE: 3}};
</script>

<script setup>
  import {computed, ref, watch} from 'vue';
  import EnergyCostTariffPicker from './energy-cost-tariff-picker.vue';
  import DialogWindow from '@/common/gui/dialog/DialogWindow.vue';
  import DialogContent from '@/common/gui/dialog/DialogContent.vue';

  const props = defineProps({modelValue: Boolean, tariffs: {type: Array, required: true}, loading: Boolean});
  const emit = defineEmits(['update:modelValue', 'select', 'unassign']);
  const opened = computed({get: () => props.modelValue, set: (value) => emit('update:modelValue', value)});
  const selectedId = ref('');
  let activeDialog;
  watch(opened, (value) => {
    if (value) selectedId.value = '';
  });
  watch(
    () => props.loading,
    (loading) => activeDialog?.setLoading(loading)
  );
  function confirm(dialog) {
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
      <p v-if="selectedId === '__no_tariff__'">{{ $t('The tariff assignment will be removed from this meter. Other meters keep their tariff plan.') }}</p>
      <p v-else>{{ $t('Choose a ready-made tariff. It will be applied to the full available measurement history.') }}</p>
      <fieldset :disabled="loading">
        <energy-cost-tariff-picker v-model="selectedId" :tariffs="tariffs" id-prefix="energy-cost-tariff-selection" allow-no-tariff />
      </fieldset>
    </dialog-content>
  </dialog-window>
</template>
