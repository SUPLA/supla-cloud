import {beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {devicesApi} from '@/api/devices-api';
import {useDevicesStore} from '@/stores/devices-store';
import DeviceDetailsTab from '@/devices/details/device-details-tab.vue';
import DeviceDetailsTabBasicSettings from '@/devices/details/device-details-tab-basic-settings.vue';

vi.mock('@/api/api.js', () => ({api: {patch: vi.fn()}}));
vi.mock('@/api/devices-api', () => ({devicesApi: {update: vi.fn()}}));

const device = {
  id: 11,
  comment: 'Original',
  enabled: true,
  config: {},
  connected: true,
  locked: false,
  flags: {remoteRestartAvailable: true},
  calcfgQueueableActions: {restartDevice: true},
};

describe('device settings and remote controls boundary', () => {
  let pinia;

  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
    devicesApi.update.mockResolvedValue({});
    const store = useDevicesStore();
    store.all[device.id] = {...device};
    vi.spyOn(store, 'fetchDevice').mockResolvedValue();
  });

  it('gates remote controls for edits and clears the gate on cancel and save', async () => {
    const wrapper = mount(DeviceDetailsTab, {
      props: {device},
      global: {
        plugins: [pinia],
        mocks: {$t: (value) => value},
        stubs: {
          DeviceDetailsTabLocation: true,
          DeviceDetailsTabFirmwareInfo: true,
          AccordionRoot: {template: '<div><slot /></div>'},
          AccordionItem: {template: '<div><slot /></div>'},
          DeviceDetailsTabSettingsLed: true,
          DeviceDetailsTabSettingsUpdates: true,
          DeviceDetailsTabSettingsModbus: true,
          DeviceDetailsTabSettingsUserInterface: true,
          DeviceDetailsTabSettingsDatetime: true,
          TransitionExpand: {template: '<div><slot /></div>'},
          DependenciesWarningModal: true,
          DeviceEnterConfigurationModeButton: true,
          DeviceIdentifyDeviceButton: true,
          DeviceRemoteFactoryResetButton: true,
          DeviceSetCfgPasswordButton: true,
        },
      },
    });
    const restartButton = () => wrapper.findAll('button').find((button) => button.text().includes('Restart the device'));

    expect(restartButton().element.disabled).toBe(false);
    wrapper.findComponent(DeviceDetailsTabBasicSettings).vm.$emit('change', {comment: 'Changed'});
    await wrapper.vm.$nextTick();
    expect(restartButton().element.disabled).toBe(true);
    expect(device.hasPendingChanges).toBeUndefined();

    await wrapper.get('button[type="button"]').trigger('click');
    await flushPromises();
    expect(restartButton().element.disabled).toBe(false);

    wrapper.findComponent(DeviceDetailsTabBasicSettings).vm.$emit('change', {comment: 'Changed again'});
    await wrapper.vm.$nextTick();
    expect(restartButton().element.disabled).toBe(true);
    await wrapper.get('form').trigger('submit');
    await flushPromises();
    expect(devicesApi.update).toHaveBeenCalledOnce();
    expect(restartButton().element.disabled).toBe(false);
  });
});
