import {afterEach, describe, expect, it, vi} from 'vitest';
import {mount} from '@vue/test-utils';
import {createI18n} from 'vue-i18n';
import EnergyCostBillingHistory from '@/channels/energy-cost/energy-cost-billing-history.vue';
import {timelineError} from '@/channels/energy-cost/energy-cost-history';
import {validateCostPlanDraft} from '@/channels/energy-cost/energy-cost-plan-draft';
import {serializeConfiguration} from '@/channels/energy-cost/energy-cost-plan-utils';

const initial = () => ({timezone: 'Europe/Warsaw', billingCycles: [{anchor: '2026-01-15', length: 1, unit: 'MONTH'}], periods: []});
const editor = () =>
  mount(EnergyCostBillingHistory, {
    props: {configuration: initial(), errors: []},
    global: {plugins: [createI18n({legacy: false, locale: 'pl', missingWarn: false, fallbackWarn: false})]},
  });
afterEach(() => vi.useRealTimers());
const button = (wrapper, label) => wrapper.findAll('button').find((entry) => entry.text() === label);
async function applyUpdate(wrapper) {
  const cycles = wrapper.emitted('update:cycles').at(-1)[0];
  await wrapper.setProps({configuration: {...wrapper.props('configuration'), billingCycles: cycles}});
  return cycles;
}
async function add(wrapper, date) {
  await button(wrapper, 'Add billing period').trigger('click');
  await wrapper.get('#new-billing-period-date').setValue(date);
  await button(wrapper, 'Add billing period').trigger('click');
  return applyUpdate(wrapper);
}

describe('billing transition history', () => {
  it('hides effective dates for one period while keeping its anchor editable', () => {
    const wrapper = editor();
    expect(wrapper.findAll('label').map((label) => label.text())).toEqual(['Anchor date', 'Length', 'Unit']);
    expect(wrapper.get('#billing-anchor-0').element.value).toBe('2026-01-15');
    expect(wrapper.find('h5').exists()).toBe(false);
  });

  it('sends an edited two-month billing length as a JSON number rather than a form string', async () => {
    const wrapper = editor();
    await wrapper.get('#billing-length-0').setValue(2);
    await applyUpdate(wrapper);
    const draft = wrapper.props('configuration');
    expect(validateCostPlanDraft(draft, {}).billingCycles[0]).toEqual({});
    const document = JSON.parse(JSON.stringify(serializeConfiguration(draft)));
    expect(document.billingCycles[0]).toEqual({anchor: '2026-01-15', length: 2, unit: 'MONTH'});
    expect(draft.billingCycles[0].length).toBe('2');
  });

  it('adds only a transition start and derives the preceding end in the plan timezone', async () => {
    const wrapper = editor();
    const cycles = await add(wrapper, '2026-05-01');
    expect(cycles).toEqual([
      {...initial().billingCycles[0], validFrom: null, validTo: '2026-05-01T00:00:00+02:00'},
      {...initial().billingCycles[0], validFrom: '2026-05-01T00:00:00+02:00', validTo: null},
    ]);
    expect(wrapper.findAll('label').filter((label) => label.text() === 'From date')).toHaveLength(1);
    expect(wrapper.text()).not.toContain('To date');
    expect(wrapper.props('configuration').billingCycles[0].anchor).toBe('2026-01-15');
    expect(wrapper.findAll('.billing-history-entry')).toHaveLength(2);
    expect(wrapper.findAll('.billing-history-entry')[1].get('#billing-from-1').element.value).toBe('2026-05-01');
  });

  it('defaults each new billing transition to today in the plan timezone and cancels without adding an entry', async () => {
    vi.useFakeTimers({toFake: ['Date']});
    vi.setSystemTime(new Date('2026-03-31T22:30:00Z'));
    const wrapper = editor();
    await button(wrapper, 'Add billing period').trigger('click');
    expect(wrapper.get('label[for="new-billing-period-date"]').text()).toBe('When the billing period changes?');
    expect(wrapper.get('#new-billing-period-date').element.value).toBe('2026-04-01');
    expect(wrapper.get('#new-billing-period-date').attributes('lang')).toBe('pl');
    await wrapper.get('#new-billing-period-date').setValue('2026-05-01');
    await button(wrapper, 'Cancel').trigger('click');
    expect(wrapper.emitted('update:cycles')).toBeUndefined();
    expect(wrapper.findAll('.billing-history-entry')).toHaveLength(1);
    vi.setSystemTime(new Date('2026-04-02T22:30:00Z'));
    await button(wrapper, 'Add billing period').trigger('click');
    expect(wrapper.get('#new-billing-period-date').element.value).toBe('2026-04-03');
    await button(wrapper, 'Add billing period').trigger('click');
    const cycles = await applyUpdate(wrapper);
    expect(cycles[1].validFrom).toBe('2026-04-03T00:00:00+02:00');
    expect(wrapper.findAll('.billing-history-entry')).toHaveLength(2);
    expect(wrapper.find('#new-billing-period-date').exists()).toBe(false);
  });

  it('edits a new billing entry independently of the previous entry', async () => {
    const wrapper = editor();
    await add(wrapper, '2026-05-01');
    await wrapper.get('#billing-anchor-1').setValue('2026-05-15');
    await applyUpdate(wrapper);
    await wrapper.get('#billing-length-1').setValue(2);
    await applyUpdate(wrapper);
    await wrapper.get('#billing-unit-1').setValue('WEEK');
    const cycles = await applyUpdate(wrapper);
    expect(cycles[0]).toEqual({...initial().billingCycles[0], validFrom: null, validTo: '2026-05-01T00:00:00+02:00'});
    expect(cycles[1]).toMatchObject({anchor: '2026-05-15', length: '2', unit: 'WEEK'});
    expect(timelineError(cycles)).toBe('');
  });

  it('can remove the first billing period, extending the following settings back to the start', async () => {
    const wrapper = editor();
    expect(button(wrapper, 'Remove billing period')).toBeUndefined();
    await add(wrapper, '2026-05-01');
    await wrapper.get('#billing-anchor-1').setValue('2026-05-15');
    await applyUpdate(wrapper);
    await wrapper.get('#billing-length-1').setValue(2);
    await applyUpdate(wrapper);
    await button(wrapper, 'Remove billing period').trigger('click');
    const cycles = await applyUpdate(wrapper);
    expect(cycles).toEqual([{anchor: '2026-05-15', length: '2', unit: 'MONTH', validFrom: null, validTo: null}]);
    expect(timelineError(cycles)).toBe('');
    expect(button(wrapper, 'Remove billing period')).toBeUndefined();
  });

  it('inserts and removes transitions without changing unrelated cycle definitions', async () => {
    const wrapper = editor();
    await add(wrapper, '2026-03-01');
    await add(wrapper, '2026-09-01');
    const cycles = await add(wrapper, '2026-05-01');
    expect(cycles.map((cycle) => cycle.validFrom?.slice(0, 10))).toEqual([undefined, '2026-03-01', '2026-05-01', '2026-09-01']);
    await button(wrapper, 'Remove billing period').trigger('click');
    const remaining = await applyUpdate(wrapper);
    expect(timelineError(remaining)).toBe('');
    expect(remaining[0].validTo).toBe(remaining[1].validFrom);
    expect(remaining.every((cycle) => cycle.anchor === '2026-01-15' && cycle.length === 1 && cycle.unit === 'MONTH')).toBe(true);
  });

  it('updates both sides of a boundary and rejects empty or duplicate transition dates', async () => {
    const wrapper = editor();
    await add(wrapper, '2026-05-01');
    await add(wrapper, '2026-09-01');
    const from = wrapper.findAll('input[type="date"]').find((input) => input.element.value === '2026-05-01');
    await from.setValue('2026-06-01');
    let cycles = await applyUpdate(wrapper);
    expect(cycles[0].validTo).toBe('2026-06-01T00:00:00+02:00');
    expect(cycles[2].validFrom).toBe('2026-09-01T00:00:00+02:00');
    await from.setValue('2026-09-01');
    cycles = await applyUpdate(wrapper);
    expect(timelineError(cycles)).toMatch(/unique/);
    expect(validateCostPlanDraft(wrapper.props('configuration'), {}).billingCycles[0].timeline).toBeTruthy();
    await from.setValue('');
    cycles = await applyUpdate(wrapper);
    expect(timelineError(cycles)).toMatch(/valid transition date/);
  });
});
