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
    global: {
      mocks: {$t: (key, values = {}) => key.replace(/\{(\w+)\}/g, (match, name) => values[name] ?? match)},
      plugins: [pinia, createI18n({legacy: false, locale: 'pl', missingWarn: false, fallbackWarn: false})],
    },
  });
  return {wrapper, period, components, store};
}
async function select(wrapper) {
  wrapper.findComponent(EnergyCostTariffPicker).vm.$emit('update:modelValue', 'starter');
  await flushPromises();
}

function pricingEditor() {
  const result = editor();
  result.store.presets = [
    {id: 'g11', provider: 'TAURON Sprzedaż', tariffGroup: 'G11', components: [{componentId: 'energy', kind: 'ENERGY_PURCHASE', label: 'Electricity supply'}]},
  ];
  const document = result.store.presetDetailsById.g11.document;
  document.billingDefinitionTemplate.components[0].pricePeriods = [
    {validFrom: null, validTo: '2026-01-01', rates: {DAY: '0.45', NIGHT: '0.30'}},
    {validFrom: '2026-01-01', validTo: null, rates: {DAY: '0.65', NIGHT: '0.40'}},
  ];
  document.inputs = [
    {id: 'oldDay', label: 'Old day rate', type: 'DECIMAL', unit: 'PLN/kWh', targets: ['/components/0/pricePeriods/0/rates/DAY']},
    {id: 'oldNight', label: 'Old night rate', type: 'DECIMAL', unit: 'PLN/kWh', targets: ['/components/0/pricePeriods/0/rates/NIGHT']},
    {id: 'latestDay', label: 'Day rate', type: 'DECIMAL', unit: 'PLN/kWh', targets: [{pointer: '/components/0/pricePeriods/1/rates/DAY'}]},
    {id: 'price', label: 'Night rate', type: 'DECIMAL', unit: 'PLN/kWh', targets: ['/components/0/pricePeriods/1/rates/NIGHT']},
  ];
  return result;
}

describe('individual tariff period editing', () => {
  it('shows localized information on the left, with actions on the right', async () => {
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

  it('opens full-width customization with collapsed latest-price summaries and disables conflicting actions', async () => {
    const {wrapper, period} = pricingEditor();
    await wrapper.setProps({period: {...period, components: [...period.components, {kind: 'SUPPLIER_FIXED', componentId: 'fee', rate: '15', per: 'MONTH'}]}});
    await button(wrapper, 'Customize prices').trigger('click');
    expect(wrapper.get('.energy-cost-plan-period').element.parentElement).toBe(wrapper.element);
    expect(button(wrapper, 'Change tariff').element.disabled).toBe(true);
    expect(button(wrapper, 'Remove period').element.disabled).toBe(true);
    await button(wrapper, 'Change tariff').trigger('click');
    await button(wrapper, 'Remove period').trigger('click');
    expect(wrapper.emitted('remove')).toBeUndefined();
    expect(wrapper.findComponent(EnergyCostTariffPicker).exists()).toBe(false);
    const accordions = wrapper.findAll('details.price-component-accordion');
    expect(accordions).toHaveLength(2);
    expect(accordions.every((entry) => !entry.element.open)).toBe(true);
    const summary = accordions[0].get('summary').text();
    expect(summary).toContain('Electricity supply');
    expect(summary).toContain('Pricing source: TAURON Sprzedaż · G11');
    expect(summary).toContain('Day rate: 0.65 PLN/kWh');
    expect(summary).toContain('Night rate: 0.99 PLN/kWh');
    expect(summary).not.toContain('Old day rate');
    expect(summary).not.toContain('0.45');
    expect(accordions[1].get('summary').text()).toContain('Pricing source: Custom charge');
    expect(accordions[1].get('summary').text()).toContain('Rate: 15 / Month');
    await button(wrapper, 'Customize prices').trigger('click');
    expect(wrapper.find('.energy-cost-plan-period').exists()).toBe(false);
    expect(button(wrapper, 'Change tariff').element.disabled).toBe(false);
    expect(button(wrapper, 'Remove period').element.disabled).toBe(false);
  });

  it('protects energy and variable distribution components while allowing fixed charges to be removed', async () => {
    const {wrapper, period} = pricingEditor();
    const components = [
      ...period.components,
      {kind: 'DISTRIBUTION_VARIABLE', componentId: 'distribution', presetId: 'distribution', values: {}},
      {kind: 'SUPPLIER_FIXED', componentId: 'fee', rate: '15', per: 'MONTH'},
    ];
    await wrapper.setProps({period: {...period, components}});
    await button(wrapper, 'Customize prices').trigger('click');
    const accordions = wrapper.findAll('details.price-component-accordion');
    expect(button(accordions[0], 'Remove component')).toBeUndefined();
    expect(button(accordions[1], 'Remove component')).toBeUndefined();
    expect(button(wrapper, 'Add Energy purchase')).toBeUndefined();
    expect(button(wrapper, 'Add Variable distribution')).toBeUndefined();
    expect(button(wrapper, 'Add Fixed distribution')).toBeDefined();
    expect(button(wrapper, 'Add Supplier fixed charge')).toBeDefined();
    await button(accordions[2], 'Remove component').trigger('click');
    expect(wrapper.emitted('update:period').at(-1)[0].components).toEqual(components.slice(0, 2));
    await wrapper.setProps({period: {...period, components: [components[0]]}});
    expect(button(wrapper, 'Add Energy purchase')).toBeUndefined();
    expect(button(wrapper, 'Add Variable distribution')).toBeDefined();
  });

  it('keeps price overrides and resets editable inside the accordion and updates its summary', async () => {
    const {wrapper, period} = pricingEditor();
    await button(wrapper, 'Customize prices').trigger('click');
    const accordion = wrapper.get('details.price-component-accordion');
    accordion.element.open = true;
    const dayRate = accordion.findAll('.energy-cost-preset-input').find((entry) => entry.get('label').text() === 'Day rate');
    await button(dayRate, 'Override').trigger('click');
    let updated = wrapper.emitted('update:period').at(-1)[0];
    expect(updated.components[0].values).toEqual({price: '0.99', latestDay: '0.65'});
    await wrapper.setProps({period: updated});
    await dayRate.get('input').setValue('0,71');
    updated = wrapper.emitted('update:period').at(-1)[0];
    await wrapper.setProps({period: updated});
    expect(accordion.element.open).toBe(true);
    accordion.element.open = false;
    expect(accordion.get('summary').text()).toContain('Day rate: 0.71 PLN/kWh');
    expect(accordion.get('summary').text()).toContain('Night rate: 0.99 PLN/kWh');
    accordion.element.open = true;
    await button(dayRate, 'Use tariff value').trigger('click');
    updated = wrapper.emitted('update:period').at(-1)[0];
    expect(updated.components[0].values).toEqual({price: '0.99'});
    await wrapper.setProps({period: updated});
    expect(accordion.get('summary').text()).toContain('Day rate: 0.65 PLN/kWh');
    expect(period.components[0].values).toEqual({price: '0.99'});
  });

  it('toggles tariff source information next to the pricing source and refreshes it when the source changes', async () => {
    const {wrapper, store} = pricingEditor();
    vi.spyOn(window, 'confirm').mockReturnValue(true);
    store.presets.push({id: 'g12', provider: 'TAURON Sprzedaż', tariffGroup: 'G12', components: [{componentId: 'energy', kind: 'ENERGY_PURCHASE'}]});
    store.presetDetailsById.g11.document.source = {title: 'Original tariff document', retrievedAt: '2026-01-02', url: 'https://example.com/original'};
    store.presetDetailsById.g12.document.source = {title: 'New tariff document', url: 'https://example.com/new'};
    await button(wrapper, 'Customize prices').trigger('click');
    const accordion = wrapper.get('details.price-component-accordion').element;
    accordion.open = true;
    const toggle = () => wrapper.get('button[aria-label="Tariff details"]');
    expect(toggle().attributes('aria-expanded')).toBe('false');
    expect(wrapper.find('.alert-info').exists()).toBe(false);
    await toggle().trigger('click');
    expect(toggle().attributes('aria-expanded')).toBe('true');
    expect(wrapper.get('.alert-info').text()).toContain('Original tariff document');
    expect(wrapper.get('.alert-info').text()).toContain('Retrieved: 2026-01-02');
    expect(wrapper.get('.alert-info a').attributes('href')).toBe('https://example.com/original');
    await toggle().trigger('click');
    expect(wrapper.find('.alert-info').exists()).toBe(false);
    await toggle().trigger('click');
    await wrapper.get('.energy-cost-preset-picker select').setValue('g12');
    await flushPromises();
    await wrapper.setProps({period: wrapper.emitted('update:period').at(-1)[0]});
    expect(wrapper.get('details.price-component-accordion').element).toBe(accordion);
    expect(accordion.open).toBe(true);
    expect(toggle().attributes('aria-expanded')).toBe('false');
    expect(wrapper.find('.alert-info').exists()).toBe(false);
    await toggle().trigger('click');
    expect(wrapper.get('.alert-info').text()).toContain('New tariff document');
    expect(wrapper.get('.alert-info').text()).not.toContain('Original tariff document');
    expect(wrapper.get('.alert-info a').attributes('href')).toBe('https://example.com/new');
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
