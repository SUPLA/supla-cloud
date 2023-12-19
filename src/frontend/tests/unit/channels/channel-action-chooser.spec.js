import {mount} from '@vue/test-utils'
import ChannelActionChooser from '@/channels/action/channel-action-chooser.vue'
import ActionableSubjectType from "@/common/enums/actionable-subject-type";
import {deepCopy} from "@/common/utils";
import ChannelFunction from "@/common/enums/channel-function";
import ChannelFunctionAction from "@/common/enums/channel-function-action";

describe('ChannelActionChooser', () => {

    const SCENE = {
        id: 1,
        ownSubjectType: ActionableSubjectType.SCENE,
        possibleActions: [
            {"id": 3000, "name": "EXECUTE", "nameSlug": "execute", "caption": "Execute"},
            {"id": 3001, "name": "INTERRUPT", "nameSlug": "interrupt", "caption": "Interrupt"},
            {"id": 3002, "name": "INTERRUPT_AND_EXECUTE", "nameSlug": "interrupt-and-execute", "caption": "Interrupt and execute"}
        ],
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

    const GATEWAY = {
        id: 5,
        ownSubjectType: ActionableSubjectType.CHANNEL,
        possibleActions: [{"id": 10, "name": "OPEN", "nameSlug": "open", "caption": "Open"}],
        functionId: 90,
        "function": {
            "id": 90,
            "name": "CONTROLLINGTHEDOORLOCK",
            "caption": "Door lock operation",
            "possibleVisualStates": ["opened", "closed"],
            "maxAlternativeIconIndex": 0,
            "output": true
        },
    };

    it('renders available actions', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: undefined},
        });
        const actions = wrapper.findAll('.panel-heading');
        expect(actions.length).toBe(3);
        expect(actions.at(0).text()).toEqual('Execute');
        expect(actions.at(1).text()).toEqual('Interrupt');
        expect(wrapper.emitted().input).toBeFalsy();
    });

    it('selects first action if only one action', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: GATEWAY, value: undefined},
        });
        const actions = wrapper.findAll('.panel-heading');
        expect(actions.length).toBe(1);
        expect(actions.at(0).text()).toEqual('Open');
        expect(wrapper.emitted().input).toBeTruthy();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Open');
    });

    it('selects first action if asked by prop', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: undefined, alwaysSelectFirstAction: true},
        });
        expect(wrapper.emitted().input).toBeTruthy();
        expect(wrapper.emitted().input.length).toBe(1);
        const action = wrapper.emitted().input[0][0];
        expect(action.id).toEqual(3000);
        expect(action.param).toBeNull();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Execute');
    });

    it('selects action from outer value', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}},
        });
        expect(wrapper.emitted().input).toBeFalsy();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Interrupt');
        expect(wrapper.vm.action.id).toBe(3001);
    });

    it('updates action from outer value', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}},
        });
        expect(wrapper.emitted().input).toBeFalsy();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Interrupt');
        expect(wrapper.vm.action.id).toBe(3001);
    });

    it('selects action from outer value if alwaysSelectFirstAction is enabled', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}, alwaysSelectFirstAction: true},
        });
        await wrapper.setProps({value: {id: 3002, param: {}}});
        expect(wrapper.emitted().input).toBeFalsy();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Interrupt and execute');
        expect(wrapper.vm.action.id).toBe(3002);
    });

    it('clears action when the subject is cleared', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}},
        });
        await wrapper.setProps({subject: undefined});
        expect(wrapper.emitted().input).toBeTruthy();
        expect(wrapper.emitted().input.length).toBe(1);
        expect(wrapper.emitted().input[0]).toEqual([undefined]);
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeFalsy();
    });

    it('changes action when the subject is changed', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}},
        });
        await wrapper.setProps({subject: GATEWAY});
        expect(wrapper.emitted().input).toBeTruthy();
        expect(wrapper.emitted().input.length).toBe(2);
        expect(wrapper.emitted().input.at(0)[0]).toBeUndefined();
        expect(wrapper.emitted().input.at(1)[0].id).toEqual(10);
        await wrapper.vm.$nextTick();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Open');
    });

    it('clears action when the subject is changed', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: GATEWAY, value: undefined},
        });
        await wrapper.setProps({subject: SCENE});
        expect(wrapper.emitted().input).toBeTruthy();
        expect(wrapper.emitted().input.length).toBe(2);
        expect(wrapper.emitted().input[1]).toEqual([undefined]);
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeFalsy();
    });

    it('selects first action for new subject', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: GATEWAY, value: undefined, alwaysSelectFirstAction: true},
        });
        await wrapper.setProps({subject: SCENE});
        expect(wrapper.emitted().input).toBeTruthy();
        await wrapper.vm.$nextTick();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Execute');
    });

    it('maintains action when the subject is replaced with its copy', async () => {
        const wrapper = await mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}},
        });
        expect(wrapper.html()).toContain('Interrupt');
        expect(wrapper.find('.panel-success').text()).toContain('Interrupt');
        expect(wrapper.find('.panel-success').text()).not.toContain('execute');
        await wrapper.findAll('.panel-heading').at(2).trigger('click');
        expect(wrapper.find('.panel-success').text()).toContain('Interrupt and execute');
        expect(wrapper.vm.action.id).toEqual(3002);
        await wrapper.setProps({subject: deepCopy(SCENE)});
        expect(wrapper.find('.panel-success').text()).not.toContain('execute');
        expect(wrapper.vm.action.id).toEqual(3001);
    });

    describe('HVAC', () => {
        const HVAC_AUTO = {
            id: 5,
            ownSubjectType: ActionableSubjectType.CHANNEL,
            possibleActions: [
                {"id": ChannelFunctionAction.TURN_ON, "name": "TURN_ON", "nameSlug": "on", "caption": "On"},
                {"id": ChannelFunctionAction.TURN_OFF_WITH_DURATION, "name": "TURN_OFF_WITH_DURATION", "nameSlug": "off", "caption": "Off"},
            ],
            functionId: ChannelFunction.HVAC_THERMOSTAT_HEAT_COOL,
            "function": {
                "id": ChannelFunction.HVAC_THERMOSTAT_HEAT_COOL,
                "name": "HVAC_THERMOSTAT_HEAT_COOL",
                "caption": "HVAC Auto",
                "possibleVisualStates": ["heating", "cooling"],
            },
        };

        it('turns off without duration', async () => {
            const wrapper = await mount(ChannelActionChooser, {
                propsData: {subject: HVAC_AUTO, value: undefined},
            });
            const actions = wrapper.findAll('.panel-heading');
            expect(actions.length).toBe(2);
            expect(actions.at(0).text()).toEqual('On');
            expect(actions.at(1).text()).toEqual('Off');
            expect(wrapper.emitted().input).toBeFalsy();
            await actions.at(1).trigger('click');
            const action = wrapper.emitted().input[0][0];
            expect(action).toEqual({id: ChannelFunctionAction.TURN_OFF_WITH_DURATION, param: {}});
        });
    });
})
