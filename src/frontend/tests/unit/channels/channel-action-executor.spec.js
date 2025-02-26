import {mount} from '@vue/test-utils'
import ActionableSubjectType from "@/common/enums/actionable-subject-type";
import ChannelActionExecutor from "@/channels/action/channel-action-executor.vue";
import ChannelFunctionAction from "@/common/enums/channel-function-action";
import ChannelFunction from "@/common/enums/channel-function";
import {setActivePinia} from "pinia";
import {createTestingPinia} from "@pinia/testing";

describe('ChannelActionExecutor', () => {

    beforeEach(() => {
        setActivePinia(createTestingPinia({
            initialState: {
                channels: {all: {6: ROLETTE}}
            }
        }))
    })

    const SCENE = {
        id: 1,
        ownSubjectType: ActionableSubjectType.SCENE,
        possibleActions: [
            {"id": 3000, "name": "EXECUTE", "nameSlug": "execute", "caption": "Execute"},
            {"id": 3001, "name": "INTERRUPT", "nameSlug": "interrupt", "caption": "Interrupt"},
            {"id": 3002, "name": "INTERRUPT_AND_EXECUTE", "nameSlug": "interrupt-and-execute", "caption": "Interrupt and execute"}
        ],
        state: {connected: true, connectedCode: 'CONNECTED'},
        functionId: 2000,
        "function": {
            "id": 2000,
            "name": "SCENE",
            "caption": "Scene",
            "possibleVisualStates": ["default"],
            "maxAlternativeIconIndex": 19,
            "output": true
        },
    };

    const ROLETTE = {
        id: 6,
        ownSubjectType: ActionableSubjectType.CHANNEL,
        possibleActions: [{"id": ChannelFunctionAction.OPEN_PARTIALLY, "name": "OPEN_PARTIALLY", "caption": "Open partially"}],
        state: {connected: true, connectedCode: 'CONNECTED'},
        functionId: ChannelFunction.CONTROLLINGTHEROLLERSHUTTER,
        "function": {
            "id": ChannelFunction.CONTROLLINGTHEROLLERSHUTTER,
            "name": "CONTROLLINGTHEROLLERSHUTTER",
            "caption": "Roller operation",
        },
    };

    it('renders available actions', async () => {
        const wrapper = await mount(ChannelActionExecutor, {
            propsData: {subject: SCENE},
        });
        const actions = wrapper.findAll('.panel-heading');
        expect(actions.length).toBe(3);
        expect(actions.at(0).text()).toEqual('Execute');
        expect(actions.at(1).text()).toEqual('Interrupt');
        expect(wrapper.emitted().input).toBeFalsy();
    });

    it('executes action without params', async () => {
        let executed = false;
        const wrapper = await mount(ChannelActionExecutor, {
            propsData: {subject: SCENE},
            mocks: {
                $http: {
                    patch(endpoint, data) {
                        expect(endpoint).toEqual('scenes/1');
                        expect(data).toEqual({action: ChannelFunctionAction.INTERRUPT_AND_EXECUTE})
                        executed = true;
                        return Promise.resolve();
                    },
                }
            }
        });
        await wrapper.findAll('.panel-heading').at(2).trigger('click');
        expect(executed).toBeTruthy();
    });

    it('executes action with params', async () => {
        let executed = false;
        const wrapper = await mount(ChannelActionExecutor, {
            propsData: {subject: ROLETTE},
            mocks: {
                $http: {
                    patch(endpoint, data) {
                        expect(endpoint).toEqual('channels/6');
                        expect(data).toEqual({action: ChannelFunctionAction.OPEN_PARTIALLY, percentage: '33'})
                        executed = true;
                        return Promise.resolve();
                    },
                }
            }
        });
        await wrapper.findAll('.panel-heading').at(0).trigger('click');
        const percentage = wrapper.find('input[type=text]');
        expect(percentage.exists()).toBeTruthy();
        await percentage.setValue('33');
        await wrapper.find('.btn-execute').trigger('click');
        expect(executed).toBeTruthy();
    });
})
