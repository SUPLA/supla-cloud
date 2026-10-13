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
async function dashboard(result = calculation()) {
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
  const calculate = vi.spyOn(energyCostApi, 'calculate').mockResolvedValue(result);
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
  it.each(['report', 'comparison'])('places the fixed-charge notice directly below the range selector in %s and aligns the shared range', async (view) => {
    const {wrapper, calculate} = await dashboard();
    if (view === 'comparison') await wrapper.get('.energy-cost-view-selector').findAll('button')[1].trigger('click');
    const notice = wrapper.get('.energy-cost-billing-period-warning');
    expect(wrapper.findComponent(RangeSelector).element.nextElementSibling).toBe(notice.element);
    expect(notice.text()).toContain('Fixed charges are not included in the total.');
    await notice.get('button').trigger('click');
    await flushPromises();
    const aligned = wrapper.findComponent(RangeSelector).props('modelValue');
    expect(Date.parse(aligned.from)).toBe(Date.parse('2026-07-14T22:00:00Z'));
    expect(Date.parse(aligned.to)).toBe(Date.parse('2026-08-14T22:00:00Z'));
    expect(calculate).toHaveBeenLastCalledWith(3, Date.parse(aligned.from) / 1000, Date.parse(aligned.to) / 1000);
    if (view === 'comparison') expect(wrapper.findComponent(Comparison).props('range')).toEqual(aligned);
    wrapper.unmount();
  });
  it('shows the shared notice when only the alternative has unavailable fixed charges', async () => {
    const {wrapper} = await dashboard(calculation('100', '10'));
    expect(wrapper.find('.energy-cost-billing-period-warning').exists()).toBe(false);
    const buttons = wrapper.get('.energy-cost-view-selector').findAll('button');
    await buttons[1].trigger('click');
    wrapper.findComponent(Comparison).vm.$emit('update:periodicCostsUnavailable', true);
    await flushPromises();
    expect(wrapper.find('.energy-cost-billing-period-warning').exists()).toBe(true);
    wrapper.findComponent(Comparison).vm.$emit('update:periodicCostsUnavailable', false);
    await flushPromises();
    expect(wrapper.find('.energy-cost-billing-period-warning').exists()).toBe(false);
    wrapper.findComponent(Comparison).vm.$emit('update:periodicCostsUnavailable', true);
    await buttons[0].trigger('click');
    expect(wrapper.find('.energy-cost-billing-period-warning').exists()).toBe(false);
    wrapper.unmount();
  });
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
