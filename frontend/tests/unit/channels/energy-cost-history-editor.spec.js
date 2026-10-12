import {afterEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import EnergyCostHistoryEditor from '@/channels/energy-cost/energy-cost-history-editor.vue';
import EnergyCostHistoryPeriod from '@/channels/energy-cost/energy-cost-history-period.vue';
import EnergyCostTariffPicker from '@/channels/energy-cost/energy-cost-tariff-picker.vue';
import EnergyCostCustomComposer from '@/channels/energy-cost/energy-cost-custom-composer.vue';
import EnergyCostTariffChoice from '@/channels/energy-cost/energy-cost-tariff-choice.vue';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {insertTransition, timelineError} from '@/channels/energy-cost/energy-cost-history';

afterEach(() => vi.restoreAllMocks());
const components = [{kind: 'ENERGY_PURCHASE', presetId: 'g12w', componentId: 'energy', values: {}}];
const button = (wrapper, label) => wrapper.findAll('button').find((entry) => entry.text() === label);
const customTab = (wrapper) => wrapper.findAll('.nav-tabs a').find((entry) => entry.text() === 'Compose custom tariff');

function editor() {
  const pinia = createPinia();
  setActivePinia(pinia);
  const store = useEnergyCostStore();
  const starter = {id: 'replacement', label: 'G12w', components};
  store.tariffs = [starter];
  store.tariffDetailsById = {replacement: starter};
  store.presetDetailsById = {
    g12w: {
      document: {
        currency: 'PLN',
        timezone: 'Europe/Warsaw',
        billingDefinitionTemplate: {components: [{id: 'energy', kind: 'ENERGY_PURCHASE'}]},
      },
    },
  };
  const timezone = 'Europe/Warsaw';
  const periods = insertTransition([{validFrom: null, validTo: null, components: []}], '2026-05-01', timezone);
  return mount(EnergyCostHistoryEditor, {
    props: {configuration: {currency: 'PLN', timezone, periods}},
    global: {
      plugins: [pinia, createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})],
      stubs: {EnergyCostHistoryPeriod: true, EnergyCostCustomComposer: true},
    },
  });
}

describe('tariff period history integration', () => {
  it('updates the preceding end date when a period commits its edited start date', async () => {
    const wrapper = editor();
    const period = wrapper.props('configuration').periods[1];
    wrapper.findAllComponents(EnergyCostHistoryPeriod)[1].vm.$emit('update:period', {...period, validFrom: '2026-06-01T00:00:00+02:00'});
    const periods = wrapper.emitted('update:periods')[0][0];
    expect(periods[0].validTo).toBe('2026-06-01T00:00:00+02:00');
    expect(periods[1].validFrom).toBe(periods[0].validTo);
    expect(timelineError(periods)).toBe('');
  });

  it('removes a period from the period view and restores the open-ended predecessor', () => {
    const wrapper = editor();
    wrapper.findAllComponents(EnergyCostHistoryPeriod)[1].vm.$emit('remove');
    expect(wrapper.emitted('update:periods')[0][0]).toEqual([{validFrom: null, validTo: null, components: []}]);
  });

  it('extends the following tariff back to the start of history when removing the first period', async () => {
    const wrapper = editor();
    const configuration = wrapper.props('configuration');
    const components = [{kind: 'ENERGY_PURCHASE', presetId: 'g12', componentId: 'energy', values: {price: '0.99'}}];
    await wrapper.setProps({configuration: {...configuration, periods: [configuration.periods[0], {...configuration.periods[1], components}]}});
    wrapper.findAllComponents(EnergyCostHistoryPeriod)[0].vm.$emit('remove');
    expect(wrapper.emitted('update:periods')[0][0]).toEqual([{validFrom: null, validTo: null, components}]);
  });

  it('stages a chosen tariff and applies it only to the inserted period', async () => {
    const wrapper = editor();
    const configuration = wrapper.props('configuration');
    const previous = [{kind: 'ENERGY_PURCHASE', presetId: 'g11', componentId: 'energy', values: {price: '0.99'}}];
    const periods = configuration.periods.map((period) => ({...period, components: previous}));
    await wrapper.setProps({configuration: {...configuration, periods}, addingTransition: true});
    expect(wrapper.findComponent(EnergyCostTariffChoice).exists()).toBe(true);
    expect(wrapper.findAll('.nav-tabs a').map((tab) => tab.text())).toEqual(['Choose existing tariff', 'Compose custom tariff']);
    expect(button(wrapper, 'Compose custom tariff')).toBeUndefined();
    await wrapper.get('#new-tariff-transition-date').setValue('2026-03-01');
    wrapper.findComponent(EnergyCostTariffPicker).vm.$emit('update:modelValue', 'replacement');
    await flushPromises();
    expect(wrapper.emitted('update:periods')).toBeUndefined();
    await button(wrapper, 'Add tariff change').trigger('click');
    const updated = wrapper.emitted('update:periods')[0][0];
    expect(updated[1].components).toEqual(components);
    expect(updated[0].components).toEqual(previous);
    expect(updated[2]).toEqual(periods[1]);
    expect(updated[1].validFrom).toBe('2026-03-01T00:00:00+01:00');
    expect(timelineError(updated)).toBe('');
    expect(configuration.periods).toHaveLength(2);
  });

  it('retains the selected tariff when correcting an invalid transition date', async () => {
    const wrapper = editor();
    await wrapper.setProps({addingTransition: true});
    wrapper.findComponent(EnergyCostTariffPicker).vm.$emit('update:modelValue', 'replacement');
    await flushPromises();
    await wrapper.get('#new-tariff-transition-date').setValue('2026-05-01');
    await button(wrapper, 'Add tariff change').trigger('click');
    expect(wrapper.emitted('update:periods')).toBeUndefined();
    await wrapper.get('#new-tariff-transition-date').setValue('2026-06-01');
    await button(wrapper, 'Add tariff change').trigger('click');
    expect(wrapper.emitted('update:periods')[0][0][2].components).toEqual(components);
  });

  it('discards composed tariff drafts on Cancel and permits composing a fresh transition', async () => {
    const wrapper = editor();
    await wrapper.setProps({addingTransition: true});
    await customTab(wrapper).trigger('click');
    expect(wrapper.findComponent(EnergyCostTariffPicker).exists()).toBe(false);
    const composer = wrapper.findComponent(EnergyCostCustomComposer);
    composer.vm.$emit('update:period', {...composer.props('period'), components});
    await button(wrapper, 'Cancel').trigger('click');
    expect(wrapper.emitted('update:periods')).toBeUndefined();
    await wrapper.setProps({addingTransition: false});
    await wrapper.setProps({addingTransition: true});
    await customTab(wrapper).trigger('click');
    expect(wrapper.findComponent(EnergyCostCustomComposer).props('period').components).toEqual([]);
    wrapper.findComponent(EnergyCostCustomComposer).vm.$emit('update:period', {components});
    await wrapper.get('#new-tariff-transition-date').setValue('2026-06-01');
    await button(wrapper, 'Add tariff change').trigger('click');
    expect(wrapper.emitted('update:periods')[0][0][2].components).toEqual(components);
  });

  it('ignores a tariff selection that finishes after cancelling and reopening the form', async () => {
    const wrapper = editor();
    const store = useEnergyCostStore();
    let resolve;
    vi.spyOn(store, 'fetchTariff').mockReturnValue(
      new Promise((done) => {
        resolve = done;
      })
    );
    await wrapper.setProps({addingTransition: true});
    wrapper.findComponent(EnergyCostTariffPicker).vm.$emit('update:modelValue', 'replacement');
    await button(wrapper, 'Cancel').trigger('click');
    await wrapper.setProps({addingTransition: false});
    await wrapper.setProps({addingTransition: true});
    resolve(store.tariffDetailsById.replacement);
    await flushPromises();
    await wrapper.get('#new-tariff-transition-date').setValue('2026-06-01');
    await button(wrapper, 'Add tariff change').trigger('click');
    expect(wrapper.emitted('update:periods')[0][0][2].components).toEqual([]);
  });
});
