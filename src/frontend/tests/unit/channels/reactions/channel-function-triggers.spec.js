import ChannelFunction from "@/common/enums/channel-function";
import {ChannelFunctionTriggers, reactionTriggerCaption} from "@/channels/reactions/channel-function-triggers";

describe('ChannelFunctionTriggers', () => {
    it('defines triggers for all functions', () => {
        const functionsToSkip = [
            ChannelFunction.UNSUPPORTED,
            ChannelFunction.NONE,
            ChannelFunction.SCENE,
            ChannelFunction.SCHEDULE,
            ChannelFunction.NOTIFICATION,
            ChannelFunction.CONTROLLINGTHEGATEWAYLOCK,
            ChannelFunction.CONTROLLINGTHEGATE,
            ChannelFunction.CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction.CONTROLLINGTHEDOORLOCK,
            ChannelFunction.WEATHER_STATION,
            ChannelFunction.THERMOSTAT,
            ChannelFunction.VALVEPERCENTAGE,
            ChannelFunction.ACTION_TRIGGER,
            ChannelFunction.DIGIGLASS_HORIZONTAL,
            ChannelFunction.DIGIGLASS_VERTICAL,
            ChannelFunction.CONTAINER_LEVEL_SENSOR,
        ];
        for (const fncName in ChannelFunction) {
            const fncId = ChannelFunction[fncName];
            if (!functionsToSkip.includes(fncId)) {
                expect(ChannelFunctionTriggers).toHaveProperty(fncId.toString());
            }
        }
        for (const fncId of functionsToSkip) {
            expect(ChannelFunctionTriggers).not.toHaveProperty(fncId.toString());
        }
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
                owningChannel: {functionId},
                trigger,
            };
            const humanized = reactionTriggerCaption(reaction, {
                $t: (t, p = {}) => {
                    return t.replace('{field}', p.field).replace('{value}', p.value).replace('{operator}', p.operator);
                }
            });
            expect(humanized).toEqual(expectedText);
        });
    });
});
