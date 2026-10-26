import {mount} from '@vue/test-utils';
import ChannelParamsGeneralPurposeText from '@/channels/params/channel-params-general-purpose-text.vue';

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
  it('omits history settings and emits change on refresh interval updates', async () => {
    const channel = {config: {refreshIntervalMs: 0}};
    const wrapper = mount(ChannelParamsGeneralPurposeText, {
      props: {channel},
    });

    expect(wrapper.findComponent({name: 'Toggler'}).exists()).toBe(false);
    await wrapper.findComponent({name: 'NumberInput'}).vm.$emit('change');

    expect(wrapper.emitted('change')).toHaveLength(1);
  });
});
