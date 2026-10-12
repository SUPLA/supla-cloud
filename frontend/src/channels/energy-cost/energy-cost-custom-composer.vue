<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import {computed, ref} from 'vue';
  import {useI18n} from 'vue-i18n';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';
  import {composerOptions, composeRole, compatibleComponents} from './energy-cost-composer';
  import {tariffCatalogPresentation} from './energy-cost-plan-presentation';

  const props = defineProps({period: Object, configuration: Object});
  const emit = defineEmits(['update:period']);
  const store = useEnergyCostStore();
  const {t} = useI18n();
  const error = ref('');
  const busy = ref(false);
  const roles = ['supply', 'distribution'];
  const options = computed(() => Object.fromEntries(roles.map((role) => [role, composerOptions(role, store.presets, Object.values(store.tariffDetailsById))])));
  const selected = (role) =>
    options.value[role].find((option) =>
      option.components.every((component) =>
        props.period.components.some((existing) => existing.presetId === component.presetId && existing.componentId === component.componentId)
      )
    )?.preset.id || '';

  async function choose(role, id) {
    const option = options.value[role].find((candidate) => candidate.preset.id === id);
    if (!option) return;
    busy.value = true;
    error.value = '';
    try {
      await Promise.all(option.components.map((component) => store.fetchPreset(component.presetId)));
      const period = composeRole(props.period, role, option.components);
      if (!compatibleComponents(period.components, props.configuration, store.presetDetailsById)) throw new Error('Choose a compatible tariff.'); // i18n
      emit('update:period', period);
    } catch (reason) {
      error.value = reason.body?.message || reason.message;
    } finally {
      busy.value = false;
    }
  }
</script>
<template>
  <div>
    <div v-for="role in roles" :key="role" class="form-group">
      <label>{{ role === 'supply' ? $t('Electricity supply') : $t('Distribution') }}</label>
      <select class="form-control" :value="selected(role)" :disabled="busy" @change="choose(role, $event.target.value)">
        <option value="">{{ $t('Choose tariff') }}</option>
        <option v-for="option in options[role]" :key="option.preset.id" :value="option.preset.id">
          {{ tariffCatalogPresentation(option.preset, t)[role === 'supply' ? 'supplier' : 'distribution'] }}
        </option>
      </select>
    </div>
    <div v-if="error" class="text-danger">{{ $t(error) }}</div>
  </div>
</template>
