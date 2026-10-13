import {describe, expect, it} from 'vitest';
import {mount} from '@vue/test-utils';
import {createI18n} from 'vue-i18n';
import Details from '@/channels/energy-cost/energy-cost-details.vue';
import {displayedPeriodicCharges} from '@/channels/energy-cost/energy-cost-periodic-charges';
import {addDecimals} from '@/channels/energy-cost/energy-cost-result-utils';

const january = '2026-01-01T00:00:00+01:00';
const october = '2026-10-01T00:00:00+02:00';
const nextJanuary = '2027-01-01T00:00:00+01:00';

function charge(id = 'network', from = january, to = october, units = '9', rate = '7.38', net = '66.42', tax = '15.2766', gross = '81.6966') {
  return {
    id,
    kind: 'DISTRIBUTION_FIXED',
    category: 'DISTRIBUTION',
    appliesFrom: from,
    appliesTo: to,
    definition: {period: 'MONTH', prorate: false, rate, unit: 'PLN/month', includedTaxes: []},
    calculated: {
      units,
      amounts: {
        net,
        taxTotal: tax,
        gross,
        taxes: {VAT: {type: 'PERCENTAGE', rate: '0.23', unit: null, includedInSourceAmount: false, taxableBase: net, amount: tax}},
      },
    },
  };
}
const adjacent = () => charge('network', october, nextJanuary, '3', '7.38', '22.14', '5.0922', '27.2322');
const tauron = () => [
  charge(),
  charge('capacity', january, october, '9', '17.18', '154.62', '35.5626', '190.1826'),
  charge('subscription', january, october, '9', '4.56', '41.04', '9.4392', '50.4792'),
  adjacent(),
  charge('capacity', october, nextJanuary, '3', '17.18', '51.54', '11.8542', '63.3942'),
  charge('subscription', october, nextJanuary, '3', '0', '0', '0', '0'),
];
function freeze(value) {
  if (value && typeof value === 'object') {
    Object.values(value).forEach(freeze);
    Object.freeze(value);
  }
  return value;
}
const sum = (rows, key) => rows.reduce((total, row) => addDecimals(total, row.calculated.amounts[key]), '0');

describe('fixed-charge presentation', () => {
  it('shows only September and October for a full-year selection with September 3 to October 9 history', () => {
    const september = '2026-09-01T00:00:00+02:00';
    const november = '2026-11-01T00:00:00+01:00';
    const periods = [
      {from: january, to: september, costs: {gross: {periodic: {total: '0', byComponent: {}}}}},
      {from: september, to: october, costs: {gross: {periodic: {total: '9.0774', byComponent: {network: '9.0774'}}}}},
      {from: october, to: november, costs: {gross: {periodic: {total: '9.0774', byComponent: {network: '9.0774'}}}}},
      {from: november, to: nextJanuary, costs: {gross: {periodic: {total: '0', byComponent: {}}}}},
    ];
    const original = charge('network', january, nextJanuary, '2', '7.38', '14.76', '3.3948', '18.1548');
    const rows = displayedPeriodicCharges([original], periods);
    expect(rows).toEqual([{...original, appliesFrom: september, appliesTo: november}]);
    expect(rows[0].calculated).toBe(original.calculated);
    const wrapper = mount(Details, {
      props: {result: {currency: 'PLN', periodicCharges: [original], billingPeriods: periods}, timezone: 'Europe/Warsaw'},
      global: {plugins: [createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})]},
    });
    expect(wrapper.find('tbody tr').text()).toContain('01 Sep 2026 - 01 Nov 2026');
    expect(wrapper.find('tbody tr').text()).toContain('18.15 PLN');
    wrapper.unmount();
  });
  it('merges the interleaved TAURON G13 rows into three rows without changing any original data or monetary totals', () => {
    const original = tauron();
    const snapshot = structuredClone(original);
    freeze(original);
    const rows = displayedPeriodicCharges(original);
    expect(rows.map((row) => [row.id, row.appliesFrom, row.appliesTo, row.calculated.units])).toEqual([
      ['network', january, nextJanuary, '12'],
      ['capacity', january, nextJanuary, '12'],
      ['subscription', january, october, '9'],
    ]);
    expect(rows[0].calculated.amounts).toMatchObject({net: '88.56', taxTotal: '20.3688', gross: '108.9288'});
    expect(rows[1].calculated.amounts).toMatchObject({net: '206.16', taxTotal: '47.4168', gross: '253.5768'});
    expect(rows[0].calculated.amounts.taxes.VAT).toMatchObject({taxableBase: '88.56', amount: '20.3688'});
    for (const key of ['net', 'taxTotal', 'gross']) expect(sum(rows, key)).toBe(sum(original, key));
    expect(original).toEqual(snapshot);
  });

  it('retains the existing fixed-charge table format with merged dates, units, rates and amounts', () => {
    const periodicCharges = tauron();
    const snapshot = structuredClone(periodicCharges);
    const wrapper = mount(Details, {
      props: {result: {currency: 'PLN', periodicCharges, billingPeriods: []}, timezone: 'Europe/Warsaw'},
      global: {plugins: [createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})]},
    });
    expect(wrapper.findAll('th').map((cell) => cell.text())).toEqual(['Component', 'Applies to', 'Units', 'Rate', 'Net cost', 'Taxes', 'Gross cost']);
    const rows = wrapper.findAll('tbody tr');
    expect(rows).toHaveLength(3);
    expect(rows[0].findAll('td').map((cell) => cell.text())).toEqual([
      'network',
      '01 Jan 2026 - 01 Jan 2027',
      '12.00',
      '7.38 PLN/month',
      '88.56 PLN',
      '20.37 PLN',
      '108.93 PLN',
    ]);
    expect(rows[2].findAll('td')[1].text()).toBe('01 Jan 2026 - 01 Oct 2026');
    expect(rows[2].findAll('td')[2].text()).toBe('9.00');
    expect(periodicCharges).toEqual(snapshot);
    wrapper.unmount();
  });

  it.each(['0', '0.000', '-0.00', 0])('hides only a known exact zero gross cost: %s', (gross) => {
    const row = charge();
    row.calculated.amounts.gross = gross;
    expect(displayedPeriodicCharges([row])).toEqual([]);
  });
  it.each([null, undefined, 'unknown'])('renders unavailable units and monetary amounts as unknown, never zero: %s', (unknown) => {
    const row = charge();
    row.calculated.units = unknown;
    for (const key of ['net', 'taxTotal', 'gross']) row.calculated.amounts[key] = unknown;
    const wrapper = mount(Details, {
      props: {result: {currency: 'PLN', periodicCharges: [row]}, timezone: 'Europe/Warsaw'},
      global: {plugins: [createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})]},
    });
    const cells = wrapper.findAll('tbody td');
    expect(cells[2].text()).toBe('—');
    expect(cells.slice(4).map((cell) => cell.text())).toEqual(['—', '—', '—']);
    expect(wrapper.text()).not.toContain('0.00');
    wrapper.unmount();
  });
  it.each([null, undefined, '', 'unknown', '0.00000000000000000001', '-0.01'])('keeps unknown and non-zero gross costs: %s', (gross) => {
    const row = charge();
    row.calculated.amounts.gross = gross;
    expect(displayedPeriodicCharges([row])).toEqual([row]);
  });

  it.each(['calculated', 'units', 'net', 'taxTotal', 'gross', 'taxAmount', 'taxableBase'])('does not merge unknown %s amounts', (field) => {
    const first = charge();
    const second = adjacent();
    if (field === 'calculated') second.calculated = null;
    else if (field === 'units') second.calculated.units = null;
    else if (field === 'taxAmount') second.calculated.amounts.taxes.VAT.amount = null;
    else if (field === 'taxableBase') second.calculated.amounts.taxes.VAT.taxableBase = null;
    else second.calculated.amounts[field] = null;
    const original = [first, second];
    const snapshot = structuredClone(original);
    expect(displayedPeriodicCharges(original)).toEqual(original);
    expect(original).toEqual(snapshot);
  });

  it.each([
    'rate',
    'unit',
    'period',
    'prorate',
    'includedTaxes',
    'kind',
    'category',
    'taxRate',
    'taxType',
    'taxUnit',
    'taxIncluded',
    'extraDefinition',
    'extraCalculation',
  ])('does not merge different %s rules', (field) => {
    const first = charge();
    const second = adjacent();
    if (field === 'rate') second.definition.rate = '8';
    if (field === 'unit') second.definition.unit = 'EUR/month';
    if (field === 'period') second.definition.period = 'BILLING_PERIOD';
    if (field === 'prorate') second.definition.prorate = true;
    if (field === 'includedTaxes') second.definition.includedTaxes = ['VAT'];
    if (field === 'kind') second.kind = 'SUPPLIER_FIXED';
    if (field === 'category') second.category = 'SUPPLIER';
    if (field === 'taxRate') second.calculated.amounts.taxes.VAT.rate = '0.08';
    if (field === 'taxType') second.calculated.amounts.taxes.VAT.type = 'PER_QUANTITY';
    if (field === 'taxUnit') second.calculated.amounts.taxes.VAT.unit = 'PLN/kWh';
    if (field === 'taxIncluded') second.calculated.amounts.taxes.VAT.includedInSourceAmount = true;
    if (field === 'extraDefinition') second.definition.otherRule = 'different';
    if (field === 'extraCalculation') second.calculated.otherRule = 'different';
    expect(displayedPeriodicCharges([first, second])).toEqual([first, second]);
  });

  it.each(['2026-10-02T00:00:00+02:00', '2026-09-01T00:00:00+02:00', 'invalid'])('does not merge non-adjacent or invalid ranges: %s', (from) => {
    const first = charge();
    const second = {...adjacent(), appliesFrom: from};
    expect(displayedPeriodicCharges([first, second])).toEqual([first, second]);
  });
  it('recognizes equal decimal rates and timestamp boundaries independent of formatting', () => {
    const second = adjacent();
    second.definition.rate = '7.380';
    second.appliesFrom = '2026-09-30T22:00:00Z';
    second.calculated.amounts.taxes.VAT.rate = '0.230';
    expect(displayedPeriodicCharges([charge(), second])).toHaveLength(1);
  });
  it('coalesces a chain of more than two compatible periods', () => {
    const third = charge('network', nextJanuary, '2027-02-01T00:00:00+01:00', '1', '7.38', '7.38', '1.6974', '9.0774');
    const rows = displayedPeriodicCharges([charge(), adjacent(), third]);
    expect(rows).toHaveLength(1);
    expect(rows[0].appliesTo).toBe(third.appliesTo);
    expect(rows[0].calculated.units).toBe('13');
    expect(rows[0].calculated.amounts).toMatchObject({net: '95.94', taxTotal: '22.0662', gross: '118.0062'});
  });
  it('does not extend a merged range across a hidden zero-cost period', () => {
    const zero = charge('network', october, nextJanuary, '3', '0', '0', '0', '0');
    const last = charge('network', nextJanuary, '2027-02-01T00:00:00+01:00', '1', '7.38', '7.38', '1.6974', '9.0774');
    expect(displayedPeriodicCharges([charge(), zero, last])).toEqual([charge(), last]);
  });
  it('sums decimal amounts exactly without intermediate rounding', () => {
    const first = charge('network', january, october, '1', '0.1', '0.1', '0.023', '0.123');
    const second = charge('network', october, nextJanuary, '1', '0.1', '0.1', '0.023', '0.123');
    expect(displayedPeriodicCharges([first, second])[0].calculated.amounts).toMatchObject({net: '0.2', taxTotal: '0.046', gross: '0.246'});
  });
  it('preserves the null taxable base of per-quantity taxes when merging', () => {
    const first = charge();
    const second = adjacent();
    for (const row of [first, second]) {
      row.calculated.amounts.taxes.VAT.type = 'PER_QUANTITY';
      row.calculated.amounts.taxes.VAT.taxableBase = null;
    }
    const rows = displayedPeriodicCharges([first, second]);
    expect(rows).toHaveLength(1);
    expect(rows[0].calculated.amounts.taxes.VAT).toMatchObject({taxableBase: null, amount: '20.3688'});
  });
});
