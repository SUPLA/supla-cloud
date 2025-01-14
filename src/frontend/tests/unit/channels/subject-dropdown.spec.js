import {mount} from "@vue/test-utils";
import SubjectDropdown from "@/devices/subject-dropdown.vue";
import ChannelActionChooser from "@/channels/action/channel-action-chooser.vue";
import ChannelFunction from "@/common/enums/channel-function";
import ActionableSubjectType from "@/common/enums/actionable-subject-type";
import ChannelFunctionAction from "@/common/enums/channel-function-action";
import {setActivePinia} from "pinia";
import {createTestingPinia} from "@pinia/testing";

describe('SubjectDropdown', () => {
    beforeEach(() => {
        setActivePinia(createTestingPinia({
            initialState: {
                frontendConfig: {config: {notificationsEnabled: true}}
            }
        }));
    })

    const subjectDropdown = async (cfg = {}) => {
        return mount(
            {
                data: () => ({subject: undefined, action: undefined, ...(cfg.data || {})}),
                template: `
                    <div>
                        <SubjectDropdown v-model="subject">
                            <ChannelActionChooser :subject="subject" v-model="action" :always-select-first-action="true"/>
                        </SubjectDropdown>
                    </div>`,
                components: {SubjectDropdown, ChannelActionChooser},
            },
            {
                mocks: {
                    $http: {
                        get(endpoint) {
                            let body = [];
                            if (endpoint.startsWith('channels')) {
                                body = cfg.channels || [];
                            }
                            return Promise.resolve({body});
                        },
                    },
                },
            });
    };

    const channelStub = (functionId, ext) => ({
        id: Math.floor(Math.random() * 10000),
        caption: 'My Channel',
        ownSubjectType: ActionableSubjectType.CHANNEL,
        functionId: functionId,
        function: {id: functionId, caption: 'Function Caption'},
        iodevice: {id: 1, name: 'SONOFF'},
        location: {id: 2, caption: 'Location #2'},
        ...ext,
    });

    const possibleAction = (id) => ({
        id,
        name: Object.keys(ChannelFunctionAction).find(k => ChannelFunctionAction[k] === id),
        caption: Object.keys(ChannelFunctionAction).find(k => ChannelFunctionAction[k] === id).replace(/_/g, ' '),
    });

    it('reders subject dropdown', async () => {
        const wrapper = await subjectDropdown();
        expect(wrapper.vm.subject).toBeUndefined();
        expect(wrapper.vm.action).toBeUndefined();
        expect(wrapper.find('div').text()).toContain('Channels');
        expect(wrapper.find('div').text()).toContain('Schedules');
        expect(wrapper.find('div').text()).toContain('Send notification');
    });

    it('selects channel action', async () => {
        const channel = channelStub(ChannelFunction.CONTROLLINGTHEGARAGEDOOR, {
            possibleActions: [possibleAction(ChannelFunctionAction.OPEN_CLOSE), possibleAction(ChannelFunctionAction.OPEN), possibleAction(ChannelFunctionAction.CLOSE)]
        });
        const wrapper = await subjectDropdown({channels: [channel]});
        await wrapper.findAll('.panel-heading').at(0).trigger('click');
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.subject).toBeUndefined();
        const tom = wrapper.find('select').element.tomselect;
        expect(Object.entries(tom.options)).toHaveLength(1);
        tom.setValue(channel.id);
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.subject).not.toBeUndefined();
        expect(wrapper.vm.subject.id).toEqual(channel.id);
        expect(wrapper.vm.subject.ownSubjectType).toEqual(ActionableSubjectType.CHANNEL);
        expect(wrapper.vm.action).not.toBeUndefined();
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.OPEN_CLOSE);
        await wrapper.vm.$nextTick();
        expect(wrapper.find('div').text()).toContain('My Channel');
        expect(wrapper.find('div').text()).toContain('OPEN CLOSE');
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('OPEN CLOSE');
        await wrapper.findAll('.channel-action-chooser .panel-heading').at(1).trigger('click');
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.OPEN);
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('OPEN');
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).not.toContain('CLOSE');
    });

    it('changes action when subject changes', async () => {
        const channels = [
            channelStub(ChannelFunction.CONTROLLINGTHEGARAGEDOOR, {
                possibleActions: [possibleAction(ChannelFunctionAction.OPEN_CLOSE), possibleAction(ChannelFunctionAction.OPEN), possibleAction(ChannelFunctionAction.CLOSE)],
            }),
            channelStub(ChannelFunction.POWERSWITCH, {
                possibleActions: [possibleAction(ChannelFunctionAction.TURN_ON), possibleAction(ChannelFunctionAction.TURN_OFF)],
            }),
        ];
        const wrapper = await subjectDropdown({channels});
        await wrapper.findAll('.panel-heading').at('0').trigger('click');
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.subject).toBeUndefined();
        const tom = wrapper.find('select').element.tomselect;
        expect(Object.entries(tom.options)).toHaveLength(2);
        tom.setValue(channels[0].id);
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.subject.id).toEqual(channels[0].id);
        expect(wrapper.vm.subject.ownSubjectType).toEqual(ActionableSubjectType.CHANNEL);
        expect(wrapper.vm.action).not.toBeUndefined();
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.OPEN_CLOSE);
        tom.setValue(channels[1].id);
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.subject.id).toEqual(channels[1].id);
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.TURN_ON);
    });

    it('maintains action when subject changes and has the same function', async () => {
        const channels = [
            channelStub(ChannelFunction.POWERSWITCH, {
                possibleActions: [possibleAction(ChannelFunctionAction.TURN_ON), possibleAction(ChannelFunctionAction.TURN_OFF)],
            }),
            channelStub(ChannelFunction.POWERSWITCH, {
                possibleActions: [possibleAction(ChannelFunctionAction.TURN_ON), possibleAction(ChannelFunctionAction.TURN_OFF)],
            }),
        ];
        const wrapper = await subjectDropdown({channels});
        await wrapper.findAll('.panel-heading').at('0').trigger('click');
        await wrapper.vm.$nextTick();
        const tom = wrapper.find('select').element.tomselect;
        expect(Object.entries(tom.options)).toHaveLength(2);
        tom.setValue(channels[0].id);
        await wrapper.vm.$nextTick();
        await wrapper.findAll('.channel-action-chooser .panel-heading').at(1).trigger('click');
        expect(wrapper.vm.subject.id).toEqual(channels[0].id);
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.TURN_OFF);
        tom.setValue(channels[1].id);
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.subject.id).toEqual(channels[1].id);
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.TURN_OFF);
    });

    it('sets the subject and action based on props', async () => {
        const channels = [
            channelStub(ChannelFunction.POWERSWITCH, {
                possibleActions: [possibleAction(ChannelFunctionAction.TURN_ON), possibleAction(ChannelFunctionAction.TURN_OFF)],
            }),
            channelStub(ChannelFunction.LIGHTSWITCH, {
                possibleActions: [possibleAction(ChannelFunctionAction.TURN_ON), possibleAction(ChannelFunctionAction.TURN_OFF)],
            }),
        ];
        const wrapper = await subjectDropdown({
            channels,
            data: {subject: channels[1], action: possibleAction(ChannelFunctionAction.TURN_OFF)}
        });
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('TURN OFF');
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.TURN_OFF);
        expect(wrapper.vm.subject.id).toEqual(channels[1].id);
    });

    it('can change the action externally', async () => {
        const channels = [
            channelStub(ChannelFunction.POWERSWITCH, {
                possibleActions: [possibleAction(ChannelFunctionAction.TURN_ON), possibleAction(ChannelFunctionAction.TURN_OFF)],
            }),
        ];
        const wrapper = await subjectDropdown({
            channels,
            data: {subject: channels[0], action: possibleAction(ChannelFunctionAction.TURN_OFF)}
        });
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('TURN OFF');
        wrapper.vm.action = possibleAction(ChannelFunctionAction.TURN_ON);
        await wrapper.vm.$nextTick();
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('TURN ON');
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.TURN_ON);
        expect(wrapper.vm.subject.id).toEqual(channels[0].id);
    });

    it('selects the first action if the action is cleared', async () => {
        const channels = [
            channelStub(ChannelFunction.POWERSWITCH, {
                possibleActions: [possibleAction(ChannelFunctionAction.TURN_ON), possibleAction(ChannelFunctionAction.TURN_OFF)],
            }),
        ];
        const wrapper = await subjectDropdown({
            channels,
            data: {subject: channels[0], action: possibleAction(ChannelFunctionAction.TURN_OFF)}
        });
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('TURN OFF');
        wrapper.vm.action = undefined;
        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('.channel-action-chooser .panel-success .panel-heading')).toHaveLength(1);
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.TURN_ON);
        expect(wrapper.vm.subject.id).toEqual(channels[0].id);
    });

    it('selects action with parameters', async () => {
        const channels = [
            channelStub(ChannelFunction.CONTROLLINGTHEROLLERSHUTTER, {
                possibleActions: [possibleAction(ChannelFunctionAction.OPEN), possibleAction(ChannelFunctionAction.CLOSE), possibleAction(ChannelFunctionAction.CLOSE_PARTIALLY)],
            }),
        ];
        const wrapper = await subjectDropdown({channels, data: {subject: channels[0]}});
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('OPEN');
        await wrapper.findAll('.channel-action-chooser .panel-heading').at(2).trigger('click');
        expect(wrapper.vm.action).toBeDefined();
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.CLOSE_PARTIALLY);
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('CLOSE PARTIALLY');
        const percentage = wrapper.find('input[type=text]');
        expect(percentage.exists()).toBeTruthy();
        expect(wrapper.vm.action.param).toEqual({percentage: ''});
        await percentage.setValue('22');
        expect(wrapper.vm.action.param).toEqual({percentage: '22'});
    });

    it('clears action when the subject is cleared', async () => {
        const channels = [
            channelStub(ChannelFunction.CONTROLLINGTHEROLLERSHUTTER, {
                possibleActions: [possibleAction(ChannelFunctionAction.OPEN)],
            }),
        ];
        const wrapper = await subjectDropdown({channels, data: {subject: channels[0]}});
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('OPEN');
        wrapper.vm.subject = undefined;
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.subject).toBeUndefined();
        expect(wrapper.vm.action).toBeUndefined();
        expect(wrapper.vm.subjectType).toBeUndefined();
        expect(wrapper.find('div').text()).toContain('Channels');
        expect(wrapper.find('div').text()).toContain('Schedules');
        expect(wrapper.find('div').text()).toContain('Send notification');
    });

    it('can change the params externally', async () => {
        const channels = [
            channelStub(ChannelFunction.CONTROLLINGTHEROLLERSHUTTER, {
                possibleActions: [possibleAction(ChannelFunctionAction.OPEN), possibleAction(ChannelFunctionAction.OPEN_PARTIALLY)],
            }),
        ];
        const wrapper = await subjectDropdown({
            channels,
            data: {subject: channels[0]}
        });
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('OPEN');
        wrapper.vm.action = {id: ChannelFunctionAction.OPEN_PARTIALLY, param: {percentage: 10}};
        await wrapper.vm.$nextTick();
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('OPEN PARTIALLY');
        const percentage = wrapper.find('input[type=text]');
        expect(percentage.element.value).toEqual('10');
        wrapper.vm.action = {id: ChannelFunctionAction.OPEN_PARTIALLY, param: {percentage: 20}};
        await wrapper.vm.$nextTick();
        expect(percentage.element.value).toEqual('20');
    });
})
