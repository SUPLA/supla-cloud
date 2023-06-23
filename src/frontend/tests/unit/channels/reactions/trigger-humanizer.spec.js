import ChannelFunction from "@/common/enums/channel-function";
import {triggerHumanizer} from "@/channels/reactions/trigger-humanizer";

describe('triggerHumanizer', () => {
    const tests = [
        ['When the temperature will be equal to 20', ChannelFunction.THERMOMETER, {on_change_to: {eq: 20}}],
        ['When the humidity will be different than 30', ChannelFunction.HUMIDITYANDTEMPERATURE, {on_change_to: {ne: 30, name: 'humidity'}}],
        ['When the garage door will be opened', ChannelFunction.OPENINGSENSOR_GARAGEDOOR, {on_change_to: {eq: 'open'}}],
        ['When the device will be turned off', ChannelFunction.LIGHTSWITCH, {on_change_to: {eq: 'off'}}],
        ['When the temperature will be lower than 20', ChannelFunction.THERMOMETER, {on_change_to: {lt: 20}}],
        ['When the temperature will be lower than 20', ChannelFunction.THERMOMETER, {on_change_to: {lt: 20}}],
        ['When the temperature will be lower than or equal to 20', ChannelFunction.THERMOMETER, {on_change_to: {le: 20}}],
        ['When the humidity will be greater than or equal to 20', ChannelFunction.HUMIDITY, {on_change_to: {ge: 20}}],
        ['When the voltage of phase 1 will be greater than 230', ChannelFunction.ELECTRICITYMETER, {
            on_change_to: {
                gt: 230,
                name: 'voltage1'
            }
        }],
        ['When the condition is met', ChannelFunction.HUMIDITY, {}],
    ];

    it.each(tests)('humanizes trigger to %p', (expectedText, channelFunction, trigger) => {
        const humanized = triggerHumanizer(channelFunction, trigger, {
            $t: (t, p = {}) => {
                return t.replace('{field}', p.field).replace('{value}', p.value);
            }
        });
        expect(humanized).toEqual(expectedText);
    });
});
