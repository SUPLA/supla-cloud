import {mount} from '@vue/test-utils';
import ChannelStateTable from '@/channels/channel-state-table.vue';
import {setActivePinia} from 'pinia';
import {createTestingPinia} from '@pinia/testing';

describe('ChannelStateTable', () => {
  beforeEach(() => {
    setActivePinia(createTestingPinia());
  });

  describe('Thermometer', function () {
    it('renders channel temperature', () => {
      const wrapper = mount(ChannelStateTable, {
        propsData: {channel: {type: {}}, state: {temperature: 12}},
      });
      expect(wrapper.text()).toMatch('Temperature12°C');
    });

    it('does not show channel temperature if state is -273', () => {
      const wrapper = mount(ChannelStateTable, {
        propsData: {channel: {type: {}}, state: {temperature: -273}},
      });
      expect(wrapper.text()).toMatch('Temperature?°C');
    });
  });
});
