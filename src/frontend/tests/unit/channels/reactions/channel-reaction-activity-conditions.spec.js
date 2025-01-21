import {shallowMount} from '@vue/test-utils'
import ChannelReactionActivityConditions from "@/activity/channel-reaction-activity-conditions.vue";

describe('ChannelReactionActivityConditions', () => {
    describe('timesToConditions', () => {
        const tests = [
            [[], []],
            [[[]], []],
            [[[-60, 120]], [[{afterSunrise: 0}]]],
            [[[-120, -50]], [[{beforeSunrise: 10}]]],
            [[[-60, 60]], [[{afterSunrise: 0}, {beforeSunset: 0}]]],
            [[[-70, 70]], [[{afterSunrise: -10}, {beforeSunset: 10}]]],
            [[[-1, 1]], [[{afterSunrise: 59}, {beforeSunset: -59}]]],
            [[[-70, -50], [50, 70]], [[{afterSunrise: -10}, {beforeSunrise: 10}], [{afterSunset: -10}, {beforeSunset: 10}]]],
            [[[-120, -50], [50, 120]], [[{beforeSunrise: 10}], [{afterSunset: -10}]]],
            [[[0, 0]], []],
            [[[60, -60]], [[{afterSunset: 0}], [{beforeSunrise: 0}]]],
            [[[110, -110]], [[{afterSunset: 50}], [{beforeSunrise: -50}]]],
            [[[110, 120]], [[{afterSunset: 50}]]],
            [[[-60, 60], [60, -60]], [[{afterSunrise: 0}, {beforeSunset: 0}], [{afterSunset: 0}], [{beforeSunrise: 0}]]],
            [[[-100, 100], [100, -100]], [[{afterSunrise: -40}, {beforeSunset: 40}], [{afterSunset: 40}], [{beforeSunrise: -40}]]],
        ];

        it.each(tests)('translates timesToConditions', async (times, expectedConditions) => {
            const wrapper = await shallowMount(ChannelReactionActivityConditions);
            const actualConditions = wrapper.vm.timesToConditions(times);
            expect(actualConditions).toEqual(expectedConditions);
        });

        it.each(tests)('translates conditionsToTimes', async (expectedTimes, conditions) => {
            const wrapper = await shallowMount(ChannelReactionActivityConditions);
            const actualTimes = wrapper.vm.conditionsToTimes(conditions);
            if (conditions.length > 0) {
                expect(actualTimes).toEqual(expectedTimes);
            }
        });
    });
});
