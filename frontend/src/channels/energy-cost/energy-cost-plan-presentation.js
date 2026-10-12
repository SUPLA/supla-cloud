import {DateTime} from 'luxon';

const metadata = (entry) => entry?.metadata || entry || {};
const partyLabel = (party) => (typeof party === 'object' ? party?.label : typeof party === 'string' ? party : '');
const identity = ({kind, presetId, componentId}) => JSON.stringify([kind, presetId, componentId]);
const identities = (components) => components.map(identity).sort();
const sameComponents = (left, right) => JSON.stringify(identities(left)) === JSON.stringify(identities(right));
const translate = (text, values = {}) => text.replace(/\{(\w+)\}/g, (match, key) => values[key] ?? match);

// Catalog lists and detail responses have different envelopes; neither IDs nor plan names are display fallbacks.
export function tariffCatalogPresentation(entry, t = translate) {
  const info = metadata(entry);
  const operator = partyLabel(info.operator);
  const provider = partyLabel(info.provider);
  const label = info.label && info.label !== (entry?.id || info.id) ? info.label : '';
  return {
    label: (operator || provider) && info.tariffGroup ? `${operator || provider} · ${info.tariffGroup}` : label || t('Unavailable tariff definition'),
    supplier: provider && info.tariffGroup ? `${provider} · ${info.tariffGroup}` : provider || label || t('Unavailable tariff definition'),
    distribution: operator && info.tariffGroup ? `${operator} · ${info.tariffGroup}` : operator || label || t('Unavailable tariff definition'),
    group: operator || t('Other tariffs'),
  };
}

export function presentTariffPeriod(period, {starters = [], presets = [], t = translate} = {}) {
  const components = period?.components || [];
  const starter = components.length && starters.find((candidate) => Array.isArray(candidate.components) && sameComponents(components, candidate.components));
  const presetById = new Map(presets.map((preset) => [preset.id, preset]));
  const descriptions = (kind, field) => [
    ...new Set(
      components.filter((component) => component.kind === kind).map((component) => tariffCatalogPresentation(presetById.get(component.presetId), t)[field])
    ),
  ];
  const details = components.map((component) => {
    const preset = presetById.get(component.presetId);
    const info = metadata(preset);
    const definition = info.components?.find((candidate) => candidate.componentId === component.componentId && candidate.kind === component.kind);
    return {
      label:
        definition?.label ||
        {
          ENERGY_PURCHASE: t('Electricity supply'),
          DISTRIBUTION_VARIABLE: t('Distribution'),
          DISTRIBUTION_FIXED: t('Fixed distribution fee'),
          SUPPLIER_FIXED: t('Supplier fixed charge'),
        }[component.kind] ||
        t('Additional charge'),
      tariff: component.presetId ? tariffCatalogPresentation(preset, t).label : t('Custom charge'),
    };
  });
  return {
    label: starter ? tariffCatalogPresentation(starter, t).label : t('Custom tariff configuration'),
    custom: !starter,
    supplier: descriptions('ENERGY_PURCHASE', 'supplier').join(', '),
    distribution: descriptions('DISTRIBUTION_VARIABLE', 'distribution').join(', '),
    customized: components.some((component) => component.presetId && Object.keys(component.values || {}).length > 0),
    details,
  };
}

export function presentCostPlan(configuration, {starters = [], presets = [], t = translate, locale = 'en', now = DateTime.now()} = {}) {
  const timezone = configuration?.timezone || 'UTC';
  const instant = DateTime.isDateTime(now) ? now : now instanceof Date ? DateTime.fromJSDate(now) : DateTime.fromISO(now);
  const date = (value) => DateTime.fromISO(value, {setZone: true}).setZone(timezone).setLocale(locale);
  const history = (configuration?.periods || []).map((period) => {
    const from = period.validFrom ? date(period.validFrom) : null;
    const to = period.validTo ? date(period.validTo) : null;
    const format = (boundary) => boundary.toLocaleString(boundary.equals(boundary.startOf('day')) ? DateTime.DATE_MED : DateTime.DATETIME_MED);
    const fromLabel = from ? format(from) : null;
    // A midnight half-open end means the preceding local calendar day is the last applicable day.
    const untilLabel = to ? (to.equals(to.startOf('day')) ? to.minus({days: 1}).toLocaleString(DateTime.DATE_MED) : format(to)) : null;
    const effectiveDates =
      fromLabel && untilLabel
        ? t('From {from} until {until}', {from: fromLabel, until: untilLabel})
        : fromLabel
          ? t('From {date}', {date: fromLabel})
          : untilLabel
            ? t('Until {date}', {date: untilLabel})
            : t('Entire history');
    return {
      ...presentTariffPeriod(period, {starters, presets, t}),
      effectiveDates,
      current: (!from || from <= instant) && (!to || instant < to),
      scheduled: Boolean(from && from > instant),
    };
  });
  const currentPeriod = history.find((period) => period.current) || null;
  return {
    ...(currentPeriod || {label: t('No tariff currently effective'), supplier: '', distribution: '', customized: false, details: []}),
    periodCount: history.length,
    currentPeriod,
    history,
  };
}
