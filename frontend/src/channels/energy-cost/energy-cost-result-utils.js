import {DateTime} from 'luxon';

const zero = '0';

function decimalParts(value) {
  const [integer = zero, fraction = ''] = String(value ?? zero).split('.');
  return {negative: integer.startsWith('-'), integer: integer.replace(/^[+-]/, '') || zero, fraction};
}

export function addDecimals(left, right) {
  const a = decimalParts(left);
  const b = decimalParts(right);
  const scale = Math.max(a.fraction.length, b.fraction.length);
  const toInteger = ({negative, integer, fraction}) => BigInt(`${negative ? '-' : ''}${integer}${fraction.padEnd(scale, '0')}`);
  const value = toInteger(a) + toInteger(b);
  const negative = value < 0n;
  const digits = (negative ? -value : value).toString().padStart(scale + 1, '0');
  const whole = scale ? digits.slice(0, -scale) : digits;
  const fraction = scale ? digits.slice(-scale).replace(/0+$/, '') : '';
  return `${negative ? '-' : ''}${whole}${fraction ? `.${fraction}` : ''}`;
}

function roundDecimal(value, scale) {
  const {negative, integer, fraction} = decimalParts(value);
  let digits = BigInt(`${integer}${fraction.slice(0, scale).padEnd(scale, '0')}`);
  if (fraction[scale] >= '5') digits += 1n;
  const rounded = digits.toString().padStart(scale + 1, '0');
  const whole = scale ? rounded.slice(0, -scale) : rounded;
  const decimal = scale ? rounded.slice(-scale) : '';
  return `${negative && digits !== 0n ? '-' : ''}${whole}${decimal ? `.${decimal}` : ''}`;
}

export function formatDecimal(value) {
  const normalized = /e/i.test(String(value ?? zero)) ? Number(value).toFixed(20) : value;
  if (!/^[+-]?\d+(?:\.\d*)?$/.test(String(normalized ?? zero))) return '';
  const rounded = roundDecimal(normalized, 2);
  if (rounded !== '0.00' && rounded !== '-0.00') return rounded;
  const {integer, fraction} = decimalParts(normalized);
  if (integer !== zero || !/[1-9]/.test(fraction)) return rounded;
  return roundDecimal(normalized, fraction.search(/[1-9]/) + 1);
}

export function preferredGranularity(from, to, timezone) {
  const hours = DateTime.fromISO(to, {zone: timezone}).diff(DateTime.fromISO(from, {zone: timezone}), 'hours').hours;
  if (hours <= 48) return 'hour';
  if (hours <= 120 * 24) return 'day';
  return 'month';
}

export function summaryFromResult(result) {
  const costs = result?.costs || {};
  const gross = costs.gross || {};
  const usageBasedTaxes = Object.values(costs.taxes?.byTax || {}).reduce((total, amount) => addDecimals(total, amount), zero);
  return {
    currency: result?.currency,
    usageBased: gross.usageBased?.total ?? zero,
    usageBasedNet: costs.net?.usageBased?.total ?? zero,
    usageBasedTaxes,
    imported: result?.usage?.ACTIVE_ENERGY_IMPORT ?? zero,
    exported: result?.usage?.ACTIVE_ENERGY_EXPORT ?? zero,
  };
}

export function aggregateCharges(charges = [], granularity, timezone) {
  const buckets = new Map();
  charges.forEach((charge) => {
    const date = DateTime.fromISO(charge.from, {setZone: true}).setZone(timezone).startOf(granularity);
    const key = date.toISO();
    const bucket = buckets.get(key) || {from: key, gross: zero, net: zero, taxes: zero, byComponent: {}, byZone: {}};
    const amounts = charge.amounts || {};
    const gross = amounts.gross ?? zero;
    bucket.gross = addDecimals(bucket.gross, gross);
    bucket.net = addDecimals(bucket.net, amounts.net ?? zero);
    bucket.taxes = addDecimals(bucket.taxes, amounts.taxTotal ?? amounts.taxes ?? zero);
    const component = charge.componentId || charge.kind || 'Other';
    bucket.byComponent[component] = addDecimals(bucket.byComponent[component] ?? zero, gross);
    if (charge.selection) bucket.byZone[charge.selection] = addDecimals(bucket.byZone[charge.selection] ?? zero, gross);
    buckets.set(key, bucket);
  });
  return [...buckets.values()].sort((a, b) => a.from.localeCompare(b.from));
}

export function aggregateEnergy(intervals = [], granularity, timezone) {
  const buckets = new Map();
  intervals.forEach((interval) => {
    const date = DateTime.fromISO(interval.from, {setZone: true}).setZone(timezone).startOf(granularity);
    const key = date.toISO();
    const bucket = buckets.get(key) || {from: key, imported: zero, exported: zero};
    bucket.imported = addDecimals(bucket.imported, interval.usage?.ACTIVE_ENERGY_IMPORT ?? zero);
    bucket.exported = addDecimals(bucket.exported, interval.usage?.ACTIVE_ENERGY_EXPORT ?? zero);
    buckets.set(key, bucket);
  });
  return [...buckets.values()].sort((a, b) => a.from.localeCompare(b.from));
}

export function aggregateChargesByWeekdayAndHour(charges = [], timezone) {
  const buckets = Array.from({length: 7}, (_, weekday) => ({weekday: weekday + 1, available: false, hours: Array(24).fill(zero)}));
  charges.forEach((charge) => {
    const date = DateTime.fromISO(charge.from, {setZone: true}).setZone(timezone);
    const bucket = buckets[date.weekday - 1];
    bucket.available = true;
    bucket.hours[date.hour] = addDecimals(bucket.hours[date.hour], charge.amounts?.gross ?? zero);
  });
  return buckets;
}

export function aggregateImportedEnergyByWeekdayAndHour(intervals = [], timezone) {
  const buckets = Array.from({length: 7}, (_, weekday) => ({weekday: weekday + 1, available: false, hours: Array(24).fill(zero)}));
  intervals.forEach((interval) => {
    const date = DateTime.fromISO(interval.from, {setZone: true}).setZone(timezone);
    const bucket = buckets[date.weekday - 1];
    bucket.available = true;
    bucket.hours[date.hour] = addDecimals(bucket.hours[date.hour], interval.usage?.ACTIVE_ENERGY_IMPORT ?? zero);
  });
  return buckets;
}

function breakdown(values = {}) {
  return Object.entries(values)
    .map(([id, amount]) => ({id, amount}))
    .sort((a, b) => Number(b.amount) - Number(a.amount));
}

export const componentBreakdown = (result) => breakdown(result?.costs?.gross?.usageBased?.byComponent);
export const zoneBreakdown = (result) => breakdown(result?.costs?.gross?.usageBased?.byZone);
export const taxBreakdown = (result) => breakdown(result?.costs?.taxes?.byTax);
