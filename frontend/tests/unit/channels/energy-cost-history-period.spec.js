import {afterEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {createI18n} from 'vue-i18n';
import EnergyCostHistoryPeriod from '@/channels/energy-cost/energy-cost-history-period.vue';
import EnergyCostTariffPicker from '@/channels/energy-cost/energy-cost-tariff-picker.vue';
import EnergyCostCustomComposer from '@/channels/energy-cost/energy-cost-custom-composer.vue';
import {useEnergyCostStore} from '@/stores/energy-cost-store';

afterEach(() => vi.restoreAllMocks());
const button = (wrapper, label) => wrapper.findAll('button').find((entry) => entry.text() === label);

function editor(index = 1) {
  const pinia = createPinia();
  setActivePinia(pinia);
  const store = useEnergyCostStore();
  const components = [{kind: 'ENERGY_PURCHASE', componentId: 'energy', presetId: 'g12', values: {}}];
  store.tariffs = [{id: 'starter', label: 'Readable G12 tariff', components}];
  store.tariffDetailsById = {starter: {label: 'Readable G12 tariff', components}};
  store.presetDetailsById = Object.fromEntries(
    ['g11', 'g12'].map((id) => [
      id,
      {
        document: {
          currency: 'PLN',
          timezone: 'Europe/Warsaw',
          taxContext: {jurisdiction: 'PL', customerClass: 'HOUSEHOLD'},
          billingDefinitionTemplate: {components: [{id: 'energy', kind: 'ENERGY_PURCHASE'}]},
        },
      },
    ])
  );
  const period = {
    validFrom: index ? '2026-05-01T00:00:00+02:00' : null,
    validTo: null,
    components: [{...components[0], presetId: 'g11', values: {price: '0.99'}}],
  };
  const configuration = {
    currency: 'PLN',
    timezone: 'Europe/Warsaw',
    periods: index ? [{...period, validFrom: null, validTo: period.validFrom}, period] : [period],
  };
  const wrapper = mount(EnergyCostHistoryPeriod, {
    props: {period, index, configuration, errors: {}},
    global: {plugins: [pinia, createI18n({legacy: false, locale: 'pl', missingWarn: false, fallbackWarn: false})]},
  });
  return {wrapper, period, components, store};
}
async function select(wrapper) {
  wrapper.findComponent(EnergyCostTariffPicker).vm.$emit('update:modelValue', 'starter');
  await flushPromises();
}

describe('individual tariff period editing', () => {
  it('shows localized information and price customization on the left, with actions on the right', async () => {
    const {wrapper, store, period} = editor();
    store.presets = [
      {id: 'g11', provider: 'TAURON Sprzedaż', tariffGroup: 'G11'},
      {id: 'distribution', operator: 'Stoen Operator', tariffGroup: 'G11'},
    ];
    await wrapper.setProps({
      period: {
        ...period,
        components: [...period.components, {kind: 'DISTRIBUTION_VARIABLE', presetId: 'distribution', componentId: 'distribution', values: {}}],
      },
    });
    expect(wrapper.get('.col-sm-8').text()).toContain('Custom tariff configuration');
    expect(wrapper.get('.col-sm-8').text()).toContain('From date: 01.05.2026');
    expect(wrapper.get('.col-sm-8').text()).toContain('Electricity supply: TAURON Sprzedaż · G11');
    expect(wrapper.get('.col-sm-8').text()).toContain('Distribution: Stoen Operator · G11');
    expect(wrapper.get('.col-sm-8').text()).not.toContain('Customize prices');
    expect(wrapper.get('.period-actions').text()).toContain('Customize prices');
    expect(wrapper.get('.period-actions').text()).toContain('Change tariff');
    expect(wrapper.get('.period-actions').text()).toContain('Remove period');
    expect(wrapper.find('input[type="date"]').exists()).toBe(false);
    expect(wrapper.text()).not.toContain('To date');
  });

  it('shows the inclusive end date and permits removal of the first of multiple periods', async () => {
    const {wrapper} = editor();
    const configuration = wrapper.props('configuration');
    await wrapper.setProps({index: 0, period: configuration.periods[0]});
    expect(wrapper.get('.col-sm-8').text()).toContain('To date: 30.04.2026');
    expect(wrapper.get('.col-sm-8').text()).not.toContain('From date');
    await button(wrapper, 'Remove period').trigger('click');
    expect(wrapper.emitted('remove')).toHaveLength(1);
  });

  it('stages tariff and date changes, then warns and applies them only on OK', async () => {
    const {wrapper, period, components} = editor();
    const confirmation = vi.spyOn(window, 'confirm').mockReturnValue(false);
    await button(wrapper, 'Change tariff').trigger('click');
    expect(wrapper.find('.col-sm-8').exists()).toBe(false);
    expect(wrapper.find('.period-actions').exists()).toBe(false);
    await select(wrapper);
    await wrapper.get('input[type="date"]').setValue('2026-06-01');
    expect(wrapper.emitted('update:period')).toBeUndefined();
    expect(confirmation).not.toHaveBeenCalled();
    await button(wrapper, 'OK').trigger('click');
    expect(confirmation).toHaveBeenCalledWith('Changing the tariff will reset custom pricing settings for this period.');
    expect(wrapper.emitted('update:period')).toBeUndefined();
    confirmation.mockReturnValue(true);
    await button(wrapper, 'OK').trigger('click');
    const updated = {...period, validFrom: '2026-06-01T00:00:00+02:00', components};
    expect(wrapper.emitted('update:period')[0][0]).toEqual(updated);
    expect(period.components[0].values).toEqual({price: '0.99'});
    await wrapper.setProps({period: updated});
    expect(wrapper.get('.col-sm-8').text()).toContain('Readable G12 tariff');
    expect(wrapper.get('.col-sm-8').text()).toContain('From date: 01.06.2026');
    expect(wrapper.findComponent(EnergyCostTariffPicker).exists()).toBe(false);
  });

  it('discards tariff and date drafts on Cancel, including composed custom tariffs', async () => {
    const {wrapper, period, components} = editor();
    await button(wrapper, 'Change tariff').trigger('click');
    await select(wrapper);
    await wrapper.get('input[type="date"]').setValue('2026-06-01');
    await button(wrapper, 'Compose custom tariff').trigger('click');
    wrapper.findComponent(EnergyCostCustomComposer).vm.$emit('update:period', {...period, components});
    await button(wrapper, 'Cancel').trigger('click');
    expect(wrapper.emitted('update:period')).toBeUndefined();
    expect(wrapper.text()).toContain('From date: 01.05.2026');
    await button(wrapper, 'Change tariff').trigger('click');
    expect(wrapper.get('input[type="date"]').element.value).toBe('2026-05-01');
    await button(wrapper, 'Compose custom tariff').trigger('click');
    expect(wrapper.findComponent(EnergyCostCustomComposer).props('period')).toEqual(period);
  });

  it('rejects invalid transition dates without applying the draft', async () => {
    const {wrapper} = editor();
    await button(wrapper, 'Change tariff').trigger('click');
    await wrapper.get('input[type="date"]').setValue('');
    await button(wrapper, 'OK').trigger('click');
    expect(wrapper.text()).toContain('Choose a valid transition date.');
    expect(wrapper.emitted('update:period')).toBeUndefined();
    await wrapper.get('input[type="date"]').setValue('2026-06-01');
    await button(wrapper, 'OK').trigger('click');
    expect(wrapper.emitted('update:period')[0][0].validFrom).toBe('2026-06-01T00:00:00+02:00');
  });

  it('omits the start date and removal action for the first period', async () => {
    const {wrapper} = editor(0);
    expect(wrapper.text()).not.toContain('From date');
    expect(button(wrapper, 'Remove period')).toBeUndefined();
    await button(wrapper, 'Change tariff').trigger('click');
    expect(wrapper.find('input[type="date"]').exists()).toBe(false);
    await button(wrapper, 'Cancel').trigger('click');
    await button(wrapper, 'Customize prices').trigger('click');
    expect(wrapper.find('input[type="date"]').exists()).toBe(false);
    expect(wrapper.emitted('update:period')).toBeUndefined();
  });

  it('ignores a tariff request completing after the draft was cancelled', async () => {
    const {wrapper, store, period} = editor();
    let resolve;
    vi.spyOn(store, 'fetchTariff').mockReturnValue(
      new Promise((done) => {
        resolve = done;
      })
    );
    await button(wrapper, 'Change tariff').trigger('click');
    wrapper.findComponent(EnergyCostTariffPicker).vm.$emit('update:modelValue', 'starter');
    await button(wrapper, 'Cancel').trigger('click');
    await button(wrapper, 'Change tariff').trigger('click');
    resolve(store.tariffDetailsById.starter);
    await flushPromises();
    await button(wrapper, 'Compose custom tariff').trigger('click');
    expect(wrapper.findComponent(EnergyCostCustomComposer).props('period')).toEqual(period);
    expect(wrapper.emitted('update:period')).toBeUndefined();
  });
});
