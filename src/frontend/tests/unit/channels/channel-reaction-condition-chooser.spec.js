import {mount} from '@vue/test-utils'
import ChannelFunction from "@/common/enums/channel-function";
import ChannelReactionConditionChooser from "@/channels/reactions/channel-reaction-condition-chooser.vue";
import {ChannelReactionConditions} from "@/channels/reactions/channel-reaction-conditions";

describe('ChannelReactionsConfig', () => {
    const GARAGEDOOR = {id: 5, functionId: ChannelFunction.OPENINGSENSOR_GARAGEDOOR};

    it('renders the form and selects nothing', () => {
        const wrapper = mount({
            data: () => ({channel: GARAGEDOOR, condition: undefined}),
            template: '<div><cc :subject="channel" v-model="condition"/></div>',
            components: {cc: ChannelReactionConditionChooser},
        });
        const actions = wrapper.findAll('.panel-heading');
        expect(actions.length).toBe(ChannelReactionConditions[GARAGEDOOR.functionId].length);
        expect(wrapper.vm.condition).toBeUndefined();
    });

    it('chooses condition without params', async () => {
        const wrapper = mount({
            data: () => ({channel: GARAGEDOOR, condition: undefined}),
            template: '<div><cc :subject="channel" v-model="condition"/></div>',
            components: {cc: ChannelReactionConditionChooser},
        });
        await wrapper.find('.panel-heading').trigger('click');
        expect(wrapper.vm.condition).not.toBeUndefined();
        expect(wrapper.vm.condition).toEqual(ChannelReactionConditions[GARAGEDOOR.functionId][0].def());
        expect(wrapper.findAll('.panel-success').length).toEqual(1);
    });

    it('unselects condition after click twice', async () => {
        const wrapper = mount({
            data: () => ({channel: GARAGEDOOR, condition: undefined}),
            template: '<div><cc :subject="channel" v-model="condition"/></div>',
            components: {cc: ChannelReactionConditionChooser},
        });
        await wrapper.find('.panel-heading').trigger('click');
        await wrapper.find('.panel-heading').trigger('click');
        expect(wrapper.vm.condition).toBeUndefined();
        expect(wrapper.findAll('.panel-success').length).toEqual(0);
    });

    it('selects no-param condition based on initial value', async () => {
        const wrapper = mount({
            data: () => ({channel: GARAGEDOOR, condition: ChannelReactionConditions[GARAGEDOOR.functionId][0].def()}),
            template: '<div><cc :subject="channel" v-model="condition"/></div>',
            components: {cc: ChannelReactionConditionChooser},
        });
        expect(wrapper.vm.condition).not.toBeUndefined();
        expect(wrapper.vm.condition).toEqual(ChannelReactionConditions[GARAGEDOOR.functionId][0].def());
        expect(wrapper.findAll('.panel-success').length).toEqual(1);
    });
});
