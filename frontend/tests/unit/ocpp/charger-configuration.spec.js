// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later
import {mount, flushPromises} from '@vue/test-utils';
import {createApp, nextTick, h} from 'vue';
import ChargerConfigurationModal from '@/account/integrations/ocpp/charger-configuration-modal.vue';
import ChargerConnectionSettings from '@/account/integrations/ocpp/charger-connection-settings.vue';
import VirtualChannelListPage from '@/account/integrations/data-sources/virtual-channel-list-page.vue';
import {createTestingPinia} from '@pinia/testing';
import {api} from '@/api/api';

vi.mock('@/api/api', () => ({api: {post: vi.fn(), put: vi.fn()}}));
vi.mock('vue-i18n', async (importOriginal) => ({...(await importOriginal()), useI18n: () => ({t: (key) => key})}));
vi.mock('@/stores/locations-store', async (importOriginal) => ({
  ...(await importOriginal()),
  useLocations: () => ({list: [{id: 5, caption: 'Garage'}]}),
}));

const connection = {
  id: 7,
  iodeviceId: 8,
  name: 'Garage',
  url: 'wss://ocpp.example.org/U1-ABCD',
  baseUrl: 'wss://ocpp.example.org',
  chargePointIdentity: 'U1-ABCD',
  username: 'U1-ABCD',
};
const key = '00112233445566778899AABBCCDDEEFF';
const modalOptions = {
  global: {
    stubs: {
      CopyButton: {props: ['text'], template: '<button />'},
    },
  },
};

describe('Charger configuration', () => {
  const cleanups = [];
  beforeEach(() => vi.clearAllMocks());
  afterEach(() => cleanups.splice(0).forEach((cleanup) => cleanup()));

  // Mount with the same compat runtime as the SFCs (the shared VTU setup also loads native Vue).
  function mountModal(station) {
    const element = document.createElement('div');
    document.body.appendChild(element);
    const created = vi.fn();
    const updated = vi.fn();
    const closed = vi.fn();
    const app = createApp(ChargerConfigurationModal, {station, onCreated: created, onUpdated: updated, onClose: closed});
    app.config.globalProperties.$t = (key) => key;
    app.component('fa', {render: () => h('span')});
    app.mount(element);
    cleanups.push(() => {
      app.unmount();
      element.remove();
    });
    return {element, created, updated, closed};
  }

  it('groups the displayed key but copies the exact gateway-compatible key', () => {
    const wrapper = mount(ChargerConnectionSettings, {...modalOptions, props: {connection: {...connection, authorizationKey: key}}});
    expect(wrapper.get('#ocpp-key').element.value).toBe('0011-2233-4455-6677-8899-AABB-CCDD-EEFF');
    expect(wrapper.findAllComponents({name: 'CopyButton'}).at(-1).props('text')).toBe(key);
    expect(wrapper.get('#ocpp-url').element.value).toBe(connection.url);
  });

  it('creates once, then keeps the one-time key visible without navigating away', async () => {
    api.post.mockResolvedValue({body: {...connection, authorizationKey: key}});
    const {element, created, closed} = mountModal();
    const name = element.querySelector('#ocpp-name');
    name.value = 'Garage';
    name.dispatchEvent(new Event('input', {bubbles: true}));
    const location = element.querySelector('#ocpp-location');
    location.value = '5';
    location.dispatchEvent(new Event('change', {bubbles: true}));
    await nextTick();
    element.querySelector('button.btn-green').click();
    await flushPromises();
    await nextTick();
    expect(api.post).toHaveBeenCalledWith('integrations/ocpp', {name: 'Garage', locationId: 5}, {skipErrorHandler: true});
    expect(created).toHaveBeenCalledOnce();
    expect(element.querySelector('#ocpp-name').value).toBe('Garage');
    expect(element.querySelector('#ocpp-key').value).toContain('0011-2233');
    expect(closed).not.toHaveBeenCalled();
  });

  it('saves a changed charger name as the device caption', async () => {
    const device = {id: connection.iodeviceId, name: connection.name, comment: 'Home charger'};
    api.put.mockResolvedValue({body: device});
    const {element, updated} = mountModal(connection);
    const name = element.querySelector('#ocpp-name');
    expect(name.value).toBe('Garage');
    const saveButton = element.querySelector('button.btn-green');
    expect(saveButton.disabled).toBe(true);
    name.value = ' Home charger ';
    name.dispatchEvent(new Event('input', {bubbles: true}));
    await nextTick();
    expect(saveButton.disabled).toBe(false);
    saveButton.click();
    await flushPromises();
    expect(api.put).toHaveBeenCalledWith('iodevices/8?safe=1', {comment: 'Home charger'}, {skipErrorHandler: true});
    expect(updated).toHaveBeenCalledWith(device);
    expect(name.value).toBe('Home charger');
    expect(saveButton.disabled).toBe(true);
  });

  it('does not send an empty name and shows a save error', async () => {
    api.put.mockRejectedValue({});
    const {element, updated} = mountModal(connection);
    const name = element.querySelector('#ocpp-name');
    const saveButton = element.querySelector('button.btn-green');
    name.value = ' ';
    name.dispatchEvent(new Event('input', {bubbles: true}));
    await nextTick();
    expect(saveButton.disabled).toBe(true);
    name.value = 'New name';
    name.dispatchEvent(new Event('input', {bubbles: true}));
    await nextTick();
    saveButton.click();
    await flushPromises();
    expect(element.querySelector('[role="alert"]').textContent).toContain('Could not save charger name. Try again in a while.');
    expect(updated).not.toHaveBeenCalled();
  });

  it('requires confirmation before replacing an existing key', async () => {
    api.post.mockResolvedValue({body: {...connection, authorizationKey: key}});
    const {element} = mountModal(connection);
    expect(element.querySelector('#ocpp-key')).toBeNull();
    element.querySelector('button.btn-default').click();
    await nextTick();
    expect(api.post).not.toHaveBeenCalled();
    element.querySelector('button.btn-danger').click();
    await flushPromises();
    await nextTick();
    expect(api.post).toHaveBeenCalledWith('integrations/ocpp/7/authorization-key', {}, {skipErrorHandler: true});
    expect(element.querySelector('#ocpp-key')).not.toBeNull();
  });

  it('does not show charger channels on the data sources page', () => {
    const channels = [
      {id: 1, isVirtual: true, virtualType: 'DATA_SOURCE', config: {}},
      {id: 2, isVirtual: true, virtualType: 'OCPP', config: {}},
      {id: 3, isVirtual: false, virtualType: null, config: {}},
    ];
    const wrapper = mount(VirtualChannelListPage, {
      shallow: true,
      global: {plugins: [createTestingPinia({initialState: {channels: {ids: [1, 2, 3], all: Object.fromEntries(channels.map((ch) => [ch.id, ch]))}}})]},
    });
    expect(wrapper.vm.allVirtualDataSources.map((ch) => ch.id)).toEqual([1]);
  });
});
