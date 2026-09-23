import {describe, expect, it} from 'vitest';
import {
  dateFromDatetime,
  dateFromPeriodEnd,
  dateToDatetime,
  dateToPeriodEnd,
  fromDatetimeLocal,
  inputsForComponent,
  isTime,
  normalizeDecimal,
  presetDefault,
  readJsonPointer,
  serializeConfiguration,
  toDatetimeLocal,
} from '@/channels/energy-cost/energy-cost-plan-utils';

describe('energy cost plan utilities', () => {
  it('reads escaped JSON Pointer tokens and array indexes', () => {
    expect(readJsonPointer({'a/b': [{'c~d': 'value'}]}, '/a~1b/0/c~0d')).toBe('value');
  });

  it('resolves defaults without putting them into plan values', () => {
    const preset = {document: {billingDefinitionTemplate: {rates: {DAY: '0.2841'}}}};
    const input = {id: 'distribution.DAY', targets: ['/rates/DAY']};
    const configuration = {
      currency: 'PLN',
      timezone: 'Europe/Warsaw',
      priceBasis: 'NET',
      billingCycles: [],
      periods: [{components: [{presetId: 'preset', values: {}}]}],
    };

    expect(presetDefault(preset, input)).toBe('0.2841');
    expect(serializeConfiguration(configuration).periods[0].components[0].values).not.toHaveProperty('distribution.DAY');
  });

  it('preserves an explicit override equal to the preset default', () => {
    const configuration = {
      currency: 'PLN',
      timezone: 'Europe/Warsaw',
      priceBasis: 'NET',
      billingCycles: [],
      periods: [{components: [{presetId: 'preset', values: {'distribution.DAY': '0.2841'}}]}],
    };

    expect(serializeConfiguration(configuration).periods[0].components[0].values).toEqual({'distribution.DAY': '0.2841'});
  });

  it('returns only inputs targeting a component without rewriting their pointers', () => {
    const preset = {
      document: {
        inputs: [
          {id: 'first', targets: ['/components/0/values/rate']},
          {id: 'second', targets: ['/components/1/values/rate']},
          {id: 'shared', targets: ['/components/0/values/fee', '/components/1/values/fee']},
        ],
        billingDefinitionTemplate: {
          components: [{values: {rate: '0.71', fee: '2.00'}}, {values: {rate: '0.91', fee: '3.00'}}],
        },
      },
    };

    const inputs = inputsForComponent(preset, 1);

    expect(inputs.map((input) => input.id)).toEqual(['second', 'shared']);
    expect(presetDefault(preset, inputs[0], 1)).toBe('0.91');
    expect(presetDefault(preset, inputs[1], 1)).toBe('3.00');
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

  it('serializes a v2 draft, omitting empty boundaries and preserving component values', () => {
    const configuration = {
      currency: 'PLN',
      timezone: 'Europe/Warsaw',
      priceBasis: 'NET',
      billingCycles: [
        {anchor: '2026-01-15', length: 1, unit: 'MONTH', validFrom: '', validTo: undefined},
        {anchor: '2027-01-15', length: 2, unit: 'MONTH', validFrom: '2027-01-01'},
      ],
      periods: [
        {
          validFrom: '2026-01-01',
          validTo: '2026-12-31',
          components: [
            {kind: 'ENERGY_PURCHASE', presetId: 'first', componentId: 'energy', values: {rate: '1'}},
            {kind: 'DISTRIBUTION_VARIABLE', presetId: 'later', componentId: 'distribution', values: {rate: '2'}},
          ],
        },
      ],
    };

    expect(serializeConfiguration(configuration)).toEqual({
      version: 2,
      currency: 'PLN',
      timezone: 'Europe/Warsaw',
      priceBasis: 'NET',
      billingCycles: [
        {anchor: '2026-01-15', length: 1, unit: 'MONTH'},
        {anchor: '2027-01-15', length: 2, unit: 'MONTH', validFrom: '2027-01-01T00:00:00+01:00'},
      ],
      periods: [
        {
          validFrom: '2026-01-01T00:00:00+01:00',
          validTo: '2027-01-01T00:00:00+01:00',
          components: [
            {kind: 'ENERGY_PURCHASE', presetId: 'first', componentId: 'energy', values: {rate: '1'}},
            {kind: 'DISTRIBUTION_VARIABLE', presetId: 'later', componentId: 'distribution', values: {rate: '2'}},
          ],
        },
      ],
    });
  });
});
