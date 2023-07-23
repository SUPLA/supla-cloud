import {mount} from "@vue/test-utils";
import SubjectDropdown from "@/devices/subject-dropdown.vue";
import ChannelActionChooser from "@/channels/action/channel-action-chooser.vue";
import ChannelFunction from "@/common/enums/channel-function";
import ActionableSubjectType from "@/common/enums/actionable-subject-type";
import ChannelFunctionAction from "@/common/enums/channel-function-action";

describe('SubjectDropdown', () => {
    const subjectDropdown = async (cfg) => {
        return mount(
            {
                data: () => ({subject: undefined, action: undefined}),
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

    it('reders subject dropdown', async () => {
        const wrapper = await subjectDropdown();
        expect(wrapper.vm.subject).toBeUndefined();
        expect(wrapper.vm.action).toBeUndefined();
        expect(wrapper.find('div').text()).toContain('Channels');
        expect(wrapper.find('div').text()).toContain('Schedules');
        expect(wrapper.find('div').text()).toContain('Send notification');
    });

    it('selects channel action', async () => {
        const wrapper = await subjectDropdown({
            channels: [
                {
                    id: 5,
                    caption: 'My Channel',
                    ownSubjectType: ActionableSubjectType.CHANNEL,
                    functionId: ChannelFunction.CONTROLLINGTHEGARAGEDOOR,
                    function: {id: ChannelFunction.CONTROLLINGTHEGARAGEDOOR, caption: 'Garage door operation'},
                    iodevice: {id: 1, name: 'SONOFF'},
                    location: {id: 2, caption: 'Location #2'},
                    possibleActions: [
                        {id: ChannelFunctionAction.OPEN_CLOSE, name: "OPEN_CLOSE", caption: "Open / Close"},
                        {id: ChannelFunctionAction.OPEN, name: "OPEN", caption: "Open"},
                        {id: ChannelFunctionAction.CLOSE, name: "CLOSE", caption: "Close"},
                    ],
                }
            ],
        });
        await wrapper.findAll('.panel-heading').at('0').trigger('click');
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.subject).toBeUndefined();
        const options = wrapper.findAll('.selectpicker option');
        expect(options).toHaveLength(1);
        await options.at(0).setSelected();
        expect(wrapper.vm.subject).not.toBeUndefined();
        expect(wrapper.vm.subject.id).toEqual(5);
        expect(wrapper.vm.subject.ownSubjectType).toEqual(ActionableSubjectType.CHANNEL);
        expect(wrapper.vm.action).not.toBeUndefined();
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.OPEN_CLOSE);
        await wrapper.vm.$nextTick();
        expect(wrapper.find('div').text()).toContain('My Channel');
        expect(wrapper.find('div').text()).toContain('Open / Close');
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('Open / Close');
        await wrapper.findAll('.channel-action-chooser .panel-heading').at(1).trigger('click');
        expect(wrapper.vm.action.id).toEqual(ChannelFunctionAction.OPEN);
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).toContain('Open');
        expect(wrapper.find('.channel-action-chooser .panel-success .panel-heading').text()).not.toContain('Close');

    });
})
