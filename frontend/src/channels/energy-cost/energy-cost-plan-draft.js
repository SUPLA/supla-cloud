import {
  billingCyclesCoverPeriod,
  fromDatetimeLocal,
  inputsForComponent,
  isDecimal,
  isInteger,
  isTime,
  normalizeDecimal,
  presetDefault,
  serializeConfiguration,
} from './energy-cost-plan-utils';
import {timelineError} from './energy-cost-history';
import {compatibleComponents} from './energy-cost-composer';

export function validateCostPlanDraft(configuration, details) {
  const billingCycles = configuration.billingCycles.map((cycle) => {
    const errors = {};
    if (!/^\d{4}-\d{2}-\d{2}$/.test(cycle.anchor || '')) errors.anchor = 'Anchor date is required.'; // i18n
    if (!isInteger(cycle.length) || Number(cycle.length) < 1) errors.length = 'Length must be positive.'; // i18n
    if (cycle.validFrom && cycle.validTo && Date.parse(cycle.validFrom) >= Date.parse(cycle.validTo)) errors.validTo = 'End date must be after start date.'; // i18n
    return errors;
  });
  const billingTimelineError = timelineError(configuration.billingCycles);
  if (billingTimelineError && billingCycles.length)
    billingCycles[0].timeline = 'Billing periods must be contiguous, with valid transition dates in chronological order.'; // i18n
  const periods = configuration.periods.map((period) => {
    const errors = {components: []};
    if (!billingCyclesCoverPeriod(period, configuration.billingCycles)) errors.billingCycles = 'Billing cycles must cover this period continuously.'; // i18n
    if (!period.components.length || !compatibleComponents(period.components, configuration, details)) errors.configuration = 'Choose a compatible tariff.'; // i18n
    errors.components = period.components.map((component) => {
      const result = {};
      if (component.presetId === undefined) {
        if (!isDecimal(component.rate)) result.rate = 'Invalid decimal value.'; // i18n
        return result;
      }
      const preset = details[component.presetId];
      if (!preset) result.preset = 'Choose a compatible tariff.'; // i18n
      inputsForComponent(preset, component.componentId).forEach((input) => {
        const value = Object.hasOwn(component.values || {}, input.id) ? component.values[input.id] : presetDefault(preset, input, component.componentId);
        if (input.required && (value === null || value === undefined || value === ''))
          result[input.id] = 'This field is required.'; // i18n
        else if (value !== null && value !== undefined && value !== '') {
          if (input.type === 'DECIMAL' && !isDecimal(value)) result[input.id] = 'Invalid decimal value.'; // i18n
          if (input.type === 'INTEGER' && (!isInteger(value) || (input.minimum !== undefined && Number(value) < input.minimum)))
            result[input.id] = 'Invalid integer value.'; // i18n
          if (input.type === 'DATETIME' && !fromDatetimeLocal(value, preset.document.timezone) && !/Z|[+-]\d\d:\d\d$/.test(value))
            result[input.id] = 'Invalid date and time.'; // i18n
          if (input.type === 'TIME' && !isTime(value)) result[input.id] = 'Invalid time.'; // i18n
          if (input.type === 'CHOICE' && !input.options?.some((option) => option.value === value)) result[input.id] = 'Choose a valid option.'; // i18n
        }
      });
      return result;
    });
    if (errors.configuration) errors.components.push({preset: errors.configuration});
    return errors;
  });
  return {
    billingCycles,
    periods,
    invalid:
      Boolean(timelineError(configuration.periods) || billingTimelineError) ||
      billingCycles.some((error) => Object.keys(error).length) ||
      periods.some((error) => Object.keys(error).some((key) => key !== 'components') || error.components.some((component) => Object.keys(component).length)),
  };
}

export function costPlanDraftPayload(configuration, details) {
  return serializeConfiguration({
    ...configuration,
    periods: configuration.periods.map((period) => ({
      ...period,
      components: period.components.map((component) => {
        if (component.presetId === undefined) return {...component, rate: normalizeDecimal(component.rate)};
        const values = {...component.values};
        inputsForComponent(details[component.presetId], component.componentId).forEach((input) => {
          if (Object.hasOwn(values, input.id)) {
            if (input.type === 'DECIMAL') values[input.id] = normalizeDecimal(values[input.id]);
            if (input.type === 'INTEGER' && isInteger(values[input.id])) values[input.id] = Number(values[input.id]);
          }
        });
        return {...component, values};
      }),
    })),
  });
}
