import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import ChannelEnergyCosts from '@/channels/energy-cost/channel-energy-costs.vue';
import EnergyCostTariffSelector from '@/channels/energy-cost/energy-cost-tariff-selector.vue';
import EnergyCostPlanDialog from '@/channels/energy-cost/energy-cost-plan-dialog.vue';
import ChannelFunction from '@/common/enums/channel-function';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {configurationFromTariff} from '@/channels/energy-cost/energy-cost-plan-utils';
import {insertTransition} from '@/channels/energy-cost/energy-cost-history';

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
  const wrapper = mount(ChannelEnergyCosts, {
    props: {channel: {id: 3, functionId: ChannelFunction.ELECTRICITYMETER, config: {simpleCostSettingsAvailable: true}}},
    global: {
      plugins: [pinia, createI18n({legacy: false, locale: 'pl', missingWarn: false, fallbackWarn: false})],
      stubs: {
        LoadingCover: {template: '<div><slot /></div>'},
        EnergyCostDashboard: true,
        EnergyCostTariffSelector: true,
        EnergyCostPlanDialog: {compatConfig: {MODE: 3}, props: ['modelValue', 'plan', 'channelId'], template: '<div />'},
      },
    },
  });
  await flushPromises();
  return {wrapper, store, assignStarter, plan, replacement};
}

describe('meter tariff setup and summary', () => {
  it('shows the current tariff and both effective dates without the change-tariff shortcut', async () => {
    const {wrapper} = await editor();
    const summary = wrapper.get('.energy-cost-assigned-plan');
    expect(summary.text()).toContain('Latest G12');
    expect(summary.text()).toContain('From date: 01.05.2026');
    expect(summary.text()).toContain('To date: 30.04.2030');
    expect(summary.text()).not.toContain('Scheduled G13');
    expect(summary.text()).not.toContain('Change tariff');
    expect(wrapper.findComponent(EnergyCostTariffSelector).exists()).toBe(false);
  });

  it('shows only the end date when the first period is currently effective', async () => {
    vi.setSystemTime(new Date('2026-04-15T12:00:00Z'));
    const {wrapper} = await editor();
    const summary = wrapper.get('.energy-cost-assigned-plan');
    expect(summary.text()).toContain('Original G11');
    expect(summary.text()).not.toContain('From date');
    expect(summary.text()).toContain('To date: 30.04.2026');
  });

  it('switches to the following period exactly at local midnight and hides the final end date', async () => {
    vi.setSystemTime(new Date('2030-04-30T22:00:00Z'));
    const {wrapper} = await editor();
    const summary = wrapper.get('.energy-cost-assigned-plan');
    expect(summary.text()).toContain('Scheduled G13');
    expect(summary.text()).toContain('From date: 01.05.2030');
    expect(summary.text()).not.toContain('To date');
  });

  it('hides the start date for a single-period plan', async () => {
    const {wrapper} = await editor(false);
    expect(wrapper.get('.energy-cost-assigned-plan').text()).not.toContain('From date');
    expect(wrapper.findComponent(EnergyCostTariffSelector).exists()).toBe(false);
  });

  it('can still assign a starter when the meter has no tariff', async () => {
    const {wrapper, assignStarter, replacement} = await editor(false, false);
    expect(wrapper.text()).toContain('Set up electricity costs');
    const setupButtons = wrapper.get('section.well').findAll('button');
    expect(setupButtons).toHaveLength(1);
    expect(setupButtons[0].text()).toBe('Choose tariff');
    expect(setupButtons[0].classes()).toContain('btn-green');
    expect(wrapper.get('.alert-warning').text()).toBe(
      'After assigning a tariff, the basic price per kWh and currency settings in channel configuration will be hidden.'
    );
    wrapper.findComponent(EnergyCostTariffSelector).vm.$emit('select', 'replacement');
    await flushPromises();
    expect(assignStarter).toHaveBeenCalledWith(3, 'replacement', configurationFromTariff(replacement));
  });

  it('hides the basic-settings warning when simple costs are unavailable', async () => {
    const {wrapper} = await editor(false, false);
    await wrapper.setProps({channel: {id: 3, functionId: ChannelFunction.ELECTRICITYMETER, config: {simpleCostSettingsAvailable: false}}});
    expect(wrapper.text()).toContain('Set up electricity costs');
    expect(wrapper.find('.alert-warning').exists()).toBe(false);
  });

  it('creates and assigns a composed tariff to a meter without an assignment', async () => {
    const {wrapper, store, replacement} = await editor(false, false);
    const create = vi.spyOn(store, 'createPlan').mockResolvedValue({id: 12});
    const assign = vi.spyOn(store, 'assignPlan').mockResolvedValue({channelId: 3, planId: 12});
    wrapper.findComponent(EnergyCostTariffSelector).vm.$emit('compose', replacement.components);
    await flushPromises();
    expect(create).toHaveBeenCalledWith({name: 'Custom tariff', configuration: configurationFromTariff(replacement)});
    expect(assign).toHaveBeenCalledWith(3, 12);
    expect(wrapper.findComponent(EnergyCostTariffSelector).props('modelValue')).toBe(false);
  });

  it('opens tariff history and settings for the assigned plan', async () => {
    const {wrapper, plan} = await editor();
    const settings = wrapper.get('.energy-cost-assigned-plan .pull-right').get('button');
    expect(settings.text()).toBe('Tariff history / settings');
    expect(settings.classes()).toContain('btn-default');
    expect(settings.find('i.pe-7s-config').exists()).toBe(true);
    await settings.trigger('click');
    await flushPromises();
    const dialog = wrapper.findComponent(EnergyCostPlanDialog);
    expect(dialog.props('modelValue')).toBe(true);
    expect(dialog.props('plan')).toEqual(plan);
    expect(dialog.props('channelId')).toBe(3);
  });
});
