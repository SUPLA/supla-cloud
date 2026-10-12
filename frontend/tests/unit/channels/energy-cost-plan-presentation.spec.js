import {describe, expect, it} from 'vitest';
import {presentCostPlan, presentTariffPeriod, tariffCatalogPresentation} from '@/channels/energy-cost/energy-cost-plan-presentation';

const component = (kind, presetId, componentId) => ({kind, presetId, componentId, values: {}});
const recipe = (group) => [
  component('ENERGY_PURCHASE', `PL.TAURON_SPRZEDAZ.${group}`, 'energy-purchase'),
  component('DISTRIBUTION_VARIABLE', `PL.TAURON_DYSTRYBUCJA.${group}`, 'distribution-variable'),
  ...['distribution-fixed-network', 'capacity-fee', 'distribution-subscription'].map((id) =>
    component('DISTRIBUTION_FIXED', `PL.TAURON_DYSTRYBUCJA.${group}.FIXED`, id)
  ),
];
const starters = ['G11', 'G12'].map((group) => ({
  id: `starter-${group}`,
  metadata: {label: `TAURON Dystrybucja - ${group}`, operator: {id: 'TAURON_DYSTRYBUCJA', label: 'TAURON Dystrybucja'}, tariffGroup: group},
  components: recipe(group),
}));
const presets = [
  ...['G11', 'G12'].flatMap((group) => [
    {id: `PL.TAURON_SPRZEDAZ.${group}`, provider: {label: 'TAURON Sprzedaż'}, tariffGroup: group},
    {id: `PL.TAURON_DYSTRYBUCJA.${group}`, operator: {label: 'TAURON Dystrybucja'}, tariffGroup: group},
    {
      id: `PL.TAURON_DYSTRYBUCJA.${group}.FIXED`,
      operator: {label: 'TAURON Dystrybucja'},
      tariffGroup: group,
      components: [
        {kind: 'DISTRIBUTION_FIXED', componentId: 'distribution-fixed-network', label: 'Network fee'},
        {kind: 'DISTRIBUTION_FIXED', componentId: 'capacity-fee', label: 'Capacity fee'},
        {kind: 'DISTRIBUTION_FIXED', componentId: 'distribution-subscription', label: 'Subscription'},
      ],
    },
  ]),
  {id: 'PL.ENERGA_OBROT.G12', provider: {label: 'ENERGA-OBRÓT'}, tariffGroup: 'G12'},
];
const catalogs = {starters, presets};
const plan = (periods = [{validFrom: null, validTo: null, components: recipe('G12')}]) => ({timezone: 'Europe/Warsaw', periods});
const historyPlan = () =>
  plan([
    {validFrom: null, validTo: '2026-04-30T22:00:00Z', components: recipe('G11')},
    {validFrom: '2026-05-01T00:00:00+02:00', validTo: null, components: recipe('G12')},
  ]);

describe('CostPlan presentation', () => {
  it('recognizes TAURON G12 and shows the supplier without five technical tariff labels', () => {
    const result = presentCostPlan(plan(), catalogs);
    expect(result.label).toBe('TAURON Dystrybucja · G12');
    expect(result.supplier).toBe('TAURON Sprzedaż · G12');
    expect(result.custom).toBe(false);
    expect(result.customized).toBe(false);
  });

  it('keeps distinct fixed charges in details without adding them as separate distribution tariffs', () => {
    const result = presentCostPlan(plan(), catalogs);
    expect(result.distribution).toBe('TAURON Dystrybucja · G12');
    expect(result.details.slice(2).map((detail) => detail.label)).toEqual(['Network fee', 'Capacity fee', 'Subscription']);
  });

  it('recognizes component identity independently of order and object key order', () => {
    const components = recipe('G12')
      .reverse()
      .map(({kind, presetId, componentId}) => ({componentId, values: {}, presetId, kind}));
    expect(presentTariffPeriod({components}, catalogs).label).toBe('TAURON Dystrybucja · G12');
  });

  it('retains the recognized label when prices have overrides', () => {
    const configuration = plan();
    configuration.periods[0].components[0].values = {'price.DAY': '0.99'};
    const result = presentCostPlan(configuration, catalogs);
    expect(result.label).toBe('TAURON Dystrybucja · G12');
    expect(result.customized).toBe(true);
  });

  it('does not recognize a different identity, missing component, or duplicated identity as the starter', () => {
    const original = recipe('G12');
    const changed = structuredClone(original);
    changed[2].componentId = 'another-charge';
    for (const components of [changed, original.slice(1), [...original, original[0]]]) {
      expect(presentTariffPeriod({components}, catalogs).label).toBe('Custom tariff configuration');
    }
  });

  it('presents independent ENERGA supply and TAURON distribution', () => {
    const components = recipe('G12');
    components[0] = component('ENERGY_PURCHASE', 'PL.ENERGA_OBROT.G12', 'energy-purchase');
    const result = presentTariffPeriod({components}, catalogs);
    expect(result.label).toBe('Custom tariff configuration');
    expect(result.supplier).toBe('ENERGA-OBRÓT · G12');
    expect(result.distribution).toBe('TAURON Dystrybucja · G12');
  });

  it('deduplicates supplier descriptions while preserving component details', () => {
    const components = [component('ENERGY_PURCHASE', 'PL.ENERGA_OBROT.G12', 'one'), component('ENERGY_PURCHASE', 'PL.ENERGA_OBROT.G12', 'two')];
    const result = presentTariffPeriod({components}, catalogs);
    expect(result.supplier).toBe('ENERGA-OBRÓT · G12');
    expect(result.details).toHaveLength(2);
  });

  it('presents historical effective periods using local dates and half-open boundaries', () => {
    const result = presentCostPlan(historyPlan(), {...catalogs, now: '2026-05-10T00:00:00Z'});
    expect(result.label).toBe('TAURON Dystrybucja · G12');
    expect(result.periodCount).toBe(2);
    expect(result.history.map((entry) => entry.effectiveDates)).toEqual(['Until Apr 30, 2026', 'From May 1, 2026']);
    expect(result.currentPeriod).toBe(result.history[1]);
  });

  it('keeps a scheduled future tariff out of the current summary', () => {
    const result = presentCostPlan(historyPlan(), {...catalogs, now: '2026-04-30T21:59:59Z'});
    expect(result.label).toBe('TAURON Dystrybucja · G11');
    expect(result.history[1].scheduled).toBe(true);
    expect(result.history[1].current).toBe(false);
    expect(presentCostPlan(historyPlan(), {...catalogs, now: '2026-04-30T22:00:00Z'}).label).toBe('TAURON Dystrybucja · G12');
  });

  it('uses safe fallbacks when catalogs or a referenced preset are unavailable', () => {
    const result = presentCostPlan(plan());
    expect(result.label).toBe('Custom tariff configuration');
    expect(result.supplier).toBe('Unavailable tariff definition');
    expect(tariffCatalogPresentation({id: 'missing', label: 'missing'}).label).toBe('Unavailable tariff definition');
  });

  it('presents inline components without a known starter', () => {
    const result = presentCostPlan(plan([{components: [{kind: 'SUPPLIER_FIXED', componentId: 'inline', rate: '25', per: 'MONTH'}]}]), catalogs);
    expect(result.label).toBe('Custom tariff configuration');
    expect(result.details).toEqual([{label: 'Supplier fixed charge', tariff: 'Custom charge'}]);
    expect(result.customized).toBe(false);
  });

  it('does not expose preset IDs anywhere in ordinary presentation output or mutate configuration', () => {
    const configuration = historyPlan();
    const before = JSON.stringify(configuration);
    for (const options of [catalogs, {}]) {
      const serialized = JSON.stringify(presentCostPlan(configuration, options));
      for (const period of configuration.periods) for (const item of period.components) expect(serialized).not.toContain(item.presetId);
    }
    expect(JSON.stringify(configuration)).toBe(before);
  });

  it('supports list and detail metadata envelopes consistently', () => {
    const info = {label: 'Friendly tariff', operator: {id: 'internal', label: 'Friendly operator'}, tariffGroup: 'R1'};
    expect(tariffCatalogPresentation(info)).toEqual(tariffCatalogPresentation({metadata: info}));
    expect(tariffCatalogPresentation({operator: {id: 'internal'}}).group).toBe('Other tariffs');
  });

  it('does not treat preset price history as effective tariff history', () => {
    const result = presentCostPlan(plan(), {
      ...catalogs,
      presets: presets.map((preset) => ({...preset, document: {billingDefinitionTemplate: {periods: [{}, {}, {}]}}})),
    });
    expect(result.periodCount).toBe(1);
    expect(result.history[0].effectiveDates).toBe('Entire history');
  });

  it('uses locale-aware history dates and preserves non-midnight exclusive boundaries', () => {
    const result = presentCostPlan(plan([{validTo: '2026-05-01T10:00:00Z', components: recipe('G12')}]), {...catalogs, locale: 'pl'});
    expect(result.history[0].effectiveDates).toContain('12:00');
    expect(result.history[0].effectiveDates).toContain('maj');
  });
});
