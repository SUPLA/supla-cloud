import {beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import EnergyCostPlanDialog from '@/channels/energy-cost/energy-cost-plan-dialog.vue';
import DialogWindow from '@/common/gui/dialog/DialogWindow.vue';
import EnergyCostHistoryEditor from '@/channels/energy-cost/energy-cost-history-editor.vue';
import EnergyCostSharedSave from '@/channels/energy-cost/energy-cost-shared-save.vue';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {energyCostApi} from '@/api/energy-cost-api';
import {configurationFromTariff} from '@/channels/energy-cost/energy-cost-plan-utils';
import {insertTransition} from '@/channels/energy-cost/energy-cost-history';

vi.mock('@/api/energy-cost-api', () => ({
  energyCostApi: {
    getPresets: vi.fn(),
    getPlanStarters: vi.fn(),
    getPreset: vi.fn(),
    getPlanChannels: vi.fn(),
    saveAssignedConfiguration: vi.fn(),
    deletePlan: vi.fn(),
  },
}));

const components = [{kind: 'ENERGY_PURCHASE', presetId: 'supply', componentId: 'energy', values: {}}];
const configuration = () => configurationFromTariff({components});
const meters = [
  {id: 1, caption: 'Kitchen'},
  {id: 2, caption: 'Garage'},
];
const clickSave = async (wrapper) => {
  wrapper.findComponent(DialogWindow).vm.$emit('confirm', {setLoading: vi.fn()});
  await flushPromises();
};

describe('shared plan save workflow', () => {
  beforeEach(() => {
    vi.clearAllMocks();
    energyCostApi.getPresets.mockResolvedValue([]);
    energyCostApi.getPlanStarters.mockResolvedValue([]);
    energyCostApi.getPreset.mockResolvedValue({
      id: 'supply',
      document: {
        currency: 'PLN',
        timezone: 'Europe/Warsaw',
        taxContext: {jurisdiction: 'PL', customerClass: 'HOUSEHOLD'},
        billingDefinitionTemplate: {components: [{id: 'energy', kind: 'ENERGY_PURCHASE'}]},
      },
    });
    energyCostApi.getPlanChannels.mockResolvedValue(meters);
    energyCostApi.saveAssignedConfiguration.mockImplementation(async (channelId, document, scope) => ({
      plan: {id: scope === 'allMeters' ? 10 : 20, configuration: document},
      assignment: {channelId, planId: scope === 'allMeters' ? 10 : 20},
      affectedAssignments: scope === 'allMeters' ? meters.map((meter) => ({channelId: meter.id, planId: 10})) : [{channelId, planId: 20}],
    }));
  });

  async function open() {
    const pinia = createPinia();
    setActivePinia(pinia);
    const store = useEnergyCostStore();
    const plan = {id: 10, name: 'Internal name', configuration: configuration()};
    store.plans = [plan];
    store.assignmentsByChannelId = {1: {channelId: 1, planId: 10}, 2: {channelId: 2, planId: 10}};
    const wrapper = mount(EnergyCostPlanDialog, {
      props: {modelValue: true, plan, channelId: 1},
      global: {
        plugins: [pinia, createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})],
        stubs: {
          DialogWindow: {template: '<div><slot /></div>'},
          DialogContent: {template: '<div><slot name="header"/><slot/><slot name="footer"/></div>'},
        },
      },
    });
    await flushPromises();
    wrapper.findComponent(EnergyCostHistoryEditor).vm.$emit('update:periods', insertTransition(configuration().periods, '2026-05-01', 'Europe/Warsaw'));
    await flushPromises();
    return {wrapper, store, plan};
  }

  it('lists every affected meter and defaults to saving only this meter', async () => {
    const {wrapper, store, plan} = await open();
    await clickSave(wrapper);
    expect(energyCostApi.saveAssignedConfiguration).not.toHaveBeenCalled();
    const confirmation = wrapper.findComponent(EnergyCostSharedSave);
    expect(confirmation.findAll('li').map((item) => item.text())).toEqual(['Kitchen (#1)', 'Garage (#2)']);
    expect(confirmation.props('modelValue')).toBe('thisMeter');
    expect(wrapper.text()).not.toContain('Internal name');
    expect(wrapper.findAll('button').some((button) => button.text() === 'Delete')).toBe(false);
    await clickSave(wrapper);
    expect(energyCostApi.saveAssignedConfiguration).toHaveBeenCalledWith(1, expect.any(Object), 'thisMeter', 10, [1, 2]);
    expect(store.assignmentsByChannelId[1].planId).toBe(20);
    expect(store.assignmentsByChannelId[2].planId).toBe(10);
    expect(store.plansById[20].configuration.periods).toHaveLength(2);
    expect(plan.configuration.periods).toHaveLength(1);
    expect(energyCostApi.deletePlan).not.toHaveBeenCalled();
  });

  it('updates all affected assignments and replaces the reactive plan to trigger recalculation', async () => {
    const {wrapper, store, plan} = await open();
    await clickSave(wrapper);
    await wrapper.findComponent(EnergyCostSharedSave).findAll('input')[1].setChecked();
    await clickSave(wrapper);
    expect(energyCostApi.saveAssignedConfiguration).toHaveBeenCalledWith(1, expect.any(Object), 'allMeters', 10, [1, 2]);
    expect(store.plansById[10]).not.toBe(plan);
    expect(store.plansById[10].configuration.periods).toHaveLength(2);
    expect(store.assignmentReadyByChannelId).toMatchObject({1: true, 2: true});
    expect(wrapper.emitted('saved')[0][0].id).toBe(10);
  });

  it('saves a private plan without scope confirmation', async () => {
    energyCostApi.getPlanChannels.mockResolvedValue([meters[0]]);
    const {wrapper} = await open();
    await clickSave(wrapper);
    expect(energyCostApi.saveAssignedConfiguration).toHaveBeenCalledWith(1, expect.any(Object), 'thisMeter', 10, [1]);
  });

  it('requires renewed confirmation if sharing changes before save', async () => {
    const {wrapper} = await open();
    await clickSave(wrapper);
    await wrapper.findComponent(EnergyCostSharedSave).findAll('input')[1].setChecked();
    energyCostApi.getPlanChannels.mockResolvedValue([...meters, {id: 3, caption: 'Workshop'}]);
    await clickSave(wrapper);
    expect(energyCostApi.saveAssignedConfiguration).not.toHaveBeenCalled();
    expect(wrapper.findComponent(EnergyCostSharedSave).props('modelValue')).toBe('thisMeter');
    expect(wrapper.text()).toContain('Workshop');
  });

  it('does not create a private copy or ask for scope when nothing changed', async () => {
    const {wrapper} = await open();
    wrapper.findComponent(EnergyCostHistoryEditor).vm.$emit('update:periods', configuration().periods);
    await flushPromises();
    await clickSave(wrapper);
    expect(energyCostApi.getPlanChannels).not.toHaveBeenCalled();
    expect(energyCostApi.saveAssignedConfiguration).not.toHaveBeenCalled();
    expect(wrapper.emitted('update:modelValue')).toEqual([[false]]);
  });
});
