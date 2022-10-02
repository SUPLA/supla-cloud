import {mount} from '@vue/test-utils'
import ChannelActionChooser from '@/channels/action/channel-action-chooser.vue'
import ActionableSubjectType from "@/common/enums/actionable-subject-type";

describe('ChannelActionChooser', () => {

    const SCENE = {
        id: 1,
        subjectType: ActionableSubjectType.SCENE,
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
        subjectType: ActionableSubjectType.CHANNEL,
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

    it('renders available actions', () => {
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: undefined},
        });
        const actions = wrapper.findAll('.panel-heading');
        expect(actions.length).toBe(3);
        expect(actions.at(0).text()).toEqual('Execute');
        expect(actions.at(1).text()).toEqual('Interrupt');
        expect(wrapper.emitted().input).toBeFalsy();
    });

    it('selects first action if only one action', async () => {
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: GATEWAY, value: undefined},
        });
        const actions = wrapper.findAll('.panel-heading');
        expect(actions.length).toBe(1);
        expect(actions.at(0).text()).toEqual('Open');
        expect(wrapper.emitted().input).toBeTruthy();
        await wrapper.vm.$nextTick();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Open');
    });

    it('selects first action if asked by prop', async () => {
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: undefined, alwaysSelectFirstAction: true},
        });
        expect(wrapper.emitted().input).toBeTruthy();
        expect(wrapper.emitted().input.length).toBe(1);
        const action = wrapper.emitted().input[0][0];
        expect(action.id).toEqual(3000);
        expect(action.param).toBeNull();
        await wrapper.vm.$nextTick();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Execute');
    });

    it('selects action from outer value', async () => {
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}},
        });
        await wrapper.vm.$nextTick();
        expect(wrapper.emitted().input).toBeFalsy();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Interrupt');
        expect(wrapper.vm.action.id).toBe(3001);
    });

    it('updates action from outer value', async () => {
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}},
        });
        await wrapper.vm.$nextTick();
        expect(wrapper.emitted().input).toBeFalsy();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Interrupt');
        expect(wrapper.vm.action.id).toBe(3001);
    });

    it('selects action from outer value if alwaysSelectFirstAction is enabled', async () => {
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}, alwaysSelectFirstAction: true},
        });
        await wrapper.vm.$nextTick();
        wrapper.setProps({value: {id: 3002, param: {}}});
        await wrapper.vm.$nextTick();
        expect(wrapper.emitted().input).toBeFalsy();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Interrupt and execute');
        expect(wrapper.vm.action.id).toBe(3002);
    });

    it('clears action when the subject is cleared', async () => {
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}},
        });
        await wrapper.vm.$nextTick();
        wrapper.setProps({subject: undefined});
        await wrapper.vm.$nextTick();
        expect(wrapper.emitted().input).toBeTruthy();
        expect(wrapper.emitted().input.length).toBe(1);
        expect(wrapper.emitted().input[0]).toEqual([undefined]);
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeFalsy();
    });

    it('changes action when the subject is changed', async () => {
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: SCENE, value: {id: 3001, param: {}}},
        });
        await wrapper.vm.$nextTick();
        wrapper.setProps({subject: GATEWAY});
        await wrapper.vm.$nextTick();
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
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: GATEWAY, value: undefined},
        });
        await wrapper.vm.$nextTick();
        wrapper.setProps({subject: SCENE});
        await wrapper.vm.$nextTick();
        expect(wrapper.emitted().input).toBeTruthy();
        await wrapper.vm.$nextTick();
        expect(wrapper.emitted().input.length).toBe(2);
        expect(wrapper.emitted().input[1]).toEqual([undefined]);
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeFalsy();
    });

    it('selects first action for new subject', async () => {
        const wrapper = mount(ChannelActionChooser, {
            propsData: {subject: GATEWAY, value: undefined, alwaysSelectFirstAction: true},
        });
        await wrapper.vm.$nextTick();
        wrapper.setProps({subject: SCENE});
        await wrapper.vm.$nextTick();
        expect(wrapper.emitted().input).toBeTruthy();
        await wrapper.vm.$nextTick();
        const selectedAction = wrapper.find('.panel-success');
        expect(selectedAction.exists()).toBeTruthy();
        expect(selectedAction.text()).toEqual('Execute');
    });
})
