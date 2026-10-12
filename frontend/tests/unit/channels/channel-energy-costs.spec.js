import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import ChannelEnergyCosts from '@/channels/energy-cost/channel-energy-costs.vue';
import EnergyCostTariffSelector from '@/channels/energy-cost/energy-cost-tariff-selector.vue';
import ChannelFunction from '@/common/enums/channel-function';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {energyCostApi} from '@/api/energy-cost-api';
import {configurationFromTariff} from '@/channels/energy-cost/energy-cost-plan-utils';
import {copyDraft, insertTransition} from '@/channels/energy-cost/energy-cost-history';

beforeEach(() => {
  vi.useFakeTimers({toFake: ['Date']});
  vi.setSystemTime(new Date('2026-10-12T12:00:00Z'));
});
afterEach(() => {
  vi.restoreAllMocks();
  vi.useRealTimers();
});

async function editor(multiple = true, assigned = true) {
  const pinia = createPinia();
  setActivePinia(pinia);
  const store = useEnergyCostStore();
  const component = (presetId) => ({kind: 'ENERGY_PURCHASE', presetId, componentId: 'energy', values: {}});
  const original = {id: 'original', label: 'Original G11', components: [component('g11')]};
  const latest = {id: 'latest', label: 'Latest G12', components: [component('g12')]};
  const scheduled = {id: 'scheduled', label: 'Scheduled G13', components: [component('g13')]};
  const replacement = {id: 'replacement', label: 'Replacement G12w', components: [component('g12w')]};
  const configuration = configurationFromTariff(original, new Date('2026-01-01T00:00:00Z'));
  configuration.billingCycles = [{anchor: '2026-01-15', length: 2, unit: 'MONTH'}];
  if (multiple) {
    configuration.periods = insertTransition(configuration.periods, '2026-05-01', configuration.timezone);
    configuration.periods[1].components = latest.components;
    configuration.periods = insertTransition(configuration.periods, '2030-05-01', configuration.timezone);
    configuration.periods[2].components = scheduled.components;
    configuration.periods[0].components[0].values.price = '0.99';
  }
  const plan = {id: 10, name: 'Shared tariff', configuration};
  const originalPlan = copyDraft(plan);
  store.plans = assigned ? [plan] : [];
  store.plansReady = true;
  store.assignmentReadyByChannelId = {3: true};
  store.assignmentsByChannelId = assigned ? {3: {channelId: 3, planId: 10}, 4: {channelId: 4, planId: 10}} : {};
  store.tariffs = [original, latest, scheduled, replacement];
  store.tariffDetailsById = {original, latest, scheduled, replacement};
  vi.spyOn(store, 'fetchPresentationCatalogs').mockResolvedValue();
  vi.spyOn(store, 'fetchPlans').mockResolvedValue(store.plans);
  vi.spyOn(store, 'fetchAssignment').mockResolvedValue(store.assignmentsByChannelId[3]);
  const assignStarter = vi.spyOn(store, 'assignStarter').mockResolvedValue(plan);
  const save = vi.spyOn(energyCostApi, 'saveAssignedConfiguration').mockImplementation(async (channelId, document) => ({
    plan: {...plan, id: 11, configuration: document},
    assignment: {channelId, planId: 11},
  }));
  const wrapper = mount(ChannelEnergyCosts, {
    props: {channel: {id: 3, functionId: ChannelFunction.ELECTRICITYMETER}},
    global: {
      plugins: [pinia, createI18n({legacy: false, locale: 'pl', missingWarn: false, fallbackWarn: false})],
      stubs: {
        LoadingCover: {template: '<div><slot /></div>'},
        EnergyCostDashboard: true,
        EnergyCostTariffSelector: true,
        EnergyCostPlanDialog: true,
      },
    },
  });
  await flushPromises();
  return {wrapper, store, save, assignStarter, originalPlan, replacement};
}

describe('meter tariff replacement', () => {
  it('shows the current tariff and both effective dates and replaces only that period for this meter', async () => {
    const {wrapper, store, save, assignStarter, originalPlan, replacement} = await editor();
    const summary = wrapper.get('.energy-cost-assigned-plan');
    expect(summary.text()).toContain('Latest G12');
    expect(summary.text()).toContain('From date: 01.05.2026');
    expect(summary.text()).toContain('To date: 30.04.2030');
    expect(summary.text()).not.toContain('Scheduled G13');
    wrapper.findComponent(EnergyCostTariffSelector).vm.$emit('select', 'replacement');
    await flushPromises();
    const expected = copyDraft(originalPlan.configuration);
    expected.periods[1].components = replacement.components;
    expect(save).toHaveBeenCalledWith(3, expected, 'thisMeter', 10, undefined);
    expect(assignStarter).not.toHaveBeenCalled();
    expect(store.plans.find((plan) => plan.id === 10)).toEqual(originalPlan);
    expect(store.assignmentsByChannelId[4].planId).toBe(10);
    expect(store.assignmentsByChannelId[3].planId).toBe(11);
    expect(summary.text()).toContain('Replacement G12w');
    expect(summary.text()).toContain('From date: 01.05.2026');
    expect(summary.text()).toContain('To date: 30.04.2030');
  });

  it('shows only the end date and replaces the first period when it is currently effective', async () => {
    vi.setSystemTime(new Date('2026-04-15T12:00:00Z'));
    const {wrapper, save, originalPlan, replacement} = await editor();
    const summary = wrapper.get('.energy-cost-assigned-plan');
    expect(summary.text()).toContain('Original G11');
    expect(summary.text()).not.toContain('From date');
    expect(summary.text()).toContain('To date: 30.04.2026');
    wrapper.findComponent(EnergyCostTariffSelector).vm.$emit('select', 'replacement');
    await flushPromises();
    const expected = copyDraft(originalPlan.configuration);
    expected.periods[0].components = replacement.components;
    expect(save).toHaveBeenCalledWith(3, expected, 'thisMeter', 10, undefined);
  });

  it('switches to the following period exactly at local midnight and hides the final end date', async () => {
    vi.setSystemTime(new Date('2030-04-30T22:00:00Z'));
    const {wrapper, save, originalPlan, replacement} = await editor();
    const summary = wrapper.get('.energy-cost-assigned-plan');
    expect(summary.text()).toContain('Scheduled G13');
    expect(summary.text()).toContain('From date: 01.05.2030');
    expect(summary.text()).not.toContain('To date');
    wrapper.findComponent(EnergyCostTariffSelector).vm.$emit('select', 'replacement');
    await flushPromises();
    const expected = copyDraft(originalPlan.configuration);
    expected.periods[2].components = replacement.components;
    expect(save).toHaveBeenCalledWith(3, expected, 'thisMeter', 10, undefined);
  });

  it('uses starter assignment and hides the start date for a single-period plan', async () => {
    const {wrapper, save, assignStarter, replacement} = await editor(false);
    expect(wrapper.get('.energy-cost-assigned-plan').text()).not.toContain('From date');
    wrapper.findComponent(EnergyCostTariffSelector).vm.$emit('select', 'replacement');
    await flushPromises();
    expect(assignStarter).toHaveBeenCalledWith(3, 'replacement', configurationFromTariff(replacement));
    expect(save).not.toHaveBeenCalled();
  });

  it('can still assign a starter when the meter has no tariff', async () => {
    const {wrapper, save, assignStarter, replacement} = await editor(false, false);
    expect(wrapper.text()).toContain('Set up electricity costs');
    wrapper.findComponent(EnergyCostTariffSelector).vm.$emit('select', 'replacement');
    await flushPromises();
    expect(assignStarter).toHaveBeenCalledWith(3, 'replacement', configurationFromTariff(replacement));
    expect(save).not.toHaveBeenCalled();
  });
});
