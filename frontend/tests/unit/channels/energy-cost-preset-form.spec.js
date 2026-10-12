import {describe, expect, it} from 'vitest';
import {mount} from '@vue/test-utils';
import {createI18n} from 'vue-i18n';
import EnergyCostPresetForm from '@/channels/energy-cost/energy-cost-preset-form.vue';

describe('preset price-period customization', () => {
  it('shows all five price periods and overrides and resets their prices independently', async () => {
    const periods = Array.from({length: 5}, (_, index) => ({
      validFrom: index ? `${2022 + index}-01-01T00:00:00+01:00` : null,
      validTo: index < 4 ? `${2023 + index}-01-01T00:00:00+01:00` : null,
      rate: {value: (0.5 + index / 10).toFixed(4)},
    }));
    const preset = {
      document: {
        timezone: 'Europe/Warsaw',
        billingDefinitionTemplate: {components: [{id: 'energy', pricePeriods: periods}]},
        inputs: periods.map((_, index) => ({
          id: `price-${index}`,
          label: `Energy price (${2022 + index})`,
          type: 'DECIMAL',
          unit: 'PLN/kWh',
          targets: [`/components/0/pricePeriods/${index}/rate/value`],
        })),
      },
    };
    const component = {kind: 'ENERGY_PURCHASE', componentId: 'energy', presetId: 'g11', values: {}};
    const wrapper = mount(EnergyCostPresetForm, {
      props: {component, preset, errors: {}},
      global: {plugins: [createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})]},
    });
    const rows = wrapper.findAll('.energy-cost-price-period');
    expect(rows).toHaveLength(5);
    for (const [index, row] of rows.entries()) {
      expect(row.get('.energy-cost-preset-input').isVisible()).toBe(true);
      expect(row.text()).toContain(`Energy price (${2022 + index})`);
      expect(row.text()).toContain(`${periods[index].rate.value} PLN/kWh`);
      const override = row.findAll('button').find((button) => button.text() === 'Override');
      expect(override.isVisible()).toBe(true);
      await override.trigger('click');
      await wrapper.setProps({component: {...component, values: wrapper.emitted('update:values').at(-1)[0]}});
      await row.get('input').setValue(`1,${index}`);
      await wrapper.setProps({component: {...component, values: wrapper.emitted('update:values').at(-1)[0]}});
    }
    expect(wrapper.props('component').values).toEqual({'price-0': '1.0', 'price-1': '1.1', 'price-2': '1.2', 'price-3': '1.3', 'price-4': '1.4'});
    const reset = rows[2].findAll('button').find((button) => button.text() === 'Use tariff value');
    await reset.trigger('click');
    await wrapper.setProps({component: {...component, values: wrapper.emitted('update:values').at(-1)[0]}});
    expect(wrapper.props('component').values).toEqual({'price-0': '1.0', 'price-1': '1.1', 'price-3': '1.3', 'price-4': '1.4'});
    expect(rows[2].text()).toContain('0.7000 PLN/kWh');
    expect(component.values).toEqual({});
    expect(preset.document.billingDefinitionTemplate.components[0].pricePeriods).toEqual(periods);
  });
});
