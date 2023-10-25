import {mount} from '@vue/test-utils'
import ChannelStateTable from '@/channels/channel-state-table.vue'

describe('ChannelStateTable', () => {
    describe('Thermometer', function () {
        it('renders channel temperature', () => {
            const wrapper = mount(ChannelStateTable, {
                propsData: {channel: {type: {}}, state: {temperature: 12}},
            })
            expect(wrapper.text()).toMatch('Temperature 12°C')
        });

        it('does not show channel temperature if state is -273', () => {
            const wrapper = mount(ChannelStateTable, {
                propsData: {channel: {type: {}}, state: {temperature: -273}},
            })
            expect(wrapper.text()).toMatch('Temperature ?°C')
        });
    });
})
