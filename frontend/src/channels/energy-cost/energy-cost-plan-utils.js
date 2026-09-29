import {DateTime} from 'luxon';

export function readJsonPointer(document, pointer) {
  if (pointer === '') return document;
  if (!pointer?.startsWith('/')) return undefined;
  return pointer
    .slice(1)
    .split('/')
    .reduce((value, token) => {
      if (value === undefined || value === null) return undefined;
      const key = token.replaceAll('~1', '/').replaceAll('~0', '~');
      if (Array.isArray(value) && !/^0$|^[1-9]\d*$/.test(key)) return undefined;
      return value[key];
    }, document);
}

export const targetPointer = (target) => (typeof target === 'string' ? target : target?.pointer);

const structurallyEqual = (left, right) => JSON.stringify(left) === JSON.stringify(right);

export function cloneTariffComponents(tariff) {
  return JSON.parse(JSON.stringify(tariff.components || []));
}

export function commonPresetValidity(presets) {
  const validFrom = presets
    .map((preset) => preset.document?.validFrom)
    .filter(Boolean)
    .sort()
    .at(-1);
  const validTo = presets
    .map((preset) => preset.document?.validTo)
    .filter(Boolean)
    .sort()[0];
  if (validFrom && validTo && validFrom >= validTo) return null;
  return {validFrom: validFrom || '', validTo: validTo || ''};
}

export function componentsMatchTariff(components, tariff) {
  const identities = (items) => items.map(({kind, presetId, componentId}) => `${kind}\u0000${presetId}\u0000${componentId}`).sort();
  return JSON.stringify(identities(components)) === JSON.stringify(identities(tariff.components || []));
}

export function compatiblePresetComponents(presets, component) {
  return presets.filter((preset) =>
    (preset.components || []).some((candidate) => candidate.kind === component.kind && candidate.componentId === component.componentId)
  );
}

export const legacyComponentId = (kind) =>
  ({
    ENERGY_PURCHASE: 'energy-purchase',
    DISTRIBUTION_VARIABLE: 'distribution-variable',
    DISTRIBUTION_FIXED: 'distribution-fixed',
    SUPPLIER_FIXED: 'supplier-fixed',
  })[kind];

export function uniqueComponentId(base, components) {
  const usedIds = new Set(components.map((component) => component.componentId || legacyComponentId(component.kind)));
  if (!usedIds.has(base)) return base;
  let suffix = 2;
  while (usedIds.has(`${base}-${suffix}`)) suffix++;
  return `${base}-${suffix}`;
}

export function availablePresetComponentId(presets, kind, components) {
  const usedIds = new Set(components.map((component) => component.componentId || legacyComponentId(component.kind)));
  return presets.flatMap((preset) => preset.components || []).find((component) => component.kind === kind && !usedIds.has(component.componentId))?.componentId;
}

export function presetDefault(preset, input, componentIndex) {
  const componentTarget = componentIndex === undefined ? undefined : `/components/${componentIndex}/`;
  const target = componentTarget ? input.targets?.find((item) => targetPointer(item)?.includes(componentTarget)) : input.targets?.[0];
  const value = readJsonPointer(preset?.document?.billingDefinitionTemplate, targetPointer(target));
  if (!target || typeof target === 'string' || !target.values) return value;
  return Object.entries(target.values).find(([, mappedValue]) => structurallyEqual(mappedValue, value))?.[0];
}

export function inputsForComponent(preset, componentIndex) {
  const componentTarget = `/components/${componentIndex}/`;
  return preset?.document?.inputs?.filter((input) => input.targets?.some((target) => targetPointer(target)?.includes(componentTarget))) || [];
}

export const normalizeDecimal = (value) => String(value).trim().replace(',', '.');
export const isDecimal = (value) => /^-?(?:0|[1-9]\d*)(?:\.\d+)?$/.test(normalizeDecimal(value));
export const isInteger = (value) => /^-?(?:0|[1-9]\d*)$/.test(String(value).trim());
export const isTime = (value) => /^(?:[01]\d|2[0-3]):[0-5]\d$|^24:00$/.test(value);

export function toDatetimeLocal(value, timezone) {
  if (!value) return '';
  const date = DateTime.fromISO(value, {setZone: true}).setZone(timezone);
  return date.isValid ? date.toFormat("yyyy-MM-dd'T'HH:mm") : '';
}

export function fromDatetimeLocal(value, timezone) {
  if (!value) return '';
  const date = DateTime.fromFormat(value, "yyyy-MM-dd'T'HH:mm", {zone: timezone});
  return date.isValid ? date.toISO({suppressMilliseconds: true, includeOffset: true}) : null;
}

export function dateFromDatetime(value, timezone) {
  if (!value) return '';
  const date = DateTime.fromISO(value, {setZone: true}).setZone(timezone);
  return date.isValid ? date.toISODate() : '';
}

export function dateToDatetime(value, timezone) {
  if (!value) return undefined;
  const date = DateTime.fromISO(value, {zone: timezone}).startOf('day');
  return date.isValid ? date.toISO({suppressMilliseconds: true, includeOffset: true}) : undefined;
}

export function dateFromPeriodEnd(value, timezone) {
  if (!value) return '';
  const date = DateTime.fromISO(value, {setZone: true}).setZone(timezone).minus({days: 1});
  return date.isValid ? date.toISODate() : '';
}

export function dateToPeriodEnd(value, timezone) {
  if (!value) return undefined;
  const date = DateTime.fromISO(value, {zone: timezone}).plus({days: 1}).startOf('day');
  return date.isValid ? date.toISO({suppressMilliseconds: true, includeOffset: true}) : undefined;
}

export function shiftDatetimeDays(value, timezone, days) {
  const date = DateTime.fromISO(value, {setZone: true}).setZone(timezone).plus({days});
  return date.isValid ? date.toISO({suppressMilliseconds: true, includeOffset: true}) : undefined;
}

export function billingCyclesCoverPeriod(period, billingCycles) {
  const timestamp = (value, fallback) => (value ? Date.parse(value) : fallback);
  const periodFrom = timestamp(period.validFrom, -Infinity);
  const periodTo = timestamp(period.validTo, Infinity);
  const coverage = billingCycles
    .map((cycle) => ({
      from: Math.max(periodFrom, timestamp(cycle.validFrom, -Infinity)),
      to: Math.min(periodTo, timestamp(cycle.validTo, Infinity)),
    }))
    .filter((cycle) => cycle.from < cycle.to)
    .sort((left, right) => left.from - right.from);
  if (!coverage.length) return false;
  let cursor = period.validFrom ? periodFrom : coverage[0].from;
  const expectedEnd = period.validTo ? periodTo : coverage[coverage.length - 1].to;
  for (const cycle of coverage) {
    if (cycle.from !== cursor) return false;
    cursor = cycle.to;
  }
  return cursor === expectedEnd;
}

export function serializeConfiguration(configuration) {
  const {billingCycles, periods} = configuration;
  const configurationFields = Object.fromEntries(Object.entries(configuration).filter(([key]) => !['priceBasis', 'billingCycles', 'periods'].includes(key)));
  const serializeBoundaries = ({validFrom, validTo, ...value}, period = false) => {
    const serializeBoundary = (boundary, converter) => (/^\d{4}-\d{2}-\d{2}$/.test(boundary) ? converter(boundary, configuration.timezone) : boundary);
    return {
      ...(validFrom === null ? {validFrom: null} : validFrom ? {validFrom: serializeBoundary(validFrom, dateToDatetime)} : {}),
      ...(validTo === null ? {validTo: null} : validTo ? {validTo: serializeBoundary(validTo, period ? dateToPeriodEnd : dateToDatetime)} : {}),
      ...value,
    };
  };

  return {
    ...configurationFields,
    version: 2,
    billingCycles: billingCycles.map(serializeBoundaries),
    periods: periods.map((period, index) => {
      const boundaries = {
        ...period,
        validFrom: index === 0 ? null : period.validFrom,
        validTo: index === periods.length - 1 ? null : period.validTo,
      };
      return {
        ...serializeBoundaries(boundaries, true),
        components: period.components.map(({values, ...component}) => (component.presetId === undefined ? component : {...component, values: {...values}})),
      };
    }),
  };
}
