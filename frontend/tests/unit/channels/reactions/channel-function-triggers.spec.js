import ChannelFunction from '@/common/enums/channel-function';
import {getTriggerDefinitionsForChannel, reactionTriggerCaption} from '@/channels/reactions/channel-function-triggers';
import {mount} from '@vue/test-utils';
import ReactionConditionThreshold from '@/channels/reactions/params/reaction-condition-threshold.vue';

describe('ChannelFunctionTriggers', () => {
  it.each([ChannelFunction.CONTAINER, ChannelFunction.SEPTIC_TANK, ChannelFunction.WATER_TANK])(
    'renders fill-level reaction thresholds with unset and malformed slots for %s',
    (functionId) => {
      const subject = {
        functionId,
        config: {
          levelSensors: [null, {channelId: 101, fillLevel: 75}, {channelId: null, fillLevel: 25}, false, 'bad', {channelId: null}],
        },
      };
      const definition = getTriggerDefinitionsForChannel(subject).find((def) => def.component === ReactionConditionThreshold);
      expect(definition.props.availableValues(subject)).toEqual([0, 25, 75]);
      const wrapper = mount(ReactionConditionThreshold, {
        props: {...definition.props, subject, modelValue: {on_change_to: {eq: 75}}},
        global: {stubs: {ReactionConditionDuration: true}},
      });
      expect(wrapper.vm.valuesForDropdown).toEqual([0, 25, 75]);
      wrapper.unmount();
    }
  );

  it('keeps continuous fill-level reactions independent of sensor slots', () => {
    const subject = {functionId: ChannelFunction.CONTAINER, config: {levelSensors: [null], fillLevelReportingInFullRange: true}};
    const definition = getTriggerDefinitionsForChannel(subject).find((def) => def.component === ReactionConditionThreshold);
    expect(definition.props.availableValues(subject)).toBeUndefined();
  });

  describe('channelFunctionTriggerCaption', () => {
    const tests = [
      ['When the temperature will be = 20°C', ChannelFunction.THERMOMETER, {on_change_to: {eq: 20, name: 'temperature'}}],
      ['When the humidity will be = 20%', ChannelFunction.HUMIDITY, {on_change_to: {eq: 20, name: 'humidity'}}],
      ['When the temperature will be ≠ 30°C', ChannelFunction.HUMIDITYANDTEMPERATURE, {on_change_to: {ne: 30, name: 'temperature'}}],
      ['When the humidity will be ≠ 30%', ChannelFunction.HUMIDITYANDTEMPERATURE, {on_change_to: {ne: 30, name: 'humidity'}}],
      ['When the temperature changes', ChannelFunction.THERMOMETER, {on_change: {name: 'temperature'}}],
      ['When the garage door will be opened', ChannelFunction.OPENINGSENSOR_GARAGEDOOR, {on_change_to: {eq: 'open'}}],
      ['When the garage door will be opened or closed', ChannelFunction.OPENINGSENSOR_GARAGEDOOR, {on_change: {}}],
      ['When the device is turned off', ChannelFunction.LIGHTSWITCH, {on_change_to: {eq: 'off'}}],
      ['When the voltage will be > 230V', ChannelFunction.ELECTRICITYMETER, {on_change_to: {gt: 230, name: 'voltage1'}}],
      ['When the condition is met', ChannelFunction.HUMIDITY, {}],
    ];

    it.each(tests)('humanizes trigger to %p', (expectedText, functionId, trigger) => {
      const reaction = {
        owningChannel: {functionId, config: {}},
        trigger,
      };
      const humanized = reactionTriggerCaption(reaction, {
        $t: (t, p = {}) => {
          return t.replace('{field}', p.field).replace('{value}', p.value).replace('{operator}', p.operator);
        },
      });
      expect(humanized).toEqual(expectedText);
    });
  });
});
