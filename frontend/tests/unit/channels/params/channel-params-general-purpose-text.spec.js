import {mount} from '@vue/test-utils';
import ChannelParamsGeneralPurposeText from '@/channels/params/channel-params-general-purpose-text.vue';

vi.mock('@/common/gui/toggler.vue', () => ({
  default: {
    name: 'Toggler',
    compatConfig: {COMPONENT_V_MODEL: false},
    props: ['modelValue'],
    emits: ['update:modelValue'],
    template: '<input type="checkbox" />',
  },
}));

vi.mock('@/common/number-input.vue', () => ({
  default: {
    name: 'NumberInput',
    compatConfig: {COMPONENT_V_MODEL: false},
    props: ['modelValue'],
    emits: ['change', 'update:modelValue'],
    template: '<input type="number" />',
  },
}));

describe('ChannelParamsGeneralPurposeText', () => {
  it('emits change on toggler and number input updates', async () => {
    const channel = {config: {keepHistory: true, refreshIntervalMs: 0}};
    const wrapper = mount(ChannelParamsGeneralPurposeText, {
      props: {channel},
    });

    await wrapper.findComponent({name: 'Toggler'}).vm.$emit('update:modelValue', false);
    await wrapper.findComponent({name: 'NumberInput'}).vm.$emit('change');

    expect(wrapper.emitted('change')).toHaveLength(2);
  });
});
