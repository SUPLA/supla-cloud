import {aggregateCharges, aggregateChargesByWeekdayAndHour, aggregateEnergy, aggregateImportedEnergyByWeekdayAndHour} from './energy-cost-result-utils';

self.onmessage = ({data: {charges, intervals, granularity, timezone, request}}) => {
  const granularities = ['hour', 'day', 'month'];
  let effectiveGranularity = granularity;
  let buckets;
  let energyBuckets;
  for (let granularityIndex = granularities.indexOf(granularity); ; granularityIndex += 1) {
    buckets = aggregateCharges(charges, effectiveGranularity, timezone);
    energyBuckets = aggregateEnergy(intervals, effectiveGranularity, timezone);
    if (Math.max(buckets.length, energyBuckets.length) <= 750 || effectiveGranularity === 'month') break;
    effectiveGranularity = granularities[granularityIndex + 1];
  }
  self.postMessage({
    request,
    buckets,
    energyBuckets,
    heatmapBuckets: aggregateChargesByWeekdayAndHour(charges, timezone),
    heatmapEnergyBuckets: aggregateImportedEnergyByWeekdayAndHour(intervals, timezone),
    granularity: effectiveGranularity,
  });
};
