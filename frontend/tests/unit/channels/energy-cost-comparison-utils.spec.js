import {describe, expect, it} from 'vitest';
import {compareCalculations, comparisonBuckets, comparisonConfiguration} from '@/channels/energy-cost/energy-cost-comparison-utils';
import {calculation, monthlyCalculation, plan, range, starters} from './energy-cost-comparison-fixtures';

describe('financial comparison', () => {
  it.each([
    ['80', '20', 20],
    ['120', '-20', -20],
    ['100', '0', 0],
  ])('calculates savings for %s', (alternative, savings, percentage) => {
    expect(compareCalculations(calculation(), calculation(alternative), range)).toMatchObject({comparable: true, full: false, savings, percentage});
  });
  it('does not divide by a zero baseline', () => {
    expect(compareCalculations(calculation('0'), calculation('10'), range)).toMatchObject({savings: '-10', percentage: null});
  });
  it('uses full gross totals only for two complete billing results', () => {
    const current = monthlyCalculation();
    expect(compareCalculations(current, monthlyCalculation('1', '200'), current.range)).toMatchObject({full: true, savings: '-10'});
  });
  it.each([null, undefined])('does not treat missing periodic costs as zero: %s', (missing) => {
    const alternative = calculation('80', '30');
    alternative.costs.gross.periodic.total = missing;
    expect(compareCalculations(calculation('100', '10'), alternative, range)).toMatchObject({full: false, savings: '20'});
  });
  it('falls back when a billing period is partial', () => {
    const alternative = calculation('80', '30');
    alternative.billingPeriods[0].fullyCovered = false;
    expect(compareCalculations(calculation('100', '10'), alternative, range).full).toBe(false);
  });
  it.each(['incomplete', 'empty', 'differentRange', 'shortenedBoth', 'gap', 'differentReadings', 'currency', 'missingAmount'])(
    'rejects unsafe comparisons: %s',
    (problem) => {
      const current = calculation();
      const alternative = calculation('80');
      if (problem === 'incomplete') alternative.incomplete = true;
      if (problem === 'empty') alternative.processedDeltaCount = 0;
      if (problem === 'differentRange') alternative.range.to = '2026-09-01T01:00:00Z';
      if (problem === 'shortenedBoth') current.range.to = alternative.range.to = '2026-09-01T01:00:00Z';
      if (problem === 'gap') alternative.intervals.pop();
      if (problem === 'differentReadings') alternative.intervals[0].usage.ACTIVE_ENERGY_IMPORT = '2';
      if (problem === 'currency') alternative.currency = 'EUR';
      if (problem === 'missingAmount') alternative.costs.gross.usageBased.total = null;
      expect(compareCalculations(current, alternative, range).comparable).toBe(false);
    }
  );
  it('compares financial outcomes independently of zone structures', () => {
    const alternative = calculation('80');
    alternative.costs.gross.usageBased.byZone = {DAY: '50', NIGHT: '30'};
    alternative.charges[0].selection = 'DAY';
    alternative.charges[1].selection = 'NIGHT';
    expect(compareCalculations(calculation(), alternative, range).savings).toBe('20');
  });
  it('aligns buckets by timestamps and preserves absent values as unknown', () => {
    const alternative = calculation('80');
    alternative.charges.shift();
    expect(comparisonBuckets(calculation(), alternative, 'hour', 'UTC')).toEqual([
      {from: range.from.replace('Z', '.000Z'), current: '50', alternative: null},
      {from: '2026-09-01T01:00:00.000Z', current: '50', alternative: '40'},
    ]);
  });
  it('builds a single whole-history starter using catalog values and shared context', () => {
    const configuration = comparisonConfiguration(starters[1], plan.configuration);
    expect(configuration).toMatchObject({
      currency: 'PLN',
      timezone: 'Europe/Warsaw',
      taxContext: plan.configuration.taxContext,
      billingCycles: plan.configuration.billingCycles,
    });
    expect(configuration.periods).toEqual([{validFrom: null, validTo: null, components: starters[1].components}]);
    configuration.periods[0].components[0].values.price = '1';
    expect(starters[1].components[0].values).toEqual({});
    expect(plan.configuration.periods[1].components[0].values.price).toBe('999');
  });
});
