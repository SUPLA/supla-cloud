import {mount} from '@vue/test-utils'
import NotificationForm from "@/notifications/notification-form.vue";

describe('NotificationForm', () => {
    it('renders the form', () => {
        const wrapper = mount(NotificationForm, {
            propsData: {value: {}},
        });
        const actions = wrapper.findAll('.form-group');
        expect(actions.length).toBe(2);
    });

    it('validates at mount', async () => {
        let valid;
        mount(NotificationForm, {
            propsData: {value: {}},
            listeners: {
                isValid: (isValid) => valid = isValid,
            }
        });
        expect(valid).toEqual(false)
    });

    it('updates after change', async () => {
        const wrapper = mount({
            data: () => ({notification: {}, valid: false}),
            template: '<div><nf v-model="notification" @isValid="valid = $event"/></div>',
            components: {nf: NotificationForm},
        });
        await wrapper.find('input').setValue('test');
        expect(wrapper.vm.notification).toEqual({title: 'test'});
        expect(wrapper.vm.valid).toBeTruthy();
    });

    it('maintains notification id and other fields', () => {
        const wrapper = mount({
            data: () => ({notification: {id: 123, title: 'rainbow', property: 'unicorn'}, valid: false}),
            template: '<div><nf v-model="notification" @isValid="valid = $event"/></div>',
            components: {nf: NotificationForm},
        });
        expect(wrapper.vm.notification.id).toEqual(123);
        expect(wrapper.vm.notification.title).toEqual('rainbow');
        expect(wrapper.vm.notification.property).toEqual('unicorn');
        expect(wrapper.vm.valid).toBeTruthy();
    });
});
