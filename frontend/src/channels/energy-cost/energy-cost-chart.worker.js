import {aggregateCharges, aggregateEnergy} from './energy-cost-result-utils';

self.onmessage = ({data: {charges, intervals, granularity, timezone, request}}) => {
  const granularities = ['hour', 'day', 'month'];
  let effectiveGranularity = granularity;
  let buckets;
  let energyBuckets;
  do {
    buckets = aggregateCharges(charges, effectiveGranularity, timezone);
    energyBuckets = aggregateEnergy(intervals, effectiveGranularity, timezone);
    if (Math.max(buckets.length, energyBuckets.length) <= 750 || effectiveGranularity === 'month') break;
    effectiveGranularity = granularities[granularities.indexOf(effectiveGranularity) + 1];
  } while (true);
  self.postMessage({
    request,
    buckets,
    energyBuckets,
    granularity: effectiveGranularity,
  });
};
