import {describe, expect, it} from 'vitest';
import {compareCalculations, comparisonBuckets, comparisonConfiguration} from '@/channels/energy-cost/energy-cost-comparison-utils';
import {calculation, monthlyCalculation, plan, range, starters} from './energy-cost-comparison-fixtures';

describe('financial comparison', () => {
  it.each([
    ['80', '20', 20],
    ['120', '-20', -20],
    ['100', '0', 0],
  ])('calculates savings for %s', (alternative, savings, percentage) => {
    expect(compareCalculations(calculation(), calculation(alternative), range)).toMatchObject({
      comparable: true,
      full: false,
      costBasis: 'usageBasedGross',
      savings,
      percentage,
    });
  });
  it('does not divide by a zero baseline', () => {
    expect(compareCalculations(calculation('0'), calculation('10'), range)).toMatchObject({savings: '-10', percentage: null});
  });
  it('uses full gross totals only for two complete billing results', () => {
    const current = monthlyCalculation();
    expect(compareCalculations(current, monthlyCalculation('1', '200'), current.range)).toMatchObject({
      full: true,
      costBasis: 'fullGross',
      savings: '-10',
    });
  });
  it.each([null, undefined])('does not treat missing periodic costs as zero: %s', (missing) => {
    const alternative = calculation('80', '30');
    alternative.costs.gross.periodic.total = missing;
    expect(compareCalculations(calculation('100', '10'), alternative, range)).toMatchObject({full: false, savings: '20'});
  });
  it('uses usage-based amounts when only one result has complete fixed charges', () => {
    const alternative = calculation('80');
    alternative.costs.gross.periodic.total = '10';
    alternative.costs.gross.total = '90';
    expect(compareCalculations(calculation('100', '20'), alternative, range)).toMatchObject({
      comparable: true,
      costBasis: 'usageBasedGross',
      current: '100',
      alternative: '80',
      savings: '20',
    });
  });
  it('ignores partial totals and report summaries when comparing incomplete fixed charges', () => {
    const current = calculation('100');
    const alternative = calculation('80');
    current.costs.gross.periodic.total = '20';
    current.costs.gross.total = '120';
    alternative.costs.gross.periodic.total = '10';
    alternative.costs.gross.total = '90';
    current.reportSummary = {costs: {gross: {total: '999'}}, fixedChargesComplete: false};
    alternative.reportSummary = {costs: {gross: {total: '1'}}, fixedChargesComplete: false};
    expect(compareCalculations(current, alternative, range)).toMatchObject({
      comparable: true,
      costBasis: 'usageBasedGross',
      current: '100',
      alternative: '80',
      savings: '20',
    });
  });
  it('falls back when a billing period is partial', () => {
    const alternative = calculation('80', '30');
    alternative.billingPeriods[0].fullyCovered = false;
    expect(compareCalculations(calculation('100', '10'), alternative, range).full).toBe(false);
  });
  it('compares a full current month using identical available logs before the month ends', () => {
    const current = monthlyCalculation();
    const alternative = monthlyCalculation('1', '200');
    for (const result of [current, alternative]) {
      result.intervals = result.intervals.slice(0, 100);
      result.charges = result.charges.slice(0, 100);
      result.processedDeltaCount = 100;
      result.costs.gross.usageBased.total = result === current ? '100' : '80';
      result.costs.gross.total = result === current ? '110' : '280';
    }
    expect(compareCalculations(current, alternative, current.range)).toMatchObject({comparable: true, full: true, savings: '-170'});
  });
  it('compares identical available logs when both results have leading, internal and trailing gaps', () => {
    const current = monthlyCalculation();
    const alternative = monthlyCalculation('1');
    for (const result of [current, alternative]) {
      result.intervals = [result.intervals[10], result.intervals[20]];
      result.processedDeltaCount = 2;
      result.warnings = [{code: 'MISSING_METER_DATA'}];
      result.incomplete = true;
    }
    expect(compareCalculations(current, alternative, current.range)).toMatchObject({comparable: true, full: true});
  });
  it('still rejects charge calculation failures alongside missing measurements', () => {
    const alternative = calculation('80');
    alternative.warnings = [{code: 'MISSING_METER_DATA'}, {code: 'MISSING_PRICE'}];
    alternative.incomplete = true;
    expect(compareCalculations(calculation(), alternative, range)).toMatchObject({
      comparable: false,
      reason: 'Some logs could not be calculated. Results cannot be directly compared.',
    });
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
