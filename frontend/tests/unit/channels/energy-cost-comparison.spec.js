import {afterEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {energyCostApi} from '@/api/energy-cost-api';
import Comparison from '@/channels/energy-cost/energy-cost-comparison.vue';
import Picker from '@/channels/energy-cost/energy-cost-tariff-picker.vue';
import ComparisonChart from '@/channels/energy-cost/energy-cost-comparison-chart.vue';
import Summary from '@/channels/energy-cost/energy-cost-summary.vue';
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
  const suggestion = {
    cheapestId: 'g12w',
    tariffs: [
      {id: 'g11', total: '100', currency: 'PLN', eligible: true, fixedChargesComplete: true},
      {id: 'g12w', total: '80', currency: 'PLN', eligible: true, fixedChargesComplete: false},
    ],
  };
  it('selects the suggested tariff and retains dropdown costs across manual tariff selection', async () => {
    const suggest = vi.spyOn(energyCostApi, 'suggestTariff').mockResolvedValue(suggestion);
    const {wrapper, select, simulate} = await setup();
    await wrapper.get('.energy-cost-suggest').trigger('click');
    await flushPromises();
    expect(suggest).toHaveBeenCalledWith(3, Math.floor(Date.parse(range.from) / 1000), Math.floor(Date.parse(range.to) / 1000), plan.configuration);
    expect(wrapper.emitted('update:selectedTariffId').at(-1)).toEqual(['g12w']);
    await wrapper.setProps({selectedTariffId: 'g12w'});
    await flushPromises();
    expect(simulate).toHaveBeenCalledOnce();
    const options = () =>
      wrapper
        .findComponent(Picker)
        .findAll('option')
        .map((option) => option.text());
    expect(options()).toContain('Operator G11 — 100.00 PLN');
    expect(options()).toContain('Operator G12w — 80.00 PLN');
    await select('g11');
    expect(options()).toContain('Operator G12w — 80.00 PLN');
    await wrapper.setProps({range: {...range, to: '2026-09-03T00:00:00+02:00'}});
    expect(options().every((option) => !option.includes('PLN'))).toBe(true);
  });
  it('disables suggestions beyond 31 days and accepts exactly 31 days', async () => {
    const suggest = vi.spyOn(energyCostApi, 'suggestTariff').mockResolvedValue(suggestion);
    const {wrapper} = await setup();
    expect(wrapper.get('.energy-cost-alternative-header .energy-cost-suggest').text()).toBe('Find the cheapest');
    expect(wrapper.get('.energy-cost-suggest fa-stub').attributes('icon')).toBe('wand-magic-sparkles');
    const from = '2026-07-01T00:00:00Z';
    const to = new Date(Date.parse(from) + 31 * 86400 * 1000).toISOString();
    await wrapper.setProps({range: {from, to}});
    expect(wrapper.get('.energy-cost-suggest').element.disabled).toBe(false);
    await wrapper.setProps({range: {from, to: new Date(Date.parse(to) + 1000).toISOString()}});
    expect(wrapper.get('.energy-cost-suggest').element.disabled).toBe(true);
    expect(wrapper.get('.energy-cost-suggest-control small').text()).toBe('Available only for periods of up to 31 days.');
    await wrapper.get('.energy-cost-suggest').trigger('click');
    expect(suggest).not.toHaveBeenCalled();
  });
  it.each([
    ['2026-10-04T22:00:00Z', '2026-11-04T23:00:00Z'],
    ['2026-03-04T23:00:00Z', '2026-04-04T22:00:00Z'],
  ])('allows 31 calendar days across DST from %s to %s', async (from, to) => {
    const {wrapper} = await setup();
    await wrapper.setProps({range: {from, to}});
    expect(wrapper.get('.energy-cost-suggest').element.disabled).toBe(false);
    await wrapper.setProps({range: {from, to: new Date(Date.parse(to) + 1000).toISOString()}});
    expect(wrapper.get('.energy-cost-suggest').element.disabled).toBe(true);
  });
  it.each(['range', 'channel', 'plan'])('ignores stale suggestions after %s changes', async (context) => {
    let resolve;
    vi.spyOn(energyCostApi, 'suggestTariff').mockReturnValue(new Promise((done) => (resolve = done)));
    const {wrapper} = await setup();
    await wrapper.get('.energy-cost-suggest').trigger('click');
    expect(wrapper.get('.energy-cost-suggest').element.disabled).toBe(true);
    const changes = {
      range: {...range, to: '2026-09-03T00:00:00+02:00'},
      channel: {id: 4},
      plan: {...plan, configuration: {...plan.configuration, timezone: 'UTC'}},
    };
    await wrapper.setProps({[context]: changes[context]});
    resolve(suggestion);
    await flushPromises();
    expect(wrapper.emitted('update:selectedTariffId')).toBeUndefined();
    expect(wrapper.findComponent(Picker).props('costLabels')).toEqual({});
    expect(wrapper.get('.energy-cost-suggest').element.disabled).toBe(false);
  });
  it('shows unavailable costs without selecting a tariff when none are eligible', async () => {
    vi.spyOn(energyCostApi, 'suggestTariff').mockResolvedValue({cheapestId: null, tariffs: [{id: 'g11', total: null, eligible: false}]});
    const {wrapper} = await setup();
    await wrapper.get('.energy-cost-suggest').trigger('click');
    await flushPromises();
    expect(wrapper.findComponent(Picker).text()).toContain('Operator G11 — Cost unavailable');
    expect(wrapper.text()).toContain('No tariff can be reliably compared for the selected period.');
    expect(wrapper.emitted('update:selectedTariffId')).toBeUndefined();
  });
  it('shows a request error and allows retrying suggestions', async () => {
    const suggest = vi.spyOn(energyCostApi, 'suggestTariff').mockRejectedValueOnce(new Error('Unavailable')).mockResolvedValueOnce(suggestion);
    const {wrapper} = await setup();
    await wrapper.get('.energy-cost-suggest').trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('Could not suggest a tariff for the selected period.');
    expect(wrapper.get('.energy-cost-suggest').element.disabled).toBe(false);
    await wrapper.get('.energy-cost-suggest').trigger('click');
    await flushPromises();
    expect(suggest).toHaveBeenCalledTimes(2);
    expect(wrapper.text()).not.toContain('Could not suggest a tariff for the selected period.');
  });
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
  it('displays usage-based costs when fixed charges are incomplete, even when reported totals differ', async () => {
    const baseline = calculation('100');
    baseline.costs.gross.total = '120';
    baseline.costs.gross.periodic.total = '20';
    baseline.reportSummary = {costs: {gross: {total: '999', periodic: {total: '20'}}}, fixedChargesComplete: false};
    const {wrapper, select, simulate} = await setup(baseline);
    const currentPlan = () => wrapper.get('.col-sm-6 section');
    simulate.mockResolvedValue({...calculation('80'), costs: {gross: {usageBased: {total: '80'}, periodic: {total: '10'}, total: '90'}}});
    await select();
    expect(wrapper.text()).toContain('Savings: 20.00 PLN');
    expect(currentPlan().get('strong').text()).toBe('100.00 PLN');
    expect(currentPlan().get('small').text()).toBe('Usage-based cost (gross)');
    const alternativePlan = wrapper.findAll('.energy-cost-scenario')[1];
    expect(alternativePlan.get('strong').text()).toBe('80.00 PLN');
    expect(alternativePlan.get('small').text()).toBe('Usage-based cost (gross)');
    expect(wrapper.text()).toContain('Fixed charges are not included.');
  });
  it('displays full gross costs when both complete billing results are comparable', async () => {
    const baseline = calculation('100', '50');
    const {wrapper, select, simulate} = await setup(baseline);
    simulate.mockResolvedValue(calculation('80', '50'));
    await select();
    expect(wrapper.findAll('.energy-cost-scenario strong').map((amount) => amount.text())).toEqual(['150.00 PLN', '130.00 PLN']);
    expect(wrapper.findAll('.energy-cost-scenario small').map((subtitle) => subtitle.text())).toEqual([
      'Total gross cost, including fixed charges',
      'Total gross cost, including fixed charges',
    ]);
    expect(wrapper.text()).toContain('Savings: 20.00 PLN');
  });
  it('does not show primary comparison amounts or savings when results are not comparable', async () => {
    const {wrapper, select, simulate} = await setup();
    const alternative = calculation('80');
    alternative.currency = 'EUR';
    simulate.mockResolvedValue(alternative);
    await select();
    expect(wrapper.text()).toContain('These tariffs use different currencies and cannot be compared.');
    expect(wrapper.text()).not.toContain('Savings:');
    expect(wrapper.findAll('.energy-cost-scenario-price')).toHaveLength(0);
  });
  it('displays tariff warnings in the failed-calculation comparison notice', async () => {
    const baseline = calculation();
    baseline.warnings = [{code: 'UNSUPPORTED_RATE', from: range.from, to: range.to, message: 'Current tariff warning'}];
    const {wrapper, select, simulate} = await setup(baseline);
    const alternative = calculation('80');
    alternative.warnings = [{code: 'UNSUPPORTED_RATE', from: range.from, to: range.to, message: 'Alternative tariff warning'}];
    simulate.mockResolvedValue(alternative);
    await select();
    const notice = wrapper.get('.alert-info');
    expect(notice.text()).toContain('Some logs could not be calculated. Results cannot be directly compared.');
    expect(notice.text()).toContain('Current tariff warning');
    expect(notice.text()).toContain('Alternative tariff warning');
    expect(wrapper.findAll('.energy-cost-comparison > details')).toHaveLength(0);
    expect(wrapper.findComponent(Summary).props('result')).toStrictEqual(baseline);
    await wrapper.get('[role="tablist"] li:nth-child(2) a').trigger('click');
    expect(wrapper.findComponent(Summary).props('result')).toStrictEqual(alternative);
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
    expect(wrapper.text()).not.toContain('100.00 PLN');
    await wrapper.setProps({baseline: null, baselineError: 'Baseline unavailable'});
    expect(wrapper.text()).toContain('Baseline unavailable');
  });
});
