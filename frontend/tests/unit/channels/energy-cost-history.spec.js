import {describe, expect, it} from 'vitest';
import {
  changeTransition,
  copyDraft,
  hasPriceOverrides,
  insertTransition,
  removeTransition,
  replacePeriodComponents,
  timelineError,
} from '@/channels/energy-cost/energy-cost-history';
import {compatibleComponents, composeRole, composerOptions} from '@/channels/energy-cost/energy-cost-composer';
import {costPlanDraftPayload, validateCostPlanDraft} from '@/channels/energy-cost/energy-cost-plan-draft';
import {configurationFromTariff} from '@/channels/energy-cost/energy-cost-plan-utils';

const zone = 'Europe/Warsaw';
const component = (presetId, kind = 'ENERGY_PURCHASE', componentId = 'energy-purchase') => ({kind, presetId, componentId, values: {}});
const g11 = [component('TAURON-G11')];
const g12 = [component('TAURON-G12')];
const initial = () => configurationFromTariff({components: g11});

describe('tariff transition history', () => {
  it('changes G11 into G11 followed by G12 from May 1, 2026', () => {
    const configuration = initial();
    const periods = replacePeriodComponents(insertTransition(configuration.periods, '2026-05-01', zone), 1, g12);
    expect(periods).toEqual([
      {validFrom: null, validTo: '2026-05-01T00:00:00+02:00', components: g11},
      {validFrom: '2026-05-01T00:00:00+02:00', validTo: null, components: g12},
    ]);
    expect(configuration.periods).toEqual(initial().periods);
    expect(timelineError(periods)).toBe('');
  });

  it('inserts between existing transitions and inherits an independent copy', () => {
    let periods = insertTransition(initial().periods, '2026-03-01', zone);
    periods[1].components[0].values = {price: '0.99'};
    periods = insertTransition(periods, '2026-09-01', zone);
    const result = insertTransition(periods, '2026-05-01', zone);
    expect(result.map((period) => period.validFrom?.slice(0, 10))).toEqual([undefined, '2026-03-01', '2026-05-01', '2026-09-01']);
    result[2].components[0].values.price = '1.50';
    expect(result[1].components[0].values.price).toBe('0.99');
    expect(periods[1].components[0].values.price).toBe('0.99');
    expect(timelineError(result)).toBe('');
  });

  it('removes a transition by extending the preceding tariff and preserving later tariffs', () => {
    const periods = insertTransition(insertTransition(initial().periods, '2026-05-01', zone), '2026-09-01', zone);
    periods[2].components = g12;
    const result = removeTransition(periods, 1);
    expect(result[0].validTo).toBe(result[1].validFrom);
    expect(result[1].components).toEqual(g12);
    expect(timelineError(result)).toBe('');
    expect(removeTransition(result, 1)).toEqual(initial().periods);
  });

  it('does not repair reordered, empty or duplicate transition dates', () => {
    const periods = insertTransition(insertTransition(initial().periods, '2026-05-01', zone), '2026-09-01', zone);
    expect(() => insertTransition(periods, '2026-05-01', zone)).toThrow(/already exists/);
    const changed = changeTransition(periods, 1, '2026-10-01', zone);
    expect(timelineError(changed)).toMatch(/chronological/);
    expect(changed[2].validFrom).toBe(periods[2].validFrom);
    expect(timelineError(changeTransition(periods, 1, '', zone))).toMatch(/valid transition date/);
    expect(timelineError(changeTransition(periods, 1, '2026-09-01', zone))).toMatch(/unique/);
    expect(timelineError([{...periods[0], validTo: '2026-04-01T00:00:00+02:00'}, ...periods.slice(1)])).toMatch(/contiguous/);
  });

  it('removes the first period while retaining subsequent tariffs and boundaries', () => {
    const periods = insertTransition(insertTransition(initial().periods, '2026-05-01', zone), '2026-09-01', zone);
    periods[1].components = g12;
    const result = removeTransition(periods, 0);
    expect(result).toEqual([{...periods[1], validFrom: null}, periods[2]]);
    expect(timelineError(result)).toBe('');
    expect(periods[1].validFrom).toBe('2026-05-01T00:00:00+02:00');
    const last = removeTransition(result, 0);
    expect(last[0].validFrom).toBeNull();
    expect(last[0].validTo).toBeNull();
    expect(removeTransition(last, 0)).toEqual(last);
  });

  it('uses local midnight across spring and autumn daylight saving transitions', () => {
    const spring = insertTransition(insertTransition(initial().periods, '2026-03-29', zone), '2026-03-30', zone);
    expect(spring[1].validFrom).toBe('2026-03-29T00:00:00+01:00');
    expect(spring[2].validFrom).toBe('2026-03-30T00:00:00+02:00');
    expect(Date.parse(spring[1].validTo) - Date.parse(spring[1].validFrom)).toBe(23 * 3600000);
    const autumn = insertTransition(insertTransition(initial().periods, '2026-10-25', zone), '2026-10-26', zone);
    expect(Date.parse(autumn[1].validTo) - Date.parse(autumn[1].validFrom)).toBe(25 * 3600000);
  });

  it('preserves overrides on date edits and replaces only the explicitly selected tariff', () => {
    const configuration = initial();
    configuration.periods[0].components[0].values = {price: '0.91'};
    configuration.periods = insertTransition(configuration.periods, '2026-05-01', zone);
    const original = copyDraft(configuration);
    configuration.periods = changeTransition(configuration.periods, 1, '2026-06-01', zone);
    expect(hasPriceOverrides(configuration.periods[1])).toBe(true);
    expect(configuration.periods[0].components).toEqual(original.periods[0].components);
    expect(configuration.periods[1].components).toEqual(original.periods[1].components);
    const replaced = replacePeriodComponents(configuration.periods, 1, g12);
    expect(replaced[0]).toEqual(configuration.periods[0]);
    expect(replaced[1].validFrom).toBe(configuration.periods[1].validFrom);
    expect(replaced[1].components).toEqual(g12);
    expect(configuration.billingCycles).toEqual(original.billingCycles);
  });
});

describe('independent supplier and distribution composer', () => {
  const supply = component('ENERGA-G12');
  const distribution = component('TAURON-DISTRIBUTION-G12', 'DISTRIBUTION_VARIABLE', 'distribution-variable');
  const fixed = ['network', 'capacity', 'subscription'].map((id) => component('TAURON-FIXED-G12', 'DISTRIBUTION_FIXED', id));
  const presets = [supply, distribution, ...fixed].map((item) => ({id: item.presetId, components: [{kind: item.kind, componentId: item.componentId}]}));
  const starters = [{components: [component('TAURON-SUPPLY-G12'), distribution, ...fixed]}];
  const details = Object.fromEntries(
    presets.map((preset) => [
      preset.id,
      {
        document: {
          currency: 'PLN',
          timezone: zone,
          taxContext: {jurisdiction: 'PL', customerClass: 'HOUSEHOLD'},
          billingDefinitionTemplate: {
            components: presets
              .filter((item) => item.id === preset.id)
              .flatMap((item) => item.components.map((entry) => ({id: entry.componentId, kind: entry.kind}))),
          },
        },
      },
    ])
  );

  it('creates ENERGA supply with TAURON variable and all associated fixed distribution charges', () => {
    const configuration = configurationFromTariff({components: []});
    let period = composeRole(configuration.periods[0], 'supply', composerOptions('supply', presets, starters)[0].components);
    period = composeRole(period, 'distribution', composerOptions('distribution', presets, starters)[0].components);
    expect(period.components).toEqual([supply, distribution, ...fixed]);
    expect(compatibleComponents(period.components, configuration, details)).toBe(true);
    expect(
      costPlanDraftPayload({...configuration, periods: [period]}, details).periods[0].components.every((entry) => Object.keys(entry.values).length === 0)
    ).toBe(true);
  });

  it('retains every compatible component of a multi-component preset without a starter', () => {
    const options = composerOptions(
      'supply',
      [
        {
          id: 'multi',
          components: [
            {kind: 'ENERGY_PURCHASE', componentId: 'import'},
            {kind: 'SUPPLIER_FIXED', componentId: 'fee'},
          ],
        },
      ],
      []
    );
    expect(options[0].components.map((entry) => entry.componentId)).toEqual(['import', 'fee']);
  });

  it('rejects incompatible IDs, currency, timezone and tax context', () => {
    const configuration = initial();
    expect(compatibleComponents([supply, supply], configuration, details)).toBe(false);
    expect(compatibleComponents([{...supply, componentId: 'unknown'}], configuration, details)).toBe(false);
    expect(compatibleComponents([supply], {...configuration, currency: 'EUR'}, details)).toBe(false);
    expect(compatibleComponents([supply], {...configuration, timezone: 'UTC'}, details)).toBe(false);
    expect(compatibleComponents([supply], {...configuration, taxContext: {jurisdiction: 'DE', customerClass: 'HOUSEHOLD'}}, details)).toBe(false);
  });

  it('allows an empty editing draft but rejects saving it', () => {
    const configuration = configurationFromTariff({components: []});
    expect(configuration.periods[0].components).toEqual([]);
    expect(validateCostPlanDraft(configuration, details).invalid).toBeTruthy();
  });
});
