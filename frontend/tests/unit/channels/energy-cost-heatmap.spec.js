import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {nextTick} from 'vue';
import {createI18n} from 'vue-i18n';
import ApexCharts from 'apexcharts';
import Heatmap from '@/channels/energy-cost/energy-cost-heatmap.vue';

vi.mock('apexcharts', () => ({
  default: vi.fn(
    class {
      render = vi.fn().mockResolvedValue();
      destroy = vi.fn();
    }
  ),
}));

let frames;
beforeEach(() => {
  vi.clearAllMocks();
  frames = [];
  vi.stubGlobal('requestAnimationFrame', (callback) => frames.push(callback));
  vi.stubGlobal('matchMedia', () => ({matches: false, addEventListener: vi.fn(), removeEventListener: vi.fn()}));
});
afterEach(() => vi.unstubAllGlobals());

function heatmap() {
  const hours = Array(24).fill('0');
  hours[9] = '12';
  const imported = Array(24).fill('0');
  imported[9] = '3';
  return mount(Heatmap, {
    props: {buckets: [{weekday: 1, available: true, hours}], energyBuckets: [{weekday: 1, available: true, hours: imported}], currency: 'PLN', metric: 'cost'},
    global: {plugins: [createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})]},
  });
}
async function renderFrame() {
  await nextTick();
  frames.splice(0).forEach((callback) => callback(0));
  await flushPromises();
}

describe('heatmap rendering lifecycle', () => {
  it('renders preloaded tab data on initial mount without a metric change', async () => {
    const wrapper = heatmap();
    await renderFrame();
    expect(ApexCharts).toHaveBeenCalledOnce();
    expect(ApexCharts.mock.calls[0][1].series[0].data[9]).toEqual({x: '09', y: 12});
    expect(wrapper.emitted('rendering').at(-1)).toEqual([false]);
    wrapper.unmount();
  });
  it('redraws when a heatmap mode changes after the initial render', async () => {
    const wrapper = heatmap();
    await renderFrame();
    await wrapper.setProps({metric: 'costPerKwh'});
    await renderFrame();
    expect(ApexCharts).toHaveBeenCalledTimes(2);
    expect(ApexCharts.mock.calls[1][1].series[0].data[9].y).toBe(4);
    wrapper.unmount();
  });
  it('does not create a delayed chart after its tab has been unmounted', async () => {
    const wrapper = heatmap();
    await nextTick();
    wrapper.unmount();
    await renderFrame();
    expect(ApexCharts).not.toHaveBeenCalled();
  });
});
