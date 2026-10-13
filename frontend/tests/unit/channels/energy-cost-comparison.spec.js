import {afterEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {energyCostApi} from '@/api/energy-cost-api';
import Comparison from '@/channels/energy-cost/energy-cost-comparison.vue';
import Picker from '@/channels/energy-cost/energy-cost-tariff-picker.vue';
import Dialog from '@/common/gui/dialog/DialogWindow.vue';
import {calculation, plan, range, starters} from './energy-cost-comparison-fixtures';

afterEach(() => vi.restoreAllMocks());
async function setup() {
  const pinia = createPinia();
  setActivePinia(pinia);
  const store = useEnergyCostStore();
  store.tariffs = starters;
  store.tariffDetailsById = Object.fromEntries(starters.map((starter) => [starter.id, starter]));
  store.plans = [plan];
  store.assignmentsByChannelId = {3: {channelId: 3, planId: 10}, 4: {channelId: 4, planId: 10}};
  vi.spyOn(store, 'fetchTariffs').mockResolvedValue(starters);
  const simulate = vi.spyOn(energyCostApi, 'simulate').mockResolvedValue(calculation('80'));
  const assign = vi.spyOn(energyCostApi, 'assignStarter').mockResolvedValue({plan: {...plan, id: 11}, assignment: {channelId: 3, planId: 11}});
  const wrapper = mount(Comparison, {
    props: {channel: {id: 3}, plan, range, baseline: calculation()},
    global: {
      plugins: [pinia, createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})],
      stubs: {EnergyCostComparisonChart: true, DialogContent: {template: '<div><slot name="header"/><slot/></div>'}},
    },
  });
  await flushPromises();
  const select = async (id = 'g12w') => {
    wrapper.findComponent(Picker).vm.$emit('update:modelValue', id);
    await flushPromises();
  };
  return {wrapper, store, simulate, assign, select};
}
describe('tariff comparison interactions', () => {
  it('renders the actual historical baseline and simulates without persistence', async () => {
    const {wrapper, store, select, simulate, assign} = await setup();
    expect(wrapper.text()).toContain('Currently assigned');
    expect(wrapper.text()).toContain('Operator G11');
    expect(wrapper.text()).toContain('Operator G12w');
    await select();
    expect(simulate).toHaveBeenCalledWith(
      3,
      Date.parse(range.from) / 1000,
      Date.parse(range.to) / 1000,
      expect.objectContaining({periods: [{validFrom: null, validTo: null, components: starters[1].components}]})
    );
    expect(assign).not.toHaveBeenCalled();
    expect(store.assignmentsByChannelId[3].planId).toBe(10);
    expect(wrapper.text()).toContain('Savings: 20.00 PLN');
    expect(wrapper.text()).toContain('Fixed charges are not included.');
  });
  it('recalculates the same selected range and clears old results', async () => {
    const {wrapper, select, simulate} = await setup();
    await select();
    simulate.mockReturnValue(new Promise(() => {}));
    await wrapper.setProps({range: {...range, to: '2026-09-01T03:00:00Z'}, baselineLoading: true});
    await flushPromises();
    expect(simulate).toHaveBeenLastCalledWith(3, Date.parse(range.from) / 1000, Date.parse('2026-09-01T03:00:00Z') / 1000, expect.any(Object));
    expect(wrapper.text()).not.toContain('Savings:');
  });
  it('ignores stale simulation responses when another tariff is selected', async () => {
    const {wrapper, simulate, select} = await setup();
    let resolve;
    simulate.mockReturnValueOnce(
      new Promise((done) => {
        resolve = done;
      })
    );
    await select('g11');
    await select('g12w');
    expect(wrapper.text()).toContain('Savings: 20.00 PLN');
    resolve(calculation('999'));
    await flushPromises();
    expect(wrapper.text()).toContain('Savings: 20.00 PLN');
  });
  it('requires confirmation and replaces only this meter assignment on success', async () => {
    const {wrapper, store, select, assign} = await setup();
    await select();
    await wrapper.get('button').trigger('click');
    expect(assign).not.toHaveBeenCalled();
    expect(wrapper.findComponent(Dialog).props('modelValue')).toBe(true);
    expect(wrapper.text()).toContain('Historical cost calculations will use the newly assigned tariff.');
    wrapper.findComponent(Dialog).vm.$emit('confirm', {setLoading: vi.fn()});
    await flushPromises();
    expect(assign).toHaveBeenCalledOnce();
    expect(store.assignmentsByChannelId[3].planId).toBe(11);
    expect(store.assignmentsByChannelId[4].planId).toBe(10);
    expect(wrapper.emitted('applied')).toHaveLength(1);
  });
  it('preserves assignments and simulation when assignment fails', async () => {
    const {wrapper, store, select, assign} = await setup();
    await select();
    assign.mockRejectedValue({body: {message: 'Assignment failed'}});
    await wrapper.get('button').trigger('click');
    wrapper.findComponent(Dialog).vm.$emit('confirm', {setLoading: vi.fn()});
    await flushPromises();
    expect(store.assignmentsByChannelId[3].planId).toBe(10);
    expect(wrapper.text()).toContain('Assignment failed');
    expect(wrapper.emitted('applied')).toBeUndefined();
    expect(wrapper.text()).toContain('Savings: 20.00 PLN');
  });
  it('handles baseline and alternative errors independently', async () => {
    const {wrapper, select, simulate} = await setup();
    simulate.mockRejectedValue({body: {message: 'Rates unavailable'}});
    await select();
    expect(wrapper.text()).toContain('This tariff could not be calculated for the selected period.');
    expect(wrapper.text()).toContain('100.00 PLN');
    await wrapper.setProps({baseline: null, baselineError: 'Baseline unavailable'});
    expect(wrapper.text()).toContain('Baseline unavailable');
  });
});
