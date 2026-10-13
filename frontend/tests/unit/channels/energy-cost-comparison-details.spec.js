import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {mount} from '@vue/test-utils';
import {nextTick} from 'vue';
import {createI18n} from 'vue-i18n';
import ComparisonDetails from '@/channels/energy-cost/energy-cost-comparison-details.vue';
import Heatmap from '@/channels/energy-cost/energy-cost-heatmap.vue';
import Donut from '@/channels/energy-cost/energy-cost-donut.vue';
import {aggregateChargesByWeekdayAndHour, aggregateImportedEnergyByWeekdayAndHour} from '@/channels/energy-cost/energy-cost-result-utils';
import {calculation} from './energy-cost-comparison-fixtures';

let worker;
beforeEach(() => {
  vi.stubGlobal(
    'Worker',
    class {
      constructor() {
        worker = this;
      }
      postMessage = vi.fn();
      terminate = vi.fn();
    }
  );
});
afterEach(() => vi.unstubAllGlobals());

function result(usage = '100', fixed = '10') {
  const result = calculation(usage, fixed);
  result.costs.gross.usageBased.byComponent = {energy: usage};
  result.costs.taxes = {byTax: {VAT: '20'}, total: '20'};
  result.billingPeriods[0] = {...result.billingPeriods[0], usage: result.usage, costs: result.costs};
  result.periodicCharges = [
    {
      id: 'subscription',
      appliesFrom: result.range.from,
      appliesTo: result.range.to,
      definition: {rate: fixed, unit: 'MONTH'},
      calculated: {units: '1', amounts: {net: fixed, gross: fixed, taxTotal: '0'}},
    },
  ];
  return result;
}
function mountDetails(result) {
  return mount(ComparisonDetails, {
    props: {result, timezone: 'Europe/Warsaw'},
    global: {
      plugins: [createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})],
      stubs: {EnergyCostHeatmap: true, EnergyCostDonut: true},
    },
  });
}
async function respond(result, request = worker.postMessage.mock.calls.at(-1)[0].request) {
  worker.onmessage({
    data: {
      request,
      heatmapBuckets: aggregateChargesByWeekdayAndHour(result.charges, 'Europe/Warsaw'),
      heatmapEnergyBuckets: aggregateImportedEnergyByWeekdayAndHour(result.intervals, 'Europe/Warsaw'),
    },
  });
  await nextTick();
}

describe('simulation report details', () => {
  it('reuses report heatmap controls, all donut breakdowns, fixed charges and billing periods', async () => {
    const baseline = result();
    const wrapper = mountDetails(baseline);
    expect(worker.postMessage).toHaveBeenCalledWith({
      charges: baseline.charges,
      intervals: baseline.intervals,
      timezone: 'Europe/Warsaw',
      request: 1,
      heatmapsOnly: true,
    });
    await respond(baseline);
    expect(wrapper.findComponent(Heatmap).props('buckets')).toEqual(aggregateChargesByWeekdayAndHour(baseline.charges, 'Europe/Warsaw'));
    const buttons = wrapper.get('.energy-cost-interactive-heatmap').findAll('button');
    await buttons[1].trigger('click');
    expect(wrapper.findComponent(Heatmap).props('metric')).toBe('costPerKwh');
    await buttons[2].trigger('click');
    expect(wrapper.findComponent(Heatmap).props('metric')).toBe('usage');
    expect(wrapper.findAllComponents(Donut).map((donut) => donut.props('items'))).toEqual([
      [{id: 'energy', amount: '100', label: 'energy'}],
      [{id: 'ALL', amount: '100', label: 'ALL'}],
      [{id: 'VAT', amount: '20', label: 'VAT'}],
    ]);
    const tables = wrapper.get('.energy-cost-details').findAll('table');
    expect(tables).toHaveLength(2);
    expect(tables[0].text()).toContain('subscription');
    expect(tables[0].text()).toContain('10.00 PLN');
    expect(tables[1].text()).toContain('110.00 PLN');
    wrapper.unmount();
    expect(worker.terminate).toHaveBeenCalledOnce();
  });

  it('uses the new result for every detail and ignores obsolete heatmap responses after switching plans', async () => {
    const baseline = result();
    const alternative = result('80', '20');
    const wrapper = mountDetails(baseline);
    await wrapper.setProps({result: alternative});
    await respond(baseline, 1);
    expect(wrapper.findComponent(Heatmap).exists()).toBe(false);
    await respond(alternative, 2);
    expect(wrapper.findComponent(Heatmap).props('buckets')).toEqual(aggregateChargesByWeekdayAndHour(alternative.charges, 'Europe/Warsaw'));
    expect(wrapper.findAllComponents(Donut)[0].props('items')[0].amount).toBe('80');
    const tables = wrapper.get('.energy-cost-details').findAll('table');
    expect(tables[0].text()).toContain('20.00 PLN');
    expect(tables[1].text()).toContain('100.00 PLN');
    expect(tables[1].text()).not.toContain('110.00 PLN');
    wrapper.unmount();
  });
});
