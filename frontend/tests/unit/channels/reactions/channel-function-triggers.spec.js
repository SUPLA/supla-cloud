import ChannelFunction from '@/common/enums/channel-function';
import {getTriggerDefinitionsForChannel, reactionTriggerCaption} from '@/channels/reactions/channel-function-triggers';

describe('ChannelFunctionTriggers', () => {
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
      ['When the text value will be = hello', ChannelFunction.GENERAL_PURPOSE_TEXT, {on_change_to: {eq: 'hello'}}],
      ["When the text value doesn't change", ChannelFunction.GENERAL_PURPOSE_TEXT, {on_change: {duration_sec: 30}}],
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

  describe('getTriggerDefinitionsForChannel', () => {
    it('adds delayed no-change trigger for general purpose text', () => {
      const definitions = getTriggerDefinitionsForChannel({functionId: ChannelFunction.GENERAL_PURPOSE_TEXT, config: {}});
      const definition = definitions.find((trigger) => trigger.caption()?.includes("doesn't change"));

      expect(definition).toBeDefined();
      expect(definition.def()).toEqual({on_change: {duration_sec: 10}});
      expect(definition.test({on_change: {duration_sec: 5}})).toBe(true);
      expect(definition.test({on_change: {}})).toBe(false);
    });
  });
});
