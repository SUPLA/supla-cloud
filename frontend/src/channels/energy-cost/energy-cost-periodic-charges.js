import {addDecimals} from './energy-cost-result-utils';

const decimal = (value) => (typeof value === 'string' || typeof value === 'number') && /^[+-]?\d+(?:\.\d+)?$/.test(String(value));
const zero = (value) => decimal(value) && /^[+-]?0+(?:\.0+)?$/.test(String(value));
const without = (value, keys) => Object.fromEntries(Object.entries(value).filter(([key]) => !keys.includes(key)));
const canonical = (value) =>
  Array.isArray(value)
    ? value.map(canonical)
    : value && typeof value === 'object'
      ? Object.fromEntries(
          Object.keys(value)
            .sort()
            .map((key) => [key, canonical(value[key])])
        )
      : value;

function ruleSignature(charge) {
  const {appliesFrom, appliesTo, calculated, ...metadata} = charge;
  const definition = metadata.definition;
  const amounts = calculated?.amounts;
  if (
    !decimal(definition?.rate) ||
    !definition.period ||
    typeof definition.prorate !== 'boolean' ||
    !Array.isArray(definition.includedTaxes) ||
    !decimal(calculated?.units) ||
    !['net', 'taxTotal', 'gross'].every((key) => decimal(amounts?.[key])) ||
    !amounts.taxes ||
    typeof amounts.taxes !== 'object' ||
    !Number.isFinite(Date.parse(appliesFrom)) ||
    !(Date.parse(appliesTo) > Date.parse(appliesFrom))
  )
    return null;

  const taxRules = {};
  for (const [id, tax] of Object.entries(amounts.taxes)) {
    if (!tax || !decimal(tax.amount) || !decimal(tax.rate) || !(decimal(tax.taxableBase) || (tax.type === 'PER_QUANTITY' && tax.taxableBase === null))) {
      return null;
    }
    taxRules[id] = {...without(tax, ['amount', 'taxableBase']), rate: addDecimals(tax.rate, '0')};
  }
  return JSON.stringify(
    canonical({
      ...metadata,
      definition: {...definition, rate: addDecimals(definition.rate, '0')},
      calculationRules: without(calculated, ['units', 'amounts']),
      amountRules: without(amounts, ['net', 'taxTotal', 'gross', 'taxes']),
      taxRules,
    })
  );
}

function merge(left, right) {
  const leftAmounts = left.calculated.amounts;
  const rightAmounts = right.calculated.amounts;
  const amounts = {...leftAmounts};
  for (const key of ['net', 'taxTotal', 'gross']) amounts[key] = addDecimals(leftAmounts[key], rightAmounts[key]);
  amounts.taxes = Object.fromEntries(
    Object.entries(leftAmounts.taxes).map(([id, tax]) => [
      id,
      {
        ...tax,
        amount: addDecimals(tax.amount, rightAmounts.taxes[id].amount),
        taxableBase:
          tax.taxableBase === null || rightAmounts.taxes[id].taxableBase === null ? null : addDecimals(tax.taxableBase, rightAmounts.taxes[id].taxableBase),
      },
    ])
  );
  return {
    ...left,
    appliesTo: right.appliesTo,
    calculated: {...left.calculated, units: addDecimals(left.calculated.units, right.calculated.units), amounts},
  };
}

export function displayedPeriodicCharges(charges = [], billingPeriods) {
  // Reported billing periods carry the authoritative per-period tax calculation.
  // Keep the legacy top-level charges for callers that do not request reporting.
  if (billingPeriods?.some((period) => Array.isArray(period.periodicCharges))) {
    charges = billingPeriods.filter((period) => period.fullyCovered).flatMap((period) => period.periodicCharges || []);
    billingPeriods = undefined;
  }
  const rows = [];
  const latest = new Map();
  for (let charge of charges) {
    if (billingPeriods?.length) {
      // Non-observed periods have calculator-produced zero periodic costs, even for partial selections.
      const periods = billingPeriods.filter((period) => {
        const costs = period.costs?.gross?.periodic;
        return (
          Date.parse(period.from) < Date.parse(charge.appliesTo) &&
          Date.parse(period.to) > Date.parse(charge.appliesFrom) &&
          (costs?.total === null || (decimal(costs?.byComponent?.[charge.id]) && !zero(costs.byComponent[charge.id])))
        );
      });
      if (!periods.length) continue;
      charge = {
        ...charge,
        appliesFrom: Date.parse(periods[0].from) > Date.parse(charge.appliesFrom) ? periods[0].from : charge.appliesFrom,
        appliesTo: Date.parse(periods[periods.length - 1].to) < Date.parse(charge.appliesTo) ? periods[periods.length - 1].to : charge.appliesTo,
      };
    }
    if (zero(charge.calculated?.amounts?.gross)) {
      latest.delete(charge.id);
      continue;
    }
    const signature = ruleSignature(charge);
    const previous = latest.get(charge.id);
    if (charge.id && signature && previous?.signature === signature && Date.parse(rows[previous.index].appliesTo) === Date.parse(charge.appliesFrom)) {
      rows[previous.index] = merge(rows[previous.index], charge);
    } else {
      latest.set(charge.id, {index: rows.length, signature});
      rows.push(charge);
    }
  }
  return rows;
}
