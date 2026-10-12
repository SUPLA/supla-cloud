import {dateToDatetime} from './energy-cost-plan-utils';

export const copyDraft = (value) => JSON.parse(JSON.stringify(value));
export const hasPriceOverrides = (period) =>
  period.components.some((component) => component.presetId === undefined || Object.keys(component.values || {}).length > 0);

// Only transition starts are editable. Never reorder definitions to repair invalid input.
export function deriveTimeline(periods) {
  return periods.map((period, index) => ({
    ...period,
    validFrom: index === 0 ? null : period.validFrom,
    validTo: periods[index + 1]?.validFrom ?? null,
  }));
}

export function timelineError(periods) {
  if (!periods.length) return 'Add a tariff period.'; // i18n
  for (let index = 1; index < periods.length; index++) {
    const start = Date.parse(periods[index].validFrom);
    if (!Number.isFinite(start)) return 'Choose a valid transition date.'; // i18n
    if (index > 1 && start <= Date.parse(periods[index - 1].validFrom)) return 'Transition dates must be unique and in chronological order.'; // i18n
    if (Date.parse(periods[index - 1].validTo) !== start) return 'Tariff periods must be contiguous.'; // i18n
  }
  return '';
}

export function insertTransition(periods, date, timezone) {
  const boundary = dateToDatetime(date, timezone);
  const instant = Date.parse(boundary);
  if (!boundary || !Number.isFinite(instant)) throw new Error('Choose a valid transition date.'); // i18n
  if (timelineError(periods)) throw new Error(timelineError(periods));
  if (periods.some((period) => Date.parse(period.validFrom) === instant)) throw new Error('A transition already exists on this date.'); // i18n
  const next = periods.findIndex((period, index) => index > 0 && Date.parse(period.validFrom) > instant);
  const index = next < 0 ? periods.length : next;
  const result = copyDraft(periods);
  result.splice(index, 0, {...copyDraft(periods[index - 1]), validFrom: boundary});
  return deriveTimeline(result);
}

export function changeTransition(periods, index, date, timezone) {
  return deriveTimeline(periods.map((period, position) => (position === index ? {...period, validFrom: dateToDatetime(date, timezone) || ''} : period)));
}

export function removeTransition(periods, index) {
  if (periods.length === 1) return periods;
  return deriveTimeline(periods.filter((_, position) => position !== index));
}

export function replacePeriodComponents(periods, index, components) {
  return periods.map((period, position) => (position === index ? {...period, components: copyDraft(components)} : period));
}
