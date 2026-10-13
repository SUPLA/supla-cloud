import {afterEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import Dashboard from '@/channels/energy-cost/energy-cost-dashboard.vue';
import Comparison from '@/channels/energy-cost/energy-cost-comparison.vue';
import RangeSelector from '@/channels/energy-cost/energy-cost-range-selector.vue';
import {energyCostApi} from '@/api/energy-cost-api';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {calculation, plan, range} from './energy-cost-comparison-fixtures';

vi.mock('vue-router', () => ({useRoute: () => ({query: {}}), useRouter: () => ({replace: vi.fn()})}));
afterEach(() => {
  vi.restoreAllMocks();
  vi.unstubAllGlobals();
  sessionStorage.clear();
});
async function dashboard() {
  vi.stubGlobal(
    'Worker',
    class {
      postMessage() {}
      terminate() {}
    }
  );
  const pinia = createPinia();
  setActivePinia(pinia);
  vi.spyOn(useEnergyCostStore(), 'fetchPreset').mockResolvedValue({});
  vi.spyOn(energyCostApi, 'getMeasurementBounds').mockRejectedValue(new Error('Unavailable'));
  const calculate = vi.spyOn(energyCostApi, 'calculate').mockResolvedValue(calculation());
  sessionStorage.setItem('energy-cost-range:3', JSON.stringify(range));
  const wrapper = mount(Dashboard, {
    props: {channel: {id: 3}, plan, assignedPlanId: 10},
    global: {
      plugins: [pinia, createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})],
      stubs: {
        LoadingCover: {template: '<div><slot/></div>'},
        EnergyCostRangeSelector: true,
        EnergyCostComparison: true,
        EnergyCostSummary: true,
        EnergyCostChart: true,
        EnergyCostHeatmap: true,
        EnergyCostBreakdown: true,
        EnergyCostDetails: true,
      },
    },
  });
  await flushPromises();
  return {wrapper, calculate};
}
describe('shared Costs views', () => {
  it('defaults to report, preserves the range across switches and GETs the assigned historical plan', async () => {
    const {wrapper, calculate} = await dashboard();
    expect(wrapper.findComponent(Comparison).exists()).toBe(false);
    expect(calculate).toHaveBeenCalledWith(3, Date.parse(range.from) / 1000, Date.parse(range.to) / 1000);
    const buttons = wrapper.get('.energy-cost-view-selector').findAll('button');
    await buttons[1].trigger('click');
    expect(wrapper.findComponent(Comparison).props('plan')).toEqual(plan);
    expect(wrapper.findComponent(Comparison).props('range')).toEqual(range);
    const next = {...range, to: '2026-09-01T03:00:00Z'};
    wrapper.findComponent(RangeSelector).vm.$emit('update:modelValue', next);
    await flushPromises();
    expect(calculate).toHaveBeenLastCalledWith(3, Date.parse(next.from) / 1000, Date.parse(next.to) / 1000);
    await buttons[0].trigger('click');
    expect(wrapper.findComponent(RangeSelector).props('modelValue')).toEqual(next);
    await buttons[1].trigger('click');
    expect(wrapper.findComponent(Comparison).props('range')).toEqual(next);
    wrapper.unmount();
  });
  it('returns to report and refreshes the assigned calculation after successful assignment', async () => {
    const {wrapper, calculate} = await dashboard();
    await wrapper.get('.energy-cost-view-selector').findAll('button')[1].trigger('click');
    await wrapper.setProps({plan: {...plan, id: 11}, assignedPlanId: 11});
    wrapper.findComponent(Comparison).vm.$emit('applied');
    await flushPromises();
    expect(wrapper.findComponent(Comparison).exists()).toBe(false);
    expect(calculate).toHaveBeenCalledTimes(2);
    expect(wrapper.findComponent(RangeSelector).props('modelValue')).toEqual(range);
    wrapper.unmount();
  });
});
