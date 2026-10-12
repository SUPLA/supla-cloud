import {describe, expect, it} from 'vitest';
import {mount} from '@vue/test-utils';
import EnergyCostBillingHistory from '@/channels/energy-cost/energy-cost-billing-history.vue';
import {timelineError} from '@/channels/energy-cost/energy-cost-history';
import {validateCostPlanDraft} from '@/channels/energy-cost/energy-cost-plan-draft';

const initial = () => ({timezone: 'Europe/Warsaw', billingCycles: [{anchor: '2026-01-15', length: 1, unit: 'MONTH'}], periods: []});
const editor = () => mount(EnergyCostBillingHistory, {props: {configuration: initial(), errors: []}});
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
