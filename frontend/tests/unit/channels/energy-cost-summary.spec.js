import {describe, expect, it} from 'vitest';
import {mount} from '@vue/test-utils';
import {createI18n} from 'vue-i18n';
import Summary from '@/channels/energy-cost/energy-cost-summary.vue';
import {calculation, monthlyCalculation} from './energy-cost-comparison-fixtures';
import polish from '../../../../translations/messages.pl.yml';

function summary(result, locale = 'en') {
  const i18n = createI18n({legacy: false, locale, messages: {pl: polish}, missingWarn: false, fallbackWarn: false});
  return mount(Summary, {
    props: {result, timezone: 'Europe/Warsaw'},
    global: {plugins: [i18n], mocks: {$t: i18n.global.t}},
  });
}

describe('report total expenses', () => {
  it('places the full gross total including fixed charges above the existing summary boxes', () => {
    const wrapper = summary(monthlyCalculation());
    const total = wrapper.get('.energy-cost-total-expenses');
    expect(total.get('strong').text()).toBe('910.00 PLN');
    expect(total.text()).toContain('Gross cost, including fixed charges');
    expect(total.element.nextElementSibling).toBe(wrapper.get('.energy-cost-summary').element);
    expect(wrapper.find('.energy-cost-total-alignment-hint').exists()).toBe(false);
  });
  it('shows usage gross and taxes for a partial historical or ongoing period and offers Expand', async () => {
    const result = calculation();
    result.reportSummary = {
      costs: {gross: {total: '100', usageBased: {total: '100'}, periodic: {total: '0'}}, taxes: {total: '18.7'}},
      fixedChargesComplete: false,
    };
    const wrapper = summary(result);
    const total = wrapper.get('.energy-cost-total-expenses');
    expect(total.get('strong').text()).toBe('100.00 PLN');
    expect(total.get('small').text()).toBe('Gross cost, without fixed charges');
    expect(total.text()).toContain('01 Sep 2026, 02:00');
    expect(total.text()).toContain('Some fixed charges are not included');
    expect(total.get('button').text()).toBe('Expand');
    expect(total.text()).toContain('Expand the selection to the entire billing period');
    expect(wrapper.get('.energy-cost-summary').text()).toContain('18.70 PLN');
    expect(wrapper.get('.energy-cost-summary').text()).toContain('100.00 PLN');
    await total.get('button').trigger('click');
    expect(wrapper.emitted('align-billing-periods')).toHaveLength(1);
  });
  it('shows included middle-period fixed charges and offers Align for multiple periods', () => {
    const result = calculation();
    result.billingPeriods = [
      {fullyCovered: false, costs: {gross: {periodic: {total: null}}}},
      {fullyCovered: true, costs: {gross: {periodic: {total: '12.3'}}}},
      {fullyCovered: false, costs: {gross: {periodic: {total: null}}}},
    ];
    result.reportSummary = {
      costs: {gross: {total: '135.3', usageBased: {total: '123'}, periodic: {total: '12.3'}}, taxes: {total: '25.3'}},
      fixedChargesComplete: false,
    };
    const wrapper = summary(result);
    expect(wrapper.get('.energy-cost-total-expenses strong').text()).toBe('135.30 PLN');
    expect(wrapper.get('.energy-cost-total-expenses small').text()).toBe('Gross cost, including some fixed charges');
    expect(wrapper.get('.energy-cost-total-expenses button').text()).toBe('Align');
    expect(wrapper.get('.energy-cost-summary').text()).toContain('12.30 PLN');
    expect(wrapper.get('.energy-cost-summary').text()).toContain('25.30 PLN');
    expect(wrapper.text().match(/Some fixed charges are not included/g)).toHaveLength(1);
  });
  it('keeps a valid total and displays zero without suggesting alignment for a plan without fixed charges', () => {
    const result = calculation();
    result.costs.gross.periodic.total = '0';
    result.costs.gross.total = '100';
    result.reportSummary = {costs: result.costs, fixedChargesComplete: true};
    const wrapper = summary(result);
    expect(wrapper.get('.energy-cost-total-expenses strong').text()).toBe('100.00 PLN');
    expect(wrapper.get('.energy-cost-total-expenses small').text()).toBe('Gross cost, without fixed charges');
    expect(wrapper.find('.energy-cost-total-alignment-hint').exists()).toBe(false);
    expect(wrapper.get('.energy-cost-summary').text()).toContain('0.00 PLN');
  });
  it('does not confuse calculation warnings with missing fixed charges', () => {
    const result = monthlyCalculation();
    result.incomplete = true;
    result.warnings = [{code: 'MISSING_REFERENCE_DATA'}];
    result.reportSummary = {costs: result.costs, fixedChargesComplete: true};
    expect(summary(result).find('.energy-cost-total-alignment-hint').exists()).toBe(false);
  });
  it('localizes partial-range information and actions in Polish', () => {
    const result = calculation();
    result.reportSummary = {costs: {gross: {total: '100', periodic: {total: '0'}}}, fixedChargesComplete: false};
    const wrapper = summary(result, 'pl');
    expect(wrapper.text()).toContain('Niektóre opłaty stałe nie są uwzględnione');
    expect(wrapper.text()).toContain('Rozszerz zakres do całego okresu rozliczeniowego');
    expect(wrapper.get('button').text()).toBe('Rozwiń');
  });
  it('does not invent a missing total', () => {
    const result = monthlyCalculation();
    delete result.costs.gross.total;
    const wrapper = summary(result);
    expect(wrapper.get('.energy-cost-total-expenses strong').text()).toBe('—');
    expect(wrapper.text()).toContain('Total expenses are unavailable for this period.');
  });
  it('does not offer expansion for incomplete billing periods outside observation history', () => {
    const result = calculation();
    result.billingPeriods[0].costs.gross.periodic.total = '0';
    result.reportSummary = {costs: {gross: {total: '0', periodic: {total: '0'}}}, fixedChargesComplete: true};
    const wrapper = summary(result);
    expect(wrapper.find('button').exists()).toBe(false);
    expect(wrapper.text()).not.toContain('Expand the selection');
  });
});
