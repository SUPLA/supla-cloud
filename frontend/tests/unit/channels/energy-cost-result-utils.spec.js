import {describe, expect, it} from 'vitest';
import {
  addDecimals,
  aggregateCharges,
  aggregateChargesByWeekdayAndHour,
  aggregateEnergy,
  aggregateImportedEnergyByWeekdayAndHour,
  formatDecimal,
  preferredGranularity,
  summaryFromResult,
} from '@/channels/energy-cost/energy-cost-result-utils';

describe('energy cost result utilities', () => {
  it('keeps decimal aggregation exact', () => {
    expect(addDecimals('0.1', '0.2')).toBe('0.3');
    expect(addDecimals('1.005', '2.995')).toBe('4');
  });

  it('formats values to two decimals without hiding non-zero values', () => {
    expect(formatDecimal('12.345')).toBe('12.35');
    expect(formatDecimal('0')).toBe('0.00');
    expect(formatDecimal('0.004')).toBe('0.004');
    expect(formatDecimal('-0.0006')).toBe('-0.0006');
  });

  it('extracts usage-based summary values when fixed charges are indeterminate', () => {
    expect(
      summaryFromResult({
        costs: {
          gross: {total: null, usageBased: {total: '12.34'}, periodic: {total: null}},
          net: {usageBased: {total: '10'}},
          taxes: {byTax: {VAT: '2.1', EXCISE: '0.24'}},
        },
        usage: {},
      })
    ).toMatchObject({
      usageBased: '12.34',
      usageBasedNet: '10',
      usageBasedTaxes: '2.34',
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
});
