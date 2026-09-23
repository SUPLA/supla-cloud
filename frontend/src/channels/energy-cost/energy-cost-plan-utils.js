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

export function presetDefault(preset, input) {
  return readJsonPointer(preset?.document?.billingDefinitionTemplate, input.targets?.[0]);
}

export function simulationDefaultValues(preset) {
  const inputIds = new Set(preset?.document?.inputs?.map((input) => input.id));
  return Object.fromEntries(Object.entries(preset?.document?.simulationDefaults?.values || {}).filter(([inputId]) => inputIds.has(inputId)));
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
  return {
    version: 1,
    entries: configuration.entries.map((entry) => ({
      ...(entry.validFrom ? {validFrom: entry.validFrom} : {}),
      ...(entry.validTo ? {validTo: entry.validTo} : {}),
      presetId: entry.presetId,
      values: {...entry.values},
    })),
  };
}
