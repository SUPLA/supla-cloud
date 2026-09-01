import {mount} from '@vue/test-utils';
import ReactionConditionText from '@/channels/reactions/params/reaction-condition-text.vue';

describe('ReactionConditionText', () => {
  it('builds and updates text trigger model', async () => {
    const wrapper = mount(ReactionConditionText, {
      props: {
        modelValue: undefined,
        field: 'value',
        operators: ['eq', 'ne'],
      },
      global: {
        stubs: {ReactionConditionDuration: true},
      },
    });

    wrapper.vm.threshold = 'hello';
    wrapper.vm.updateModel();
    await wrapper.vm.$nextTick();
    let emitted = wrapper.emitted('update:modelValue');
    expect(emitted.at(-1)[0]).toEqual({on_change_to: {eq: 'hello', name: 'value', duration_sec: 0}});

    wrapper.vm.nextOperator();
    await wrapper.vm.$nextTick();
    emitted = wrapper.emitted('update:modelValue');
    expect(emitted.at(-1)[0]).toEqual({on_change_to: {ne: 'hello', name: 'value', duration_sec: 0}});

    wrapper.vm.threshold = '';
    wrapper.vm.updateModel();
    await wrapper.vm.$nextTick();
    emitted = wrapper.emitted('update:modelValue');
    expect(emitted.at(-1)[0]).toBeUndefined();
  });
});
