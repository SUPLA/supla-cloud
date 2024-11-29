import {mount} from '@vue/test-utils'
import ChannelFunction from "@/common/enums/channel-function";
import ChannelReactionConditionChooser from "@/channels/reactions/channel-reaction-condition-chooser.vue";

describe('ChannelReactionsConfig', () => {
    describe('OPENINGSENSOR_GARAGEDOOR', () => {
        const GARAGEDOOR = {id: 5, functionId: ChannelFunction.OPENINGSENSOR_GARAGEDOOR};

        it('renders the form and selects nothing', async () => {
            const wrapper = await mount({
                data: () => ({channel: GARAGEDOOR, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            const actions = wrapper.findAll('.panel-heading');
            expect(actions.length).toBe(3);
            expect(wrapper.vm.condition).toBeUndefined();
        });

        it('chooses condition', async () => {
            const wrapper = await mount({
                data: () => ({channel: GARAGEDOOR, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            expect(wrapper.vm.condition).toEqual({on_change_to: {eq: 'open'}});
        });

        it('does not unselect condition after click twice', async () => {
            const wrapper = await mount({
                data: () => ({channel: GARAGEDOOR, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            await wrapper.find('.panel-heading').trigger('click');
            expect(wrapper.vm.condition).toEqual({on_change_to: {eq: 'open'}});
        });

        it('selects second condition', async () => {
            const wrapper = await mount({
                data: () => ({channel: GARAGEDOOR, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.findAll('.panel-heading').at(1).trigger('click');
            expect(wrapper.vm.condition).toEqual({on_change_to: {eq: 'closed'}});
        });

        it('initializes with selected contidion', async () => {
            const wrapper = await mount({
                data: () => ({channel: GARAGEDOOR, condition: {on_change_to: {eq: 'closed'}}}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.findAll('.panel-success').length).toBe(1);
            expect(wrapper.find('.panel-success').text()).toContain('closed');
            expect(wrapper.vm.condition).toEqual({on_change_to: {eq: 'closed'}});
        });
    });

    describe('ELECTRICITYMETER', () => {
        it('selects the first action by default if only one', async () => {
            const wrapper = await mount({
                data: () => ({
                    channel: {functionId: ChannelFunction.ELECTRICITYMETER, config: {enabledPhases: [1, 2, 3]}},
                    condition: undefined
                }),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.findAll('.form-control').length).toEqual(2);
            expect(wrapper.vm.condition).toBeDefined();
        });
    });

    describe('THERMOMETER', () => {
        const channel = {functionId: ChannelFunction.THERMOMETER, config: {}};

        it('displays unit', async () => {
            const wrapper = await mount({
                data: () => ({channel, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            expect(wrapper.text()).toContain('°C');
        });

        it('can set the temperature condition parameters', async () => {
            const wrapper = await mount({
                data: () => ({channel, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            expect(wrapper.vm.condition).toEqual({on_change_to: {lt: 20, name: 'temperature', resume: {ge: 20}}});
            await wrapper.find('.input-group-btn a').trigger('click');
            expect(wrapper.vm.condition).toEqual({on_change_to: {le: 20, name: 'temperature', resume: {gt: 20}}});
            await wrapper.find('.input-group-btn a').trigger('click');
            expect(wrapper.vm.condition).toEqual({on_change_to: {gt: 20, name: 'temperature', resume: {le: 20}}});
            await wrapper.find('.form-control').setValue('22');
            expect(wrapper.vm.condition).toEqual({on_change_to: {gt: 22, name: 'temperature', resume: {le: 20}}});
            await wrapper.find('.form-control').setValue('0');
            expect(wrapper.vm.condition).toEqual({on_change_to: {gt: 0, name: 'temperature', resume: {le: 0}}});
            await wrapper.find('.input-group-btn a').trigger('click');
            await wrapper.find('.input-group-btn a').trigger('click');
            expect(wrapper.vm.condition).toEqual({on_change_to: {eq: 0, name: 'temperature'}});
        });

        it('can set negative threshold', async () => {
            const wrapper = await mount({
                data: () => ({channel, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            await wrapper.find('.form-control').setValue('');
            expect(wrapper.vm.condition).toBeUndefined();
            await wrapper.find('.form-control').setValue('-');
            expect(wrapper.vm.condition).toBeUndefined();
            await wrapper.find('.form-control').setValue('-2');
            expect(wrapper.vm.condition).toEqual({on_change_to: {lt: -2, name: 'temperature', resume: {ge: 20}}});
        });

        it('can set negative resume threshold', async () => {
            const wrapper = await mount({
                data: () => ({channel, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            await wrapper.find('.btn-white').trigger('click');
            await wrapper.find('.btn-white').trigger('click');
            await wrapper.findAll('.form-control').at(1).setValue('');
            expect(wrapper.vm.condition).toBeUndefined();
            await wrapper.findAll('.form-control').at(1).setValue('-');
            expect(wrapper.vm.condition).toBeUndefined();
            await wrapper.findAll('.form-control').at(1).setValue('-2');
            expect(wrapper.vm.condition).toEqual({on_change_to: {gt: 20, name: 'temperature', resume: {le: -2}}});
        });

        it('cannot set empty threshold for temerature', async () => {
            const wrapper = await mount({
                data: () => ({channel, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            await wrapper.find('.form-control').setValue('');
            expect(wrapper.vm.condition).toBeUndefined();
        });

        it('can initialize temperature condition with initial value', async () => {
            const wrapper = await mount({
                data: () => ({
                    channel,
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
                    channel,
                    condition: {on_change_to: {gt: 0, name: 'temperature'}}
                }),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.find('.input-group-btn a').text()).toContain('>');
            expect(wrapper.find('.form-control').element.value).toEqual('0');
        });

        it('can initialize temperature condition on_change', async () => {
            const wrapper = await mount({
                data: () => ({
                    channel,
                    condition: {on_change: {name: 'temperature'}}
                }),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            expect(wrapper.find('.panel-success').text()).toContain('temperature changes');
        });

        it('can set invalid threshold for a while', async () => {
            const wrapper = await mount({
                data: () => ({channel, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.find('.panel-heading').trigger('click');
            await wrapper.findAll('.form-control').at(1).setValue('2');
            expect(wrapper.vm.condition).toBeUndefined();
            await wrapper.findAll('.form-control').at(1).setValue('21');
            expect(wrapper.vm.condition).toEqual({on_change_to: {lt: 20, name: 'temperature', resume: {ge: 21}}});
        });
    });

    describe('HUMIDITYANDTEMPERATURE', () => {
        const channel = {functionId: ChannelFunction.HUMIDITYANDTEMPERATURE, config: {}};

        it('can set humidity threshold', async () => {
            const wrapper = await mount({
                data: () => ({channel, condition: undefined}),
                template: '<div><cc :subject="channel" v-model="condition"/></div>',
                components: {cc: ChannelReactionConditionChooser},
            });
            await wrapper.findAll('.panel-heading').at(2).trigger('click');
            expect(wrapper.text()).toContain('%');
            await wrapper.find('.form-control').setValue('50');
            expect(wrapper.vm.condition).toEqual({on_change_to: {lt: 50, name: 'humidity', resume: {ge: 50}}});
        });

        it('initializes humidity condition from json', async () => {
            const wrapper = await mount({
                data: () => ({
                    channel,
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
