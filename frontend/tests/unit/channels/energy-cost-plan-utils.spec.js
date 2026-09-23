import {describe, expect, it} from 'vitest';
import {
  dateFromDatetime,
  dateFromPeriodEnd,
  dateToDatetime,
  dateToPeriodEnd,
  fromDatetimeLocal,
  isTime,
  normalizeDecimal,
  presetDefault,
  readJsonPointer,
  serializeConfiguration,
  simulationDefaultValues,
  toDatetimeLocal,
} from '@/channels/energy-cost/energy-cost-plan-utils';

describe('energy cost plan utilities', () => {
  it('reads escaped JSON Pointer tokens and array indexes', () => {
    expect(readJsonPointer({'a/b': [{'c~d': 'value'}]}, '/a~1b/0/c~0d')).toBe('value');
  });

  it('resolves defaults without putting them into plan values', () => {
    const preset = {document: {billingDefinitionTemplate: {rates: {DAY: '0.2841'}}}};
    const input = {id: 'distribution.DAY', targets: ['/rates/DAY']};
    const configuration = {entries: [{presetId: 'preset', values: {}}]};

    expect(presetDefault(preset, input)).toBe('0.2841');
    expect(serializeConfiguration(configuration).entries[0].values).not.toHaveProperty('distribution.DAY');
  });

  it('preserves an explicit override equal to the preset default', () => {
    const configuration = {entries: [{presetId: 'preset', values: {'distribution.DAY': '0.2841'}}]};

    expect(serializeConfiguration(configuration).entries[0].values).toEqual({'distribution.DAY': '0.2841'});
  });

  it('uses declared simulation defaults to prefill a new plan draft', () => {
    const preset = {
      document: {
        inputs: [{id: 'billingCycle.anchor'}, {id: 'energy.rate'}],
        simulationDefaults: {
          values: {
            'billingCycle.anchor': '2026-01-01T00:00:00+01:00',
            'energy.rate': '0.5020',
            obsolete: 'ignored',
          },
        },
      },
    };

    expect(simulationDefaultValues(preset)).toEqual({
      'billingCycle.anchor': '2026-01-01T00:00:00+01:00',
      'energy.rate': '0.5020',
    });
  });

  it('normalizes Polish decimals and accepts calculator time values', () => {
    expect(normalizeDecimal(' 0,71 ')).toBe('0.71');
    expect(isTime('24:00')).toBe(true);
    expect(isTime('24:01')).toBe(false);
  });

  it('serializes local datetimes using the preset timezone offset', () => {
    const value = fromDatetimeLocal('2026-01-15T00:00', 'Europe/Warsaw');

    expect(value).toBe('2026-01-15T00:00:00+01:00');
    expect(toDatetimeLocal(value, 'Europe/Warsaw')).toBe('2026-01-15T00:00');
  });

  it('converts period boundaries between a date and timezone midnight', () => {
    const boundary = dateToDatetime('2026-07-01', 'Europe/Warsaw');

    expect(boundary).toBe('2026-07-01T00:00:00+02:00');
    expect(dateFromDatetime(boundary, 'Europe/Warsaw')).toBe('2026-07-01');
  });

  it('shows inclusive period end dates while keeping exclusive boundaries', () => {
    const boundary = dateToPeriodEnd('2026-07-01', 'Europe/Warsaw');

    expect(boundary).toBe('2026-07-02T00:00:00+02:00');
    expect(dateFromPeriodEnd(boundary, 'Europe/Warsaw')).toBe('2026-07-01');
  });

  it('preserves historical entries and boundaries during serialization', () => {
    const configuration = {
      entries: [
        {presetId: 'first', validFrom: '2026-01-01T00:00:00+01:00', values: {rate: '1'}},
        {presetId: 'later', validTo: '2027-01-01T00:00:00+01:00', values: {rate: '2'}},
      ],
    };

    expect(serializeConfiguration(configuration)).toEqual({
      version: 1,
      entries: [
        {presetId: 'first', validFrom: '2026-01-01T00:00:00+01:00', values: {rate: '1'}},
        {presetId: 'later', validTo: '2027-01-01T00:00:00+01:00', values: {rate: '2'}},
      ],
    });
  });
});
