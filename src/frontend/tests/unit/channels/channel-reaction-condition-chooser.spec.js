import {mount} from '@vue/test-utils'
import ChannelFunction from "@/common/enums/channel-function";
import ChannelReactionConditionChooser from "@/channels/reactions/channel-reaction-condition-chooser.vue";
import {ChannelReactionConditions} from "@/channels/reactions/channel-reaction-conditions";

describe('ChannelReactionsConfig', () => {
    describe('OPENINGSENSOR_GARAGEDOOR', () => {
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
            const wrapper = await mount({
                data: () => ({channel: GARAGEDOOR, condition: ChannelReactionConditions[GARAGEDOOR.functionId][0].def()}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
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

        it('does not render param panel if no params', async () => {
            const wrapper = await mount({
                data: () => ({channel: GARAGEDOOR, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            expect(wrapper.text()).not.toContain('Threshold');
            expect(wrapper.text()).not.toContain('temperature');
            expect(wrapper.findAll('.panel-body').length).toEqual(0);
        });
    });

    describe('THERMOMETER', () => {
        it('selects the first action by default if only one', async () => {
            const wrapper = await mount({
                data: () => ({channel: {functionId: ChannelFunction.THERMOMETER}, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.findAll('.panel-success').length).toEqual(1);
            expect(wrapper.vm.condition).toBeDefined();
        });

        it('displays unit', async () => {
            const wrapper = await mount({
                data: () => ({channel: {functionId: ChannelFunction.THERMOMETER}, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.text()).toContain('°C');
        });

        it('cannot deselect the only available condition', async () => {
            const wrapper = await mount({
                data: () => ({channel: {functionId: ChannelFunction.THERMOMETER}, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            expect(wrapper.findAll('.panel-success').length).toEqual(1);
            expect(wrapper.vm.condition).toBeDefined();
        });

        it('can set the temperature condition parameters', async () => {
            const wrapper = await mount({
                data: () => ({channel: {functionId: ChannelFunction.THERMOMETER}, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.vm.condition).toEqual({on_change_to: {lt: 20, name: 'temperature'}});
            await wrapper.find('.input-group-btn a').trigger('click');
            expect(wrapper.vm.condition).toEqual({on_change_to: {le: 20, name: 'temperature'}});
            await wrapper.find('.input-group-btn a').trigger('click');
            expect(wrapper.vm.condition).toEqual({on_change_to: {gt: 20, name: 'temperature'}});
            await wrapper.find('.form-control').setValue('22');
            expect(wrapper.vm.condition).toEqual({on_change_to: {gt: 22, name: 'temperature'}});
            await wrapper.find('.form-control').setValue('0');
            expect(wrapper.vm.condition).toEqual({on_change_to: {gt: 0, name: 'temperature'}});
        });

        it('cannot set empty threshold for temerature', async () => {
            const wrapper = await mount({
                data: () => ({channel: {functionId: ChannelFunction.THERMOMETER}, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.form-control').setValue('');
            expect(wrapper.vm.condition).toEqual({on_change_to: {lt: 0, name: 'temperature'}});
        });

        it('can initialize temperature condition with initial value', async () => {
            const wrapper = await mount({
                data: () => ({
                    channel: {functionId: ChannelFunction.THERMOMETER},
                    condition: {on_change_to: {gt: 33, name: 'temperature'}}
                }),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.find('.input-group-btn a').text()).toContain('>');
            expect(wrapper.find('.form-control').element.value).toEqual('33');
        });

        it('can initialize temperature condition with initial value with threshold 0', async () => {
            const wrapper = await mount({
                data: () => ({
                    channel: {functionId: ChannelFunction.THERMOMETER},
                    condition: {on_change_to: {gt: 0, name: 'temperature'}}
                }),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.find('.input-group-btn a').text()).toContain('>');
            expect(wrapper.find('.form-control').element.value).toEqual('0');
        });
    });

    describe('HUMIDITYANDTEMPERATURE', () => {
        it('can set humidity threshold', async () => {
            const wrapper = await mount({
                data: () => ({channel: {functionId: ChannelFunction.HUMIDITYANDTEMPERATURE}, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.findAll('.panel-heading').at(1).trigger('click');
            expect(wrapper.text()).toContain('%');
            await wrapper.find('.form-control').setValue('50');
            expect(wrapper.vm.condition).toEqual({on_change_to: {lt: 50, name: 'humidity'}});
        });

        it('initializes humidity condition from json', async () => {
            const wrapper = await mount({
                data: () => ({
                    channel: {functionId: ChannelFunction.HUMIDITYANDTEMPERATURE},
                    condition: {on_change_to: {lt: 60, name: 'humidity'}}
                }),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.findAll('.panel-success').length).toEqual(1);
            expect(wrapper.text()).toContain('%');
            expect(wrapper.find('.form-control').element.value).toEqual('60');
        });
    });
});
