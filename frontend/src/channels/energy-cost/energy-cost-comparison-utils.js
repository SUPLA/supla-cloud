import {configurationFromTariff} from './energy-cost-plan-utils';
import {addDecimals, aggregateCharges} from './energy-cost-result-utils';

const decimal = (value) => value !== null && value !== undefined && /^[+-]?\d+(?:\.\d+)?$/.test(String(value));
const instant = (value) => Date.parse(value);
const sameRange = (a, b) => a && b && instant(a.from) === instant(b.from) && instant(a.to) === instant(b.to);

export function comparisonConfiguration(starter, baseline) {
  const configuration = configurationFromTariff(starter);
  for (const key of ['currency', 'timezone', 'taxContext', 'billingCycles']) {
    if (baseline[key] !== undefined) configuration[key] = JSON.parse(JSON.stringify(baseline[key]));
  }
  return configuration;
}

function coverage(result) {
  return [...(result.intervals || [])].sort((a, b) => instant(a.from) - instant(b.from));
}

function validCoverage(result, intervals) {
  let end = instant(result.range?.from);
  for (const interval of intervals) {
    if (instant(interval.from) < end || instant(interval.to) <= instant(interval.from) || instant(interval.to) > instant(result.range?.to)) return false;
    end = instant(interval.to);
  }
  return intervals.length > 0;
}

function calculationFailed(result) {
  const warnings = result.warnings || [];
  return warnings.some((warning) => warning.code !== 'MISSING_METER_DATA') || (result.incomplete && !warnings.length);
}

export function compareCalculations(current, alternative, requestedRange) {
  const fail = (reason) => ({comparable: false, reason});
  if (!current || !alternative) return fail('Select a tariff to compare costs.'); // i18n
  if (!current.processedDeltaCount || !alternative.processedDeltaCount) return fail('No logs are available for the selected range.'); // i18n
  if (calculationFailed(current) || calculationFailed(alternative)) {
    return fail('Some logs could not be calculated. Results cannot be directly compared.'); // i18n
  }
  if (!sameRange(current.range, alternative.range)) return fail('These results cannot be directly compared because the calculated time ranges differ.'); // i18n
  if (!sameRange(current.range, requestedRange))
    return fail('The calculated range does not cover the selected period. Select the displayed range to compare costs.'); // i18n
  if (!current.currency || current.currency !== alternative.currency) return fail('These tariffs use different currencies and cannot be compared.'); // i18n
  const left = coverage(current);
  const right = coverage(alternative);
  if (
    !validCoverage(current, left) ||
    !validCoverage(alternative, right) ||
    left.length !== right.length ||
    current.processedDeltaCount !== alternative.processedDeltaCount ||
    left.some(
      (interval, index) =>
        !sameRange(interval, right[index]) ||
        JSON.stringify(Object.entries(interval.usage || {}).sort()) !== JSON.stringify(Object.entries(right[index].usage || {}).sort())
    )
  ) {
    return fail('Measurement coverage differs or contains gaps. Results cannot be directly compared.'); // i18n
  }
  const sameBillingPeriods =
    current.billingPeriods?.length === alternative.billingPeriods?.length &&
    (current.billingPeriods || []).every((period, index) => sameRange(period, alternative.billingPeriods[index]));
  const full =
    sameBillingPeriods &&
    [current, alternative].every(
      (result) =>
        result.billingContext?.requestedRangeCoversWholePeriods === true &&
        result.billingPeriods?.length > 0 &&
        result.billingPeriods.every((period) => period.fullyCovered) &&
        decimal(result.costs?.gross?.periodic?.total) &&
        decimal(result.costs?.gross?.total)
    );
  const amount = (result) => (full ? result.costs?.gross?.total : result.costs?.gross?.usageBased?.total);
  if (
    !decimal(amount(current)) ||
    !decimal(amount(alternative)) ||
    [current, alternative].some((result) => result.charges?.some((charge) => !decimal(charge.amounts?.gross)))
  ) {
    return fail('Cost amounts are unavailable for this comparison.'); // i18n
  }
  const negate = (value) => (String(value).startsWith('-') ? String(value).slice(1) : `-${value}`);
  const savings = addDecimals(amount(current), negate(amount(alternative)));
  return {
    comparable: true,
    full,
    costBasis: full ? 'fullGross' : 'usageBasedGross',
    current: amount(current),
    alternative: amount(alternative),
    savings,
    percentage: Number(amount(current)) > 0 ? (Number(savings) / Number(amount(current))) * 100 : null,
  };
}

export function comparisonBuckets(current, alternative, granularity, timezone) {
  const left = new Map(aggregateCharges(current?.charges, granularity, timezone).map((bucket) => [instant(bucket.from), bucket]));
  const right = new Map(aggregateCharges(alternative?.charges, granularity, timezone).map((bucket) => [instant(bucket.from), bucket]));
  return [...new Set([...left.keys(), ...right.keys()])]
    .sort((a, b) => a - b)
    .map((timestamp) => ({
      from: new Date(timestamp).toISOString(),
      current: left.get(timestamp)?.gross ?? null,
      alternative: right.get(timestamp)?.gross ?? null,
    }));
}
