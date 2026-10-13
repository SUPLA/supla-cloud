import {describe, expect, it, vi} from 'vitest';
import {mount} from '@vue/test-utils';
import {createI18n} from 'vue-i18n';
import ApexCharts from 'apexcharts';
import Chart from '@/channels/energy-cost/energy-cost-comparison-chart.vue';

vi.mock('apexcharts', () => ({
  default: vi.fn(
    class {
      render() {}
      destroy() {}
    }
  ),
}));
describe('cost-only comparison chart', () => {
  it('renders exactly two gross cost series on a single currency axis, preserving null buckets', async () => {
    const wrapper = mount(Chart, {
      props: {
        buckets: [{from: '2026-09-01T00:00:00Z', current: '10', alternative: null}],
        currency: 'EUR',
        timezone: 'Europe/Warsaw',
        alternativeLabel: 'TAURON Dystrybucja G11',
      },
      global: {plugins: [createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})]},
    });
    const options = ApexCharts.mock.calls[0][1];
    expect(options.chart.type).toBe('bar');
    expect(options.series.map((series) => series.name)).toEqual(['Current plan', 'TAURON Dystrybucja G11']);
    expect(options.series[1].data[0].y).toBeNull();
    expect(Array.isArray(options.yaxis)).toBe(false);
    expect(options.yaxis.labels.formatter(10)).toBe('10.00 EUR');
    await wrapper.setProps({alternativeLabel: 'ENEA Operator G12'});
    expect(ApexCharts.mock.calls.at(-1)[1].series[1].name).toBe('ENEA Operator G12');
    wrapper.unmount();
  });
});
