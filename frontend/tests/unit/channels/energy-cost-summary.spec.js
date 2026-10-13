import {describe, expect, it} from 'vitest';
import {mount} from '@vue/test-utils';
import {createI18n} from 'vue-i18n';
import Summary from '@/channels/energy-cost/energy-cost-summary.vue';
import {calculation, monthlyCalculation} from './energy-cost-comparison-fixtures';

function summary(result) {
  return mount(Summary, {
    props: {result, timezone: 'Europe/Warsaw'},
    global: {plugins: [createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})]},
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
  it('shows selected unaligned dates and requests alignment without presenting usage costs as a full bill', async () => {
    const wrapper = summary(calculation());
    const total = wrapper.get('.energy-cost-total-expenses');
    expect(total.get('strong').text()).toBe('—');
    expect(total.text()).toContain('01 Sep 2026, 02:00');
    expect(total.text()).toContain('The selected dates are not aligned with billing periods.');
    expect(total.text()).toContain('Align the dates to calculate total expenses including fixed charges.');
    expect(wrapper.get('.energy-cost-summary').text()).toContain('100.00 PLN');
    await total.get('button').trigger('click');
    expect(wrapper.emitted('align-billing-periods')).toHaveLength(1);
  });
  it('keeps a valid total for a plan without fixed charges while still offering date alignment', () => {
    const result = calculation();
    result.costs.gross.periodic.total = '0';
    result.costs.gross.total = '100';
    const wrapper = summary(result);
    expect(wrapper.get('.energy-cost-total-expenses strong').text()).toBe('100.00 PLN');
    expect(wrapper.find('.energy-cost-total-alignment-hint button').exists()).toBe(true);
  });
  it('does not invent a missing total', () => {
    const result = monthlyCalculation();
    delete result.costs.gross.total;
    const wrapper = summary(result);
    expect(wrapper.get('.energy-cost-total-expenses strong').text()).toBe('—');
    expect(wrapper.text()).toContain('Total expenses are unavailable for this period.');
  });
});
