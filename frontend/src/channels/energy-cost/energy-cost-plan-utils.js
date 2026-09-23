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

export function presetDefault(preset, input, componentIndex) {
  const componentTarget = componentIndex === undefined ? undefined : `/components/${componentIndex}/`;
  const target = componentTarget ? input.targets?.find((target) => target.includes(componentTarget)) : input.targets?.[0];
  return readJsonPointer(preset?.document?.billingDefinitionTemplate, target);
}

export function inputsForComponent(preset, componentIndex) {
  const componentTarget = `/components/${componentIndex}/`;
  return preset?.document?.inputs?.filter((input) => input.targets?.some((target) => target.includes(componentTarget))) || [];
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

export function serializeConfiguration(configuration) {
  const serializeBoundaries = ({validFrom, validTo, ...value}, period = false) => {
    const serializeBoundary = (boundary, converter) => (/^\d{4}-\d{2}-\d{2}$/.test(boundary) ? converter(boundary, configuration.timezone) : boundary);
    return {
      ...(validFrom ? {validFrom: serializeBoundary(validFrom, dateToDatetime)} : {}),
      ...(validTo ? {validTo: serializeBoundary(validTo, period ? dateToPeriodEnd : dateToDatetime)} : {}),
      ...value,
    };
  };

  return {
    version: 2,
    currency: configuration.currency,
    timezone: configuration.timezone,
    priceBasis: configuration.priceBasis,
    billingCycles: configuration.billingCycles.map(serializeBoundaries),
    periods: configuration.periods.map((period) => ({
      ...serializeBoundaries(period, true),
      components: period.components.map((component) => ({...component, values: {...component.values}})),
    })),
  };
}
