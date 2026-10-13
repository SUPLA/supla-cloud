import {afterEach, describe, expect, it, vi} from 'vitest';
import {calculation} from './energy-cost-comparison-fixtures';

afterEach(() => vi.unstubAllGlobals());

describe('cost chart worker', () => {
  it.each([false, true])('aggregates the same monetary and energy heatmaps with heatmapsOnly=%s', async (heatmapsOnly) => {
    vi.resetModules();
    const postMessage = vi.fn();
    vi.stubGlobal('self', {postMessage});
    await import('@/channels/energy-cost/energy-cost-chart.worker');
    const result = calculation('100', '10');
    self.onmessage({data: {charges: result.charges, intervals: result.intervals, timezone: 'Europe/Warsaw', granularity: 'day', request: 7, heatmapsOnly}});
    const data = postMessage.mock.calls[0][0];
    expect(data.request).toBe(7);
    expect(data.heatmapBuckets[1].hours[2]).toBe('50');
    expect(data.heatmapBuckets[1].hours[3]).toBe('50');
    expect(data.heatmapEnergyBuckets[1].hours[2]).toBe('1');
    expect(data.heatmapEnergyBuckets[1].hours[3]).toBe('1');
    if (heatmapsOnly) expect(data.buckets).toBeUndefined();
    else expect(data.buckets[0].gross).toBe('100');
  });
});
