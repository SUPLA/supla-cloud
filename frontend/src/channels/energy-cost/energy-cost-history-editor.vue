<script>
  export default {compatConfig: {MODE: 3}};
</script>
<script setup>
  import {computed, ref, watch} from 'vue';
  import {DateTime} from 'luxon';
  import {useI18n} from 'vue-i18n';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {useEnergyCostStore} from '@/stores/energy-cost-store';
  import EnergyCostHistoryPeriod from './energy-cost-history-period.vue';
  import EnergyCostTariffPicker from './energy-cost-tariff-picker.vue';
  import EnergyCostCustomComposer from './energy-cost-custom-composer.vue';
  import {changeTransition, copyDraft, insertTransition, removeTransition, replacePeriodComponents, timelineError} from './energy-cost-history';
  import {cloneTariffComponents, dateFromDatetime, dateToDatetime} from './energy-cost-plan-utils';
  import {compatibleComponents} from './energy-cost-composer';
  const props = defineProps({configuration: Object, errors: Array, addingTransition: Boolean});
  const emit = defineEmits(['update:periods', 'update:addingTransition']);
  const {locale} = useI18n();
  const store = useEnergyCostStore();
  const newDate = ref('');
  const error = ref('');
  const selectedStarter = ref('');
  const selectedComponents = ref(null);
  const custom = ref(false);
  const busy = ref(false);
  const selectionFailed = ref(false);
  let selectionVersion = 0;
  const invalid = computed(() => timelineError(props.configuration.periods));
  const draftPeriod = computed(() => {
    const instant = Date.parse(dateToDatetime(newDate.value, props.configuration.timezone));
    const inherited =
      props.configuration.periods.find(
        (period) => (!period.validFrom || Date.parse(period.validFrom) <= instant) && (!period.validTo || instant < Date.parse(period.validTo))
      ) || props.configuration.periods.at(-1);
    return {...copyDraft(inherited), components: copyDraft(selectedComponents.value || inherited.components)};
  });
  function resetSelection() {
    selectionVersion++;
    busy.value = false;
    selectionFailed.value = false;
    selectedStarter.value = '';
    selectedComponents.value = null;
    custom.value = false;
    error.value = '';
  }
  watch(
    () => props.addingTransition,
    (adding) => {
      resetSelection();
      if (adding) newDate.value = DateTime.now().setZone(props.configuration.timezone).toISODate();
    },
    {immediate: true}
  );
  async function selectStarter(id) {
    if (!id) {
      resetSelection();
      return;
    }
    const version = ++selectionVersion;
    selectedStarter.value = id;
    busy.value = true;
    selectionFailed.value = false;
    error.value = '';
    try {
      const starter = await store.fetchTariff(id);
      const components = cloneTariffComponents(starter);
      await Promise.all(components.map((component) => store.fetchPreset(component.presetId)));
      if (version !== selectionVersion) return;
      if (!compatibleComponents(components, props.configuration, store.presetDetailsById)) throw new Error('Choose a compatible tariff.'); // i18n
      selectedComponents.value = components;
      custom.value = false;
    } catch (reason) {
      if (version === selectionVersion) {
        selectionFailed.value = true;
        error.value = reason.body?.message || reason.message;
      }
    } finally {
      if (version === selectionVersion) busy.value = false;
    }
  }
  function updateCustom(period) {
    selectionVersion++;
    busy.value = false;
    selectionFailed.value = false;
    selectedComponents.value = copyDraft(period.components);
    selectedStarter.value = '';
    error.value = '';
  }
  function updatePeriod(index, period) {
    let periods = props.configuration.periods.map((entry, position) => (position === index ? period : entry));
    if (index > 0 && period.validFrom !== props.configuration.periods[index].validFrom)
      periods = changeTransition(periods, index, dateFromDatetime(period.validFrom, props.configuration.timezone), props.configuration.timezone);
    emit('update:periods', periods);
  }
  function insert() {
    if (busy.value || selectionFailed.value) return;
    try {
      let periods = insertTransition(props.configuration.periods, newDate.value, props.configuration.timezone);
      if (selectedComponents.value) {
        if (!selectedComponents.value.length) throw new Error('Choose a compatible tariff.'); // i18n
        const boundary = dateToDatetime(newDate.value, props.configuration.timezone);
        const index = periods.findIndex((period) => period.validFrom === boundary);
        periods = replacePeriodComponents(periods, index, selectedComponents.value);
      }
      emit('update:periods', periods);
      resetSelection();
      newDate.value = '';
      emit('update:addingTransition', false);
      error.value = '';
    } catch (reason) {
      error.value = reason.message;
    }
  }
  function cancelTransition() {
    resetSelection();
    emit('update:addingTransition', false);
    newDate.value = '';
    error.value = '';
  }
</script>
<template>
  <div>
    <section v-for="(period, index) in configuration.periods" :key="period.validFrom || 'initial'" class="tariff-history-entry">
      <energy-cost-history-period
        :period="period"
        :index="index"
        :configuration="configuration"
        :errors="errors?.[index]"
        @update:period="updatePeriod(index, $event)"
        @remove="emit('update:periods', removeTransition(configuration.periods, index))"
      />
    </section>
    <slot name="actions" />
    <transition-expand>
      <div v-if="addingTransition" class="form-group">
        <energy-cost-tariff-picker
          :tariffs="store.tariffs"
          :model-value="selectedStarter"
          id-prefix="new-tariff-transition"
          @update:model-value="selectStarter"
        />
        <label for="new-tariff-transition-date">{{ $t('When the tariff should change?') }}</label>
        <input id="new-tariff-transition-date" v-model="newDate" type="date" class="form-control" :lang="locale" />
        <button type="button" class="btn btn-link" @click="custom = !custom">{{ $t('Compose custom tariff') }}</button>
        <energy-cost-custom-composer v-if="custom" :period="draftPeriod" :configuration="configuration" @update:period="updateCustom" />
        <div class="transition-actions">
          <button type="button" class="btn btn-default" :disabled="busy" @click="insert">
            <i class="pe-7s-date" aria-hidden="true"></i> {{ $t('Add tariff change') }}
          </button>
          <button type="button" class="btn btn-link" @click="cancelTransition">{{ $t('Cancel') }}</button>
        </div>
      </div>
    </transition-expand>
    <div v-if="invalid || error" class="text-danger">{{ $t(invalid || error) }}</div>
  </div>
</template>
<style scoped>
  .transition-actions {
    margin-top: 15px;
  }
  .tariff-history-entry {
    padding: 15px 0;
    border-bottom: 1px solid #ddd;
  }
</style>
