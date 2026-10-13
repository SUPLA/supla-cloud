export const range = {from: '2026-09-01T00:00:00Z', to: '2026-09-01T02:00:00Z'};
export function monthlyCalculation(rate = '1.25', periodic = '10') {
  const result = calculation(String(Number(rate) * 720), periodic);
  result.range = {from: '2026-08-31T22:00:00Z', to: '2026-09-30T22:00:00Z'};
  result.billingCycle = {anchor: '2026-01-01', length: 1, unit: 'MONTH'};
  result.billingContext.periods = [{...result.range}];
  result.billingPeriods = [{...result.range, fullyCovered: true, costs: result.costs}];
  result.processedDeltaCount = 2880;
  result.usage.ACTIVE_ENERGY_IMPORT = '720';
  result.intervals = Array.from({length: 2880}, (_, index) => ({
    from: new Date(Date.parse(result.range.from) + index * 900000).toISOString(),
    to: new Date(Date.parse(result.range.from) + (index + 1) * 900000).toISOString(),
    usage: {ACTIVE_ENERGY_IMPORT: '0.25', ACTIVE_ENERGY_EXPORT: '0'},
  }));
  result.charges = result.intervals.map((interval) => ({
    from: interval.from,
    to: interval.to,
    componentId: 'energy',
    selection: 'ALL',
    amounts: {gross: String(Number(rate) / 4)},
  }));
  return result;
}
export function calculation(usage = '100', periodic = null) {
  const full = periodic !== null;
  return {
    currency: 'PLN',
    range: {...range},
    processedDeltaCount: 2,
    incomplete: false,
    warnings: [],
    usage: {ACTIVE_ENERGY_IMPORT: '2', ACTIVE_ENERGY_EXPORT: '0'},
    billingContext: {requestedRangeCoversWholePeriods: full, periods: [{from: range.from, to: range.to}]},
    billingPeriods: [{...range, fullyCovered: full, costs: {gross: {periodic: {total: periodic}}}}],
    costs: {
      gross: {usageBased: {total: usage, byZone: {ALL: usage}}, periodic: {total: periodic}, total: full ? String(Number(usage) + Number(periodic)) : null},
    },
    intervals: [
      {from: range.from, to: '2026-09-01T01:00:00Z', usage: {ACTIVE_ENERGY_IMPORT: '1', ACTIVE_ENERGY_EXPORT: '0'}},
      {from: '2026-09-01T01:00:00Z', to: range.to, usage: {ACTIVE_ENERGY_IMPORT: '1', ACTIVE_ENERGY_EXPORT: '0'}},
    ],
    charges: [
      {from: range.from, to: '2026-09-01T01:00:00Z', componentId: 'energy', selection: 'ALL', amounts: {gross: String(Number(usage) / 2)}},
      {from: '2026-09-01T01:00:00Z', to: range.to, componentId: 'energy', selection: 'ALL', amounts: {gross: String(Number(usage) / 2)}},
    ],
  };
}
export const starters = [
  {id: 'g11', label: 'Operator G11', components: [{kind: 'ENERGY_PURCHASE', componentId: 'energy', presetId: 'g11', values: {}}]},
  {id: 'g12w', label: 'Operator G12w', components: [{kind: 'ENERGY_PURCHASE', componentId: 'energy', presetId: 'g12w', values: {}}]},
];
export const plan = {
  id: 10,
  configuration: {
    version: 2,
    currency: 'PLN',
    timezone: 'Europe/Warsaw',
    taxContext: {jurisdiction: 'PL', customerClass: 'HOUSEHOLD'},
    billingCycles: [{anchor: '2026-01-15', length: 1, unit: 'MONTH'}],
    periods: [
      {validFrom: null, validTo: '2026-04-30T22:00:00Z', components: starters[0].components},
      {validFrom: '2026-04-30T22:00:00Z', validTo: null, components: [{...starters[1].components[0], values: {price: '999'}}]},
    ],
  },
};
