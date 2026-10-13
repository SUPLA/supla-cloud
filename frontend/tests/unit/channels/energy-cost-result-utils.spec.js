import {describe, expect, it} from 'vitest';
import {
  addDecimals,
  aggregateCharges,
  aggregateChargesByWeekdayAndHour,
  aggregateEnergy,
  aggregateImportedEnergyByWeekdayAndHour,
  alignedFixedChargeRange,
  allowsHourlyAggregation,
  formatDecimal,
  grossCostLabel,
  preferredGranularity,
  selectedRangeLabel,
  summaryFromResult,
  taxBreakdown,
} from '@/channels/energy-cost/energy-cost-result-utils';

describe('energy cost result utilities', () => {
  it('aligns only incomplete periods with omitted observed fixed charges, preserving unobserved selection edges', () => {
    const range = {from: '2026-01-10T00:00:00+01:00', to: '2026-10-09T00:00:00+02:00'};
    const result = {
      billingPeriods: [
        {from: '2026-01-01T00:00:00+01:00', to: '2026-02-01T00:00:00+01:00', fullyCovered: false, costs: {gross: {periodic: {total: '0'}}}},
        {from: '2026-09-01T00:00:00+02:00', to: '2026-10-01T00:00:00+02:00', fullyCovered: true, costs: {gross: {periodic: {total: '12.3'}}}},
        {from: '2026-10-01T00:00:00+02:00', to: '2026-11-01T00:00:00+01:00', fullyCovered: false, costs: {gross: {periodic: {total: null}}}},
      ],
    };
    expect(alignedFixedChargeRange(result, range)).toEqual({from: range.from, to: '2026-11-01T00:00:00+01:00'});
    result.billingPeriods[2].costs.gross.periodic.total = '0';
    expect(alignedFixedChargeRange(result, range)).toBeNull();
  });
  it.each([
    ['0', false, 'Gross cost, without fixed charges'],
    ['0.000', true, 'Gross cost, without fixed charges'],
    ['12.30', false, 'Gross cost, including some fixed charges'],
    ['12.30', true, 'Gross cost, including fixed charges'],
    ['0.00000000000000000001', false, 'Gross cost, including some fixed charges'],
  ])('labels included fixed charges %s with completeness %s', (total, fixedChargesComplete, label) => {
    expect(grossCostLabel({reportSummary: {costs: {gross: {periodic: {total}}}, fixedChargesComplete}})).toBe(label);
  });
  it('does not claim fixed charges are included when their amount is unavailable', () => {
    expect(grossCostLabel(null)).toBe('Gross cost');
    expect(grossCostLabel({costs: {gross: {periodic: {total: null}}}})).toBe('Gross cost');
  });
  it.each([
    ['2026-02-01T00:00:00+01:00', '2026-03-01T00:00:00+01:00', true],
    ['2026-03-01T00:00:00+01:00', '2026-04-01T00:00:00+02:00', true],
    ['2026-10-01T00:00:00+02:00', '2026-11-01T00:00:00+01:00', true],
    ['2026-10-01T00:00:00+02:00', '2026-11-01T00:00:01+01:00', false],
    ['2026-01-15T00:00:00+01:00', '2026-02-15T00:00:00+01:00', true],
    ['2026-01-15T00:00:00+01:00', '2026-03-15T00:00:00+01:00', false],
  ])('allows hourly aggregation from %s to %s: %s', (from, to, allowed) => {
    expect(allowsHourlyAggregation(from, to, 'Europe/Warsaw')).toBe(allowed);
  });
  it('keeps decimal aggregation exact', () => {
    expect(addDecimals('0.1', '0.2')).toBe('0.3');
    expect(addDecimals('1.005', '2.995')).toBe('4');
  });

  it('formats values to two decimals without hiding non-zero values', () => {
    expect(formatDecimal('12.345')).toBe('12.35');
    expect(formatDecimal('0')).toBe('0.00');
    expect(formatDecimal('0.004')).toBe('0.004');
    expect(formatDecimal('-0.0006')).toBe('-0.0006');
    expect(formatDecimal(NaN)).toBe('');
  });

  it('extracts usage-based summary values when fixed charges are indeterminate', () => {
    expect(
      summaryFromResult({
        costs: {
          gross: {total: null, usageBased: {total: '12.34'}, periodic: {total: null}},
          net: {usageBased: {total: '10'}},
          taxes: {total: null, byTax: {VAT: '2.1', EXCISE: '0.24'}},
        },
        usage: {},
      })
    ).toMatchObject({
      usageBased: '12.34',
      fixedCharges: null,
      taxes: null,
    });
  });

  it('aggregates atomic charges in the plan timezone', () => {
    const buckets = aggregateCharges(
      [
        {from: '2026-03-29T00:30:00+01:00', componentId: 'energy', selection: 'DAY', amounts: {gross: '0.1', net: '0.08', taxTotal: '0.02'}},
        {from: '2026-03-29T03:30:00+02:00', componentId: 'energy', selection: 'NIGHT', amounts: {gross: '0.2', net: '0.16', taxTotal: '0.04'}},
      ],
      'day',
      'Europe/Warsaw'
    );
    expect(buckets).toHaveLength(1);
    expect(buckets[0].gross).toBe('0.3');
    expect(buckets[0].byZone).toEqual({DAY: '0.1', NIGHT: '0.2'});
  });

  it('uses included report fixed charges and individual taxes without recomputing them', () => {
    const result = {
      currency: 'PLN',
      costs: {gross: {total: null, periodic: {total: null}}, taxes: {total: null, byTax: {VAT: '23'}}},
      reportSummary: {
        fixedChargesComplete: false,
        costs: {gross: {total: '135.3', usageBased: {total: '123'}, periodic: {total: '12.3'}}, taxes: {total: '25.3', byTax: {VAT: '25.3'}}},
      },
    };
    expect(summaryFromResult(result)).toMatchObject({usageBased: '123', fixedCharges: '12.3', taxes: '25.3'});
    expect(taxBreakdown(result)).toEqual([{id: 'VAT', amount: '25.3'}]);
  });

  it('groups hourly, daily and monthly charges without spreading periodic costs', () => {
    const charges = [
      {from: '2026-01-01T10:15:00+01:00', componentId: 'purchase', amounts: {gross: '1', net: '0.8', taxTotal: '0.2'}},
      {from: '2026-01-01T10:45:00+01:00', componentId: 'distribution', amounts: {gross: '2', net: '1.6', taxTotal: '0.4'}},
      {from: '2026-02-01T10:45:00+01:00', componentId: 'purchase', amounts: {gross: '3', net: '2.4', taxTotal: '0.6'}},
    ];
    expect(aggregateCharges(charges, 'hour', 'Europe/Warsaw')).toHaveLength(2);
    expect(aggregateCharges(charges, 'day', 'Europe/Warsaw')[0].byComponent).toEqual({purchase: '1', distribution: '2'});
    expect(aggregateCharges(charges, 'month', 'Europe/Warsaw')).toHaveLength(2);
    expect(aggregateCharges([], 'day', 'Europe/Warsaw')).toEqual([]);
  });

  it('aggregates gross charges by local weekday and hour', () => {
    const buckets = aggregateChargesByWeekdayAndHour(
      [
        {from: '2026-03-29T00:30:00+01:00', amounts: {gross: '0.1'}},
        {from: '2026-03-29T03:30:00+02:00', amounts: {gross: '0.2'}},
      ],
      'Europe/Warsaw'
    );
    expect(buckets[6].available).toBe(true);
    expect(buckets[0].available).toBe(false);
    expect(buckets[6].hours[0]).toBe('0.1');
    expect(buckets[6].hours[3]).toBe('0.2');
  });

  it('aggregates imported energy by local weekday and hour', () => {
    const buckets = aggregateImportedEnergyByWeekdayAndHour([{from: '2026-03-29T03:30:00+02:00', usage: {ACTIVE_ENERGY_IMPORT: '1.25'}}], 'Europe/Warsaw');
    expect(buckets[6].available).toBe(true);
    expect(buckets[0].available).toBe(false);
    expect(buckets[6].hours[3]).toBe('1.25');
  });

  it('aggregates imported and exported energy by the selected granularity', () => {
    const buckets = aggregateEnergy(
      [
        {from: '2026-03-29T00:30:00+01:00', usage: {ACTIVE_ENERGY_IMPORT: '0.1', ACTIVE_ENERGY_EXPORT: '0.02'}},
        {from: '2026-03-29T03:30:00+02:00', usage: {ACTIVE_ENERGY_IMPORT: '0.2', ACTIVE_ENERGY_EXPORT: '0.03'}},
      ],
      'day',
      'Europe/Warsaw'
    );
    expect(buckets).toEqual([{from: '2026-03-29T00:00:00.000+01:00', imported: '0.3', exported: '0.05'}]);
  });

  it('selects the documented automatic aggregation', () => {
    expect(preferredGranularity('2026-01-01T00:00:00+01:00', '2026-01-02T00:00:00+01:00', 'Europe/Warsaw')).toBe('hour');
    expect(preferredGranularity('2026-01-01T00:00:00+01:00', '2026-06-01T00:00:00+02:00', 'Europe/Warsaw')).toBe('month');
  });
  it('labels date-only selections with an inclusive end across DST and localizes dates', () => {
    const range = {from: '2026-09-30T22:00:00Z', to: '2026-10-31T23:00:00Z'};
    expect(selectedRangeLabel(range, 'Europe/Warsaw')).toBe('01 Oct 2026 – 31 Oct 2026');
    expect(selectedRangeLabel(range, 'Europe/Warsaw', 'pl')).toBe('01 paź 2026 – 31 paź 2026');
  });
});
