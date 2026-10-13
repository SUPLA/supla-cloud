import {describe, expect, it} from 'vitest';
import {mount} from '@vue/test-utils';
import {createI18n} from 'vue-i18n';
import RangeSelector from '@/channels/energy-cost/energy-cost-range-selector.vue';

function setup() {
  return mount(RangeSelector, {
    props: {
      modelValue: {from: '2026-09-01T00:00:00Z', to: '2026-09-03T00:00:00Z'},
      timezone: 'UTC',
    },
    global: {
      plugins: [createI18n({legacy: false, locale: 'en', missingWarn: false, fallbackWarn: false})],
      stubs: {SimpleDropdown: {template: '<button type="button" @click="$emit(\'input\', \'today\')"><slot name="button" /></button>'}},
    },
  });
}

describe('energy cost range selector', () => {
  it('applies manually edited dates only after clicking Apply', async () => {
    const wrapper = setup();
    const dates = wrapper.findAll('input[type="date"]');
    await dates[0].setValue('2026-09-05');
    await dates[1].setValue('2026-09-07');
    expect(wrapper.emitted('update:modelValue')).toBeUndefined();
    await wrapper
      .findAll('button')
      .find((button) => button.text() === 'Apply')
      .trigger('click');
    expect(wrapper.emitted('update:modelValue')).toEqual([[{from: '2026-09-05T00:00:00.000Z', to: '2026-09-08T00:00:00.000Z'}]]);
  });
  it('applies predefined ranges immediately', async () => {
    const wrapper = setup();
    await wrapper.findAll('button')[0].trigger('click');
    expect(wrapper.emitted('update:modelValue')).toHaveLength(1);
  });
});
