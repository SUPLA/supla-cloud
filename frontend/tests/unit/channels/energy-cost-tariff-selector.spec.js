import {afterEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import EnergyCostTariffSelector from '@/channels/energy-cost/energy-cost-tariff-selector.vue';
import DialogWindow from '@/common/gui/dialog/DialogWindow.vue';
import {useEnergyCostStore} from '@/stores/energy-cost-store';

const wrappers = [];
afterEach(() => {
  wrappers.splice(0).forEach((wrapper) => wrapper.unmount());
  vi.restoreAllMocks();
});

async function selector() {
  const pinia = createPinia();
  setActivePinia(pinia);
  const store = useEnergyCostStore();
  const component = (kind, presetId, componentId) => ({kind, presetId, componentId, values: {}});
  const components = [
    component('ENERGY_PURCHASE', 'supply', 'energy'),
    component('DISTRIBUTION_VARIABLE', 'distribution', 'distribution'),
    component('DISTRIBUTION_FIXED', 'distribution', 'subscription'),
  ];
  const tariff = {id: 'ready', label: 'Ready-made tariff', components};
  store.tariffs = [tariff];
  store.tariffDetailsById = {ready: tariff};
  store.presets = ['supply', 'distribution'].map((id) => ({
    id,
    components: components.filter((component) => component.presetId === id),
  }));
  store.presetDetailsById = Object.fromEntries(
    store.presets.map((preset) => [
      preset.id,
      {
        id: preset.id,
        document: {
          currency: 'PLN',
          timezone: 'Europe/Warsaw',
          billingDefinitionTemplate: {components: preset.components.map((component) => ({id: component.componentId, kind: component.kind}))},
        },
      },
    ])
  );
  vi.spyOn(store, 'fetchPresets').mockResolvedValue(store.presets);
  vi.spyOn(store, 'fetchTariffs').mockResolvedValue(store.tariffs);
  vi.spyOn(store, 'fetchTariff').mockResolvedValue(tariff);
  vi.spyOn(store, 'fetchPreset').mockImplementation(async (id) => store.presetDetailsById[id]);
  const wrapper = mount(EnergyCostTariffSelector, {
    props: {modelValue: true, tariffs: store.tariffs, loading: false},
    global: {
      plugins: [pinia, createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})],
      stubs: {
        DialogWindow: {template: '<div><slot /></div>'},
        DialogContent: {template: '<div><slot name="header" /><slot /></div>'},
      },
    },
  });
  wrappers.push(wrapper);
  await flushPromises();
  const confirm = async () => {
    const dialog = {setLoading: vi.fn()};
    wrapper.findComponent(DialogWindow).vm.$emit('confirm', dialog);
    await flushPromises();
    return dialog;
  };
  const custom = async () => {
    await wrapper.findAll('.nav-tabs a')[1].trigger('click');
    await flushPromises();
  };
  return {wrapper, store, components, confirm, custom};
}

describe('simplified tariff selection', () => {
  it('starts with just the ready-made dropdown and custom-composition option', async () => {
    const {wrapper, confirm} = await selector();
    expect(wrapper.findAll('select')).toHaveLength(1);
    expect(wrapper.text()).toContain('Compose custom tariff');
    expect(wrapper.text()).not.toContain('Billing settings');
    expect(wrapper.text()).not.toContain('Add tariff change');
    expect(wrapper.findAll('button')).toHaveLength(0);
    await wrapper.get('select').setValue('ready');
    await confirm();
    expect(wrapper.emitted('select')).toEqual([['ready']]);
    expect(wrapper.emitted('compose')).toBeUndefined();
  });

  it('composes supply and distribution, including fixed charges, with one final confirmation', async () => {
    const {wrapper, components, custom, confirm} = await selector();
    await custom();
    expect(wrapper.findAll('select')).toHaveLength(2);
    expect(wrapper.findAll('button')).toHaveLength(0);
    expect(wrapper.text()).toContain('Electricity supply');
    expect(wrapper.text()).toContain('Distribution');
    await wrapper.findAll('select')[0].setValue('supply');
    await flushPromises();
    await wrapper.findAll('select')[1].setValue('distribution');
    await flushPromises();
    expect(wrapper.emitted('compose')).toBeUndefined();
    await confirm();
    expect(wrapper.emitted('compose')).toEqual([[components]]);
    expect(wrapper.emitted('select')).toBeUndefined();
  });

  it('blocks empty composition and ignores pending selection after cancelling', async () => {
    const {wrapper, store, custom, confirm} = await selector();
    await custom();
    const dialog = await confirm();
    expect(dialog.setLoading).toHaveBeenCalledWith(false);
    expect(wrapper.emitted('compose')).toBeUndefined();
    expect(wrapper.text()).toContain('Choose a compatible tariff.');
    let resolvePreset;
    store.fetchPreset.mockImplementation(() => new Promise((resolve) => (resolvePreset = resolve)));
    await wrapper.findAll('select')[0].setValue('supply');
    await confirm();
    expect(wrapper.emitted('compose')).toBeUndefined();
    await wrapper.setProps({modelValue: false});
    await wrapper.setProps({modelValue: true});
    resolvePreset(store.presetDetailsById.supply);
    await flushPromises();
    expect(wrapper.findAll('select')).toHaveLength(1);
    expect(wrapper.get('select').element.value).toBe('');
    await custom();
    expect(wrapper.findAll('select').map((select) => select.element.value)).toEqual(['', '']);
  });

  it('retains No tariff as an unassignment action', async () => {
    const {wrapper, confirm} = await selector();
    await wrapper.get('select').setValue('__no_tariff__');
    await confirm();
    expect(wrapper.emitted('unassign')).toHaveLength(1);
    expect(wrapper.emitted('select')).toBeUndefined();
  });
});
