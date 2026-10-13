import {afterEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {energyCostApi} from '@/api/energy-cost-api';
import Comparison from '@/channels/energy-cost/energy-cost-comparison.vue';
import Picker from '@/channels/energy-cost/energy-cost-tariff-picker.vue';
import ComparisonChart from '@/channels/energy-cost/energy-cost-comparison-chart.vue';
import {calculation, monthlyCalculation, plan, range, starters} from './energy-cost-comparison-fixtures';

afterEach(() => vi.restoreAllMocks());
async function setup(baseline = {...calculation(), reportSummary: {costs: {gross: {total: '100'}}}}) {
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
  const i18n = createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false, fallbackFormat: true});
  const wrapper = mount(Comparison, {
    props: {channel: {id: 3}, plan, range, baseline},
    global: {
      plugins: [pinia, i18n],
      mocks: {$t: i18n.global.t},
      stubs: {EnergyCostComparisonChart: true, EnergyCostComparisonDetails: true},
    },
  });
  await flushPromises();
  const select = async (id = 'g12w') => {
    wrapper.findComponent(Picker).vm.$emit('update:modelValue', id);
    await wrapper.setProps({selectedTariffId: id});
    await flushPromises();
  };
  return {wrapper, store, simulate, assign, select};
}
describe('tariff comparison interactions', () => {
  it('shows report details for the selected current or alternative tab without recalculating', async () => {
    const baseline = calculation('100', '10');
    const alternative = calculation('80', '20');
    const {wrapper, select, simulate} = await setup(baseline);
    simulate.mockResolvedValue(alternative);
    await select();
    const tabs = wrapper.get('.energy-cost-comparison-report').findAll('[role="tab"]');
    expect(tabs.map((tab) => tab.text())).toEqual(['Current plan', 'Operator G12w']);
    const details = () => wrapper.findComponent({name: 'EnergyCostComparisonDetails'});
    expect(details().props('result')).toEqual(baseline);
    await tabs[1].trigger('click');
    expect(details().props('result')).toEqual(alternative);
    expect(details().props('timezone')).toBe('Europe/Warsaw');
    await tabs[0].trigger('click');
    expect(details().props('result')).toEqual(baseline);
    expect(simulate).toHaveBeenCalledOnce();
  });
  it.each([
    ['80', 'The Operator G12w is cheaper.'],
    ['120', 'The Operator G12w is more expensive.'],
    ['100', 'Operator G12w costs the same as the current plan.'],
  ])('uses the selected tariff name in simulation texts and chart labels for cost %s', async (cost, verdict) => {
    const {wrapper, select, simulate} = await setup();
    simulate.mockResolvedValue(calculation(cost));
    await select();
    expect(wrapper.text()).toContain(verdict);
    expect(
      wrapper
        .get('thead')
        .findAll('th')
        .map((header) => header.text())
    ).toEqual(['', 'Current plan', 'Operator G12w']);
    expect(wrapper.findComponent(ComparisonChart).props('alternativeLabel')).toBe('Operator G12w');
    await select('g11');
    expect(wrapper.findComponent(ComparisonChart).props('alternativeLabel')).toBe('Operator G11');
    expect(wrapper.get('thead').text()).not.toContain('Operator G12w');
  });
  it('updates comparison chart buckets when hourly, daily or monthly aggregation is selected', async () => {
    const {wrapper, select} = await setup();
    await select();
    const aggregation = wrapper.get('.form-inline select');
    expect(aggregation.element.value).toBe('hour');
    expect(wrapper.findComponent(ComparisonChart).props('buckets')).toHaveLength(2);
    for (const granularity of ['day', 'month']) {
      await aggregation.setValue(granularity);
      expect(wrapper.findComponent(ComparisonChart).props('buckets')).toEqual([{from: '2026-08-31T22:00:00.000Z', current: '100', alternative: '80'}]);
    }
    await aggregation.setValue('hour');
    expect(wrapper.findComponent(ComparisonChart).props('buckets')).toHaveLength(2);
  });
  it('allows hourly comparison for a full month and falls back when the selected range grows beyond one month', async () => {
    const availableLogs = (result) => ({...result, intervals: result.intervals.slice(0, 96), charges: result.charges.slice(0, 96), processedDeltaCount: 96});
    const baseline = availableLogs(monthlyCalculation());
    const alternative = availableLogs(monthlyCalculation('1'));
    const {wrapper, select, simulate} = await setup(baseline);
    simulate.mockResolvedValue(alternative);
    await wrapper.setProps({range: baseline.range});
    await select();
    const aggregation = () => wrapper.get('.form-inline select');
    expect(
      aggregation()
        .findAll('option')
        .map((option) => option.element.value)
    ).toEqual(['hour', 'day', 'month']);
    await aggregation().setValue('hour');
    expect(wrapper.findComponent(ComparisonChart).props('buckets')).toHaveLength(24);
    const extendedRange = {...baseline.range, to: '2026-10-31T23:00:00Z'};
    simulate.mockResolvedValue({...alternative, range: extendedRange});
    await wrapper.setProps({range: extendedRange, baseline: {...baseline, range: extendedRange}});
    await flushPromises();
    expect(
      aggregation()
        .findAll('option')
        .map((option) => option.element.value)
    ).toEqual(['day', 'month']);
    expect(aggregation().element.value).toBe('day');
    expect(wrapper.findComponent(ComparisonChart).props('buckets')).toHaveLength(1);
  });
  it.each([null, '10'])('keeps the Cost Report total before, during and after tariff selection with periodic costs %s', async (periodic) => {
    const baseline = calculation('100', periodic);
    baseline.reportSummary = {costs: {gross: {total: '112', periodic: {total: periodic ?? '0'}}}, fixedChargesComplete: periodic !== null};
    const {wrapper, select, simulate} = await setup(baseline);
    const currentPlan = () => wrapper.get('.col-sm-6 section');
    expect(currentPlan().get('strong').text()).toBe('112.00 PLN');
    const subtitle = periodic === null ? 'Gross cost, without fixed charges' : 'Gross cost, including fixed charges';
    expect(currentPlan().get('small').text()).toBe(subtitle);
    let resolve;
    simulate.mockReturnValueOnce(
      new Promise((done) => {
        resolve = done;
      })
    );
    await select();
    expect(currentPlan().get('strong').text()).toBe('112.00 PLN');
    resolve({
      ...calculation('80', periodic),
      reportSummary: {costs: {gross: {total: '102', periodic: {total: periodic ?? '0'}}}, fixedChargesComplete: periodic !== null},
    });
    await flushPromises();
    expect(wrapper.text()).toContain('Savings:');
    expect(currentPlan().get('strong').text()).toBe('112.00 PLN');
    expect(currentPlan().get('small').text()).toBe(subtitle);
    const alternativePlan = wrapper.findAll('.energy-cost-scenario')[1];
    expect(alternativePlan.get('strong').text()).toBe('102.00 PLN');
    expect(alternativePlan.get('small').text()).toBe(currentPlan().get('small').text());
    await select('');
    expect(currentPlan().get('strong').text()).toBe('112.00 PLN');
  });
  it('labels partial and zero fixed charges independently in the two simulation boxes', async () => {
    const baseline = calculation();
    baseline.reportSummary = {costs: {gross: {total: '112', periodic: {total: '12'}}}, fixedChargesComplete: false};
    const {wrapper, select, simulate} = await setup(baseline);
    simulate.mockResolvedValue({
      ...calculation('80'),
      reportSummary: {costs: {gross: {total: '80', periodic: {total: '0'}}}, fixedChargesComplete: false},
    });
    await select();
    expect(wrapper.findAll('.energy-cost-scenario small').map((subtitle) => subtitle.text())).toEqual([
      'Gross cost, including some fixed charges',
      'Gross cost, without fixed charges',
    ]);
  });
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
    expect(wrapper.text()).not.toContain('Apply this tariff to the meter');
    expect(store.assignmentsByChannelId[3].planId).toBe(10);
    expect(wrapper.text()).toContain('Savings: 20.00 PLN');
    expect(wrapper.emitted('update:periodicCostsUnavailable').at(-1)).toEqual([true]);
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
    expect(wrapper.emitted('update:periodicCostsUnavailable').at(-1)).toEqual([false]);
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
