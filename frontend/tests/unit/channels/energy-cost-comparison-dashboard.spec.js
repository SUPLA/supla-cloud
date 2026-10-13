import {afterEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import Dashboard from '@/channels/energy-cost/energy-cost-dashboard.vue';
import Comparison from '@/channels/energy-cost/energy-cost-comparison.vue';
import Picker from '@/channels/energy-cost/energy-cost-tariff-picker.vue';
import RangeSelector from '@/channels/energy-cost/energy-cost-range-selector.vue';
import Summary from '@/channels/energy-cost/energy-cost-summary.vue';
import {energyCostApi} from '@/api/energy-cost-api';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {calculation, plan, range, starters} from './energy-cost-comparison-fixtures';

const router = vi.hoisted(() => ({query: {}, replace: vi.fn()}));
vi.mock('vue-router', () => ({useRoute: () => ({query: router.query}), useRouter: () => ({replace: router.replace})}));
afterEach(() => {
  vi.restoreAllMocks();
  vi.unstubAllGlobals();
  sessionStorage.clear();
  Object.keys(router.query).forEach((key) => delete router.query[key]);
  router.replace.mockClear();
});
async function dashboard(result = calculation(), {bounds, selectedRange = range, realRangeSelector = false, realComparison = false, costPlan = plan} = {}) {
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
  if (realComparison) {
    const store = useEnergyCostStore();
    store.tariffs = starters;
    store.tariffDetailsById = Object.fromEntries(starters.map((starter) => [starter.id, starter]));
    vi.spyOn(store, 'fetchTariffs').mockResolvedValue(starters);
  }
  const measurementBounds = vi.spyOn(energyCostApi, 'getMeasurementBounds');
  if (bounds) measurementBounds.mockResolvedValue(bounds);
  else measurementBounds.mockRejectedValue(new Error('Unavailable'));
  const calculate = vi.spyOn(energyCostApi, 'calculate').mockResolvedValue(result);
  const simulate = vi.spyOn(energyCostApi, 'simulate').mockResolvedValue(calculation('80'));
  if (selectedRange) sessionStorage.setItem('energy-cost-range:3', JSON.stringify(selectedRange));
  const wrapper = mount(Dashboard, {
    props: {channel: {id: 3}, plan: costPlan, assignedPlanId: 10},
    global: {
      plugins: [pinia, createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})],
      stubs: {
        LoadingCover: {template: '<div><slot/></div>'},
        EnergyCostRangeSelector: !realRangeSelector,
        EnergyCostComparison: !realComparison,
        EnergyCostComparisonChart: true,
        EnergyCostComparisonDetails: true,
        EnergyCostSummary: false,
        EnergyCostChart: true,
        EnergyCostHeatmap: true,
        EnergyCostBreakdown: true,
        EnergyCostDetails: true,
      },
    },
  });
  await flushPromises();
  return {wrapper, calculate, simulate};
}
describe('shared Costs views', () => {
  it('restores comparison mode and automatically simulates the tariff selected in the URL', async () => {
    Object.assign(router.query, {energyCostView: 'comparison', energyCostTariff: 'g12w'});
    const {wrapper, simulate} = await dashboard(calculation(), {realComparison: true});
    expect(wrapper.findComponent(Comparison).exists()).toBe(true);
    expect(wrapper.findComponent(Comparison).props('selectedTariffId')).toBe('g12w');
    expect(simulate).toHaveBeenCalledExactlyOnceWith(
      3,
      Date.parse(range.from) / 1000,
      Date.parse(range.to) / 1000,
      expect.objectContaining({periods: [{validFrom: null, validTo: null, components: starters[1].components}]})
    );
    wrapper.findComponent(Picker).vm.$emit('update:modelValue', 'g11');
    await flushPromises();
    expect(wrapper.findComponent(Comparison).props('selectedTariffId')).toBe('g11');
    expect(router.replace).toHaveBeenLastCalledWith({query: expect.objectContaining({energyCostView: 'comparison', energyCostTariff: 'g11'})});
    expect(simulate).toHaveBeenLastCalledWith(
      3,
      Date.parse(range.from) / 1000,
      Date.parse(range.to) / 1000,
      expect.objectContaining({periods: [{validFrom: null, validTo: null, components: starters[0].components}]})
    );
    wrapper.unmount();
  });
  it('synchronizes view and tariff selection with the URL while preserving unrelated parameters', async () => {
    Object.assign(router.query, {tab: 'costs', energyCostFrom: '2026-10-01', energyCostTo: '2026-10-31'});
    const {wrapper} = await dashboard();
    const buttons = wrapper.get('.energy-cost-view-selector').findAll('button');
    await buttons[1].trigger('click');
    expect(router.replace).toHaveBeenLastCalledWith({query: {...router.query, energyCostView: 'comparison'}});
    wrapper.findComponent(Comparison).vm.$emit('update:selectedTariffId', 'g12w');
    await flushPromises();
    expect(router.replace).toHaveBeenLastCalledWith({query: {...router.query, energyCostView: 'comparison', energyCostTariff: 'g12w'}});
    await buttons[0].trigger('click');
    expect(router.replace).toHaveBeenLastCalledWith({query: {...router.query, energyCostView: 'report', energyCostTariff: 'g12w'}});
    await buttons[1].trigger('click');
    expect(wrapper.findComponent(Comparison).props('selectedTariffId')).toBe('g12w');
    wrapper.findComponent(Comparison).vm.$emit('update:selectedTariffId', '');
    await flushPromises();
    expect(router.replace).toHaveBeenLastCalledWith({query: {...router.query, energyCostView: 'comparison'}});
    wrapper.unmount();
  });
  it('allows hourly report aggregation for a full month but hides it for longer ranges', async () => {
    const month = {from: '2026-10-01T00:00:00+02:00', to: '2026-11-01T00:00:00+01:00'};
    const {wrapper} = await dashboard(calculation(), {selectedRange: month});
    const aggregation = () => wrapper.get('.energy-cost-print-chart select');
    expect(
      aggregation()
        .findAll('option')
        .map((option) => option.element.value)
    ).toEqual(['hour', 'day', 'month']);
    await aggregation().setValue('hour');
    wrapper.findComponent(RangeSelector).vm.$emit('update:modelValue', {...month, to: '2026-12-01T00:00:00+01:00'});
    await flushPromises();
    expect(
      aggregation()
        .findAll('option')
        .map((option) => option.element.value)
    ).toEqual(['day', 'month']);
    expect(aggregation().element.value).toBe('day');
    wrapper.unmount();
  });
  it.each([
    ['2026-01-15', 1, '2026-09-15T00:00:00+02:00', '2026-10-15T00:00:00+02:00'],
    ['2026-10-01', 1, '2026-10-01T00:00:00+02:00', '2026-11-01T00:00:00+01:00'],
    ['2026-08-15', 2, '2026-08-15T00:00:00+02:00', '2026-10-15T00:00:00+02:00'],
  ])('defaults to the full current billing cycle anchored at %s with length %s', async (anchor, length, from, to) => {
    vi.spyOn(Date, 'now').mockReturnValue(Date.parse('2026-10-13T12:00:00+02:00'));
    const costPlan = {...plan, configuration: {...plan.configuration, billingCycles: [{anchor, length, unit: 'MONTH'}]}};
    const {wrapper, calculate} = await dashboard(calculation(), {selectedRange: null, costPlan});
    const selected = wrapper.findComponent(RangeSelector).props('modelValue');
    expect(Date.parse(selected.from)).toBe(Date.parse(from));
    expect(Date.parse(selected.to)).toBe(Date.parse(to));
    expect(calculate).toHaveBeenCalledExactlyOnceWith(3, Date.parse(from) / 1000, Date.parse(to) / 1000);
    wrapper.unmount();
  });
  it('places the comparison notice below the selector and aligns using effective result boundaries', async () => {
    const result = calculation();
    result.billingPeriods = [{from: '2026-08-15T00:00:00+02:00', to: '2026-09-15T00:00:00+02:00', fullyCovered: false}];
    const {wrapper, calculate} = await dashboard(result);
    await wrapper.get('.energy-cost-view-selector').findAll('button')[1].trigger('click');
    const notice = wrapper.get('.energy-cost-billing-period-warning');
    expect(wrapper.findComponent(RangeSelector).element.nextElementSibling).toBe(notice.element);
    expect(notice.text()).toContain('Some fixed charges are not included');
    await notice.get('button').trigger('click');
    await flushPromises();
    const aligned = wrapper.findComponent(RangeSelector).props('modelValue');
    expect(Date.parse(aligned.from)).toBe(Date.parse('2026-08-14T22:00:00Z'));
    expect(Date.parse(aligned.to)).toBe(Date.parse('2026-09-14T22:00:00Z'));
    expect(calculate).toHaveBeenLastCalledWith(3, Date.parse(aligned.from) / 1000, Date.parse(aligned.to) / 1000);
    expect(wrapper.findComponent(Comparison).props('range')).toEqual(aligned);
    wrapper.unmount();
  });
  it('expands an ongoing fragment beyond measurements and synchronizes picker, URL, storage and recalculation', async () => {
    const selectedRange = {from: '2026-10-01T00:00:00+02:00', to: '2026-10-14T00:00:00+02:00'};
    const full = {from: '2026-10-01T00:00:00+02:00', to: '2026-11-01T00:00:00+01:00'};
    const result = calculation();
    result.range = selectedRange;
    result.billingPeriods = [{...full, fullyCovered: false}];
    result.reportSummary = {costs: {gross: {total: '100', periodic: {total: '0'}}, taxes: {total: '18.7'}}, fixedChargesComplete: false};
    const {wrapper, calculate} = await dashboard(result, {
      selectedRange,
      realRangeSelector: true,
      bounds: {oldest: {date_timestamp: Date.parse(selectedRange.from) / 1000}, newest: {date_timestamp: Date.parse('2026-10-13T12:00:00+02:00') / 1000}},
    });
    expect(wrapper.find('.energy-cost-billing-period-warning').exists()).toBe(false);
    expect(wrapper.text()).toContain('Usage costs are based only on available measurements');
    expect(calculate).toHaveBeenLastCalledWith(3, Date.parse(selectedRange.from) / 1000, Date.parse(selectedRange.to) / 1000);
    const expanded = {
      ...result,
      range: full,
      billingPeriods: [{...full, fullyCovered: true}],
      reportSummary: {costs: {gross: {total: '112.3', periodic: {total: '12.3'}}, taxes: {total: '21'}}, fixedChargesComplete: true},
    };
    calculate.mockResolvedValue(expanded);
    await wrapper.findComponent(Summary).get('button').trigger('click');
    await flushPromises();
    expect(calculate).toHaveBeenLastCalledWith(3, Date.parse(full.from) / 1000, Date.parse(full.to) / 1000);
    expect(wrapper.findComponent(RangeSelector).props('modelValue')).toEqual(full);
    expect(
      wrapper
        .findComponent(RangeSelector)
        .findAll('input')
        .map((input) => input.element.value)
    ).toEqual(['2026-10-01', '2026-10-31']);
    expect(JSON.parse(sessionStorage.getItem('energy-cost-range:3'))).toEqual(full);
    expect(router.replace).toHaveBeenLastCalledWith({query: {energyCostFrom: '2026-10-01', energyCostTo: '2026-10-31', energyCostView: 'report'}});
    expect(wrapper.findComponent(Summary).get('strong').text()).toBe('112.30 PLN');
    expect(wrapper.findComponent(Summary).text()).toContain('01 Oct 2026 – 31 Oct 2026');
    expect(wrapper.find('.energy-cost-total-alignment-hint').exists()).toBe(false);
    wrapper.unmount();
  });
  it('aligns partial edges across multiple effective periods without truncating either boundary', async () => {
    const result = calculation();
    result.billingPeriods = [
      {from: '2026-08-01T00:00:00+02:00', to: '2026-09-01T00:00:00+02:00', fullyCovered: false},
      {from: '2026-09-01T00:00:00+02:00', to: '2026-10-01T00:00:00+02:00', fullyCovered: true},
      {from: '2026-10-01T00:00:00+02:00', to: '2026-11-01T00:00:00+01:00', fullyCovered: false},
    ];
    result.reportSummary = {costs: {gross: {total: '112.3', periodic: {total: '12.3'}}}, fixedChargesComplete: false};
    const {wrapper, calculate} = await dashboard(result);
    expect(wrapper.findComponent(Summary).get('button').text()).toBe('Align');
    await wrapper.findComponent(Summary).get('button').trigger('click');
    await flushPromises();
    expect(calculate).toHaveBeenLastCalledWith(3, Date.parse(result.billingPeriods[0].from) / 1000, Date.parse(result.billingPeriods[2].to) / 1000);
    wrapper.unmount();
  });
  it('preserves an explicitly selected entire month from inclusive date-only URL parameters', async () => {
    Object.assign(router.query, {energyCostFrom: '2026-10-01', energyCostTo: '2026-10-31'});
    const {wrapper, calculate} = await dashboard(calculation(), {
      bounds: {
        oldest: {date_timestamp: Date.parse('2026-10-01T00:00:00+02:00') / 1000},
        newest: {date_timestamp: Date.parse('2026-10-13T12:00:00+02:00') / 1000},
      },
    });
    expect(calculate).toHaveBeenLastCalledWith(3, Date.parse('2026-10-01T00:00:00+02:00') / 1000, Date.parse('2026-11-01T00:00:00+01:00') / 1000);
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
  it('presents missing measurements separately from skipped-charge errors and fixed-charge completeness', async () => {
    const result = calculation('100', '10');
    result.reportSummary = {costs: result.costs, fixedChargesComplete: true};
    result.incomplete = true;
    result.warnings = [{code: 'MISSING_METER_DATA', scope: 'METER_INTERVAL', from: range.from, to: range.to}];
    const {wrapper} = await dashboard(result);
    expect(wrapper.text()).toContain('Usage costs are based only on available measurements');
    expect(wrapper.find('.alert-warning').exists()).toBe(false);
    expect(wrapper.find('.energy-cost-total-alignment-hint').exists()).toBe(false);
    wrapper.unmount();
    const skipped = {
      ...result,
      warnings: [{code: 'MISSING_REFERENCE_DATA', scope: 'METER_INTERVAL', from: range.from, to: range.to, message: 'Missing price'}],
    };
    const next = await dashboard(skipped);
    expect(next.wrapper.get('.alert-warning').text()).toContain('Some logs could not be calculated');
    expect(next.wrapper.find('.energy-cost-total-alignment-hint').exists()).toBe(false);
    next.wrapper.unmount();
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
  it('refreshes changed assigned plans while preserving comparison view and range', async () => {
    const {wrapper, calculate} = await dashboard();
    await wrapper.get('.energy-cost-view-selector').findAll('button')[1].trigger('click');
    await wrapper.setProps({plan: {...plan, id: 11}, assignedPlanId: 11});
    await flushPromises();
    expect(wrapper.findComponent(Comparison).exists()).toBe(true);
    expect(calculate).toHaveBeenCalledTimes(2);
    expect(wrapper.findComponent(RangeSelector).props('modelValue')).toEqual(range);
    wrapper.unmount();
  });
});
