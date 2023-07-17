import {mount} from '@vue/test-utils'
import NotificationForm from "@/notifications/notification-form.vue";

describe('NotificationForm', () => {
    it('renders the form', () => {
        const wrapper = mount(NotificationForm, {
            propsData: {value: {}},
            stubs: {AccessIdsDropdown: {template: '<div />'}},
        });
        const actions = wrapper.findAll('.form-group');
        expect(actions.length).toBe(3);
    });

    it('updates after change', async () => {
        const wrapper = mount({
            data: () => ({notification: {accessIds: [1]}}),
            template: '<div><nf v-model="notification"/></div>',
            components: {nf: NotificationForm},
        }, {stubs: {AccessIdsDropdown: {template: '<div />'}}});
        await wrapper.find('input').setValue('test');
        expect(wrapper.vm.notification).toEqual({title: 'test', accessIds: [1], isValid: false});
        await wrapper.findAll('input').at(1).setValue('test');
        expect(wrapper.vm.notification).toEqual({title: 'test', body: 'test', accessIds: [1], isValid: true});
    });

    it('maintains notification id and other fields', async () => {
        const wrapper = mount({
            data: () => ({notification: {id: 123, title: 'rainbow', property: 'unicorn', accessIds: [1]}}),
            template: '<div><nf v-model="notification"/></div>',
            components: {nf: NotificationForm},
        }, {stubs: {AccessIdsDropdown: {template: '<div />'}}});
        await wrapper.find('input').setValue('test');
        expect(wrapper.vm.notification.id).toEqual(123);
        expect(wrapper.vm.notification.title).toEqual('test');
        expect(wrapper.vm.notification.property).toEqual('unicorn');
    });

    it('claims invalid immediately', async () => {
        const wrapper = mount({
            data: () => ({notification: {id: 123, title: 'rainbow', property: 'unicorn', accessIds: [1]}}),
            template: '<div><nf v-model="notification"/></div>',
            components: {nf: NotificationForm},
        }, {stubs: {AccessIdsDropdown: {template: '<div />'}}});
        expect(wrapper.vm.notification.isValid).toEqual(false);
    });

    it('allows to skip body if disabled', async () => {
        const wrapper = mount({
            data: () => ({notification: {id: 123, title: 'rainbow', property: 'unicorn', accessIds: [1]}}),
            template: '<div><nf v-model="notification" disable-body-message="Disable"/></div>',
            components: {nf: NotificationForm},
        }, {stubs: {AccessIdsDropdown: {template: '<div />'}}});
        await wrapper.find('input').setValue('test');
        expect(wrapper.vm.notification.isValid).toEqual(true);
        expect(wrapper.vm.notification.body).toBeUndefined();
    });
});
