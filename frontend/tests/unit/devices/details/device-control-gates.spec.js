import {beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, shallowMount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {api} from '@/api/api.js';
import {devicesApi} from '@/api/devices-api';
import {useDevicesStore} from '@/stores/devices-store';
import DeviceRemoteRestartButton from '@/devices/details/device-remote-restart-button.vue';
import DeviceRemoteFactoryResetButton from '@/devices/details/device-remote-factory-reset-button.vue';
import DevicePairSubdeviceButton from '@/devices/details/device-pair-subdevice-button.vue';

vi.mock('@/api/api.js', () => ({api: {patch: vi.fn()}}));
vi.mock('@/api/devices-api', () => ({devicesApi: {factoryReset: vi.fn(), getOne: vi.fn(), pairSubdevice: vi.fn()}}));

let pinia;

const device = (overrides = {}) => ({
  id: 11,
  connected: false,
  flags: {remoteRestartAvailable: true, factoryResetSupported: true, pairingSubdevicesAvailable: true},
  calcfgQueueableActions: {restartDevice: true, factoryReset: true},
  ...overrides,
});

describe('non-password device control gates', () => {
  beforeEach(() => {
    pinia = createPinia();
    setActivePinia(pinia);
    vi.clearAllMocks();
    devicesApi.getOne.mockResolvedValue({pairingResult: null});
  });

  it('allows an offline eligible restart, but still blocks it while settings are unsaved', async () => {
    const offline = device();
    useDevicesStore().all[offline.id] = {...offline};
    const wrapper = shallowMount(DeviceRemoteRestartButton, {props: {device: offline}, global: {plugins: [pinia]}});

    expect(wrapper.find('button').element.disabled).toBe(false);
    await wrapper.find('button').trigger('click');
    expect(wrapper.vm.restartConfirm).toBe(true);
    wrapper.unmount();

    const unsaved = device({hasPendingChanges: true});
    useDevicesStore().all[unsaved.id] = {...unsaved};
    const blocked = shallowMount(DeviceRemoteRestartButton, {props: {device: unsaved}, global: {plugins: [pinia]}});
    expect(blocked.find('button').element.disabled).toBe(true);
    expect(blocked.vm.disabledReason).toBe('Save or discard configuration changes first.');
    expect(api.patch).not.toHaveBeenCalled();
  });

  it('keeps restart gated offline when the backend does not list that action', () => {
    const offline = device({calcfgQueueableActions: {restartDevice: false}});
    useDevicesStore().all[offline.id] = {...offline};
    const wrapper = shallowMount(DeviceRemoteRestartButton, {props: {device: offline}, global: {plugins: [pinia]}});

    expect(wrapper.find('button').element.disabled).toBe(true);
    expect(api.patch).not.toHaveBeenCalled();
  });

  it('requires explicit confirmation before submitting a destructive factory reset', async () => {
    const offline = device();
    const wrapper = shallowMount(DeviceRemoteFactoryResetButton, {props: {device: offline}});

    expect(wrapper.find('button').element.disabled).toBe(false);
    expect(devicesApi.factoryReset).not.toHaveBeenCalled();
    await wrapper.find('button').trigger('click');
    expect(wrapper.vm.showConfirmDialog).toBe(true);
    expect(devicesApi.factoryReset).not.toHaveBeenCalled();
    await wrapper.vm.factoryResetDevice();
    await flushPromises();
    expect(devicesApi.factoryReset).toHaveBeenCalledWith(offline.id);
  });

  it('does not relax the disconnected gate for subdevice pairing', async () => {
    const offline = device({calcfgQueueableActions: {pairSubdevice: true}});
    const wrapper = shallowMount(DevicePairSubdeviceButton, {
      props: {device: offline},
      global: {
        stubs: {
          FormButton: {
            props: ['disabledReason', 'loading'],
            template: '<div :data-disabled-reason="disabledReason" />',
          },
          Fa: true,
          ButtonLoadingDots: true,
        },
      },
    });
    await flushPromises();

    expect(wrapper.find('[data-disabled-reason]').attributes('data-disabled-reason')).toBe('Device is disconnected.');
    expect(devicesApi.pairSubdevice).not.toHaveBeenCalled();
  });

  it('hides the remote controls when a device does not advertise support', () => {
    const unsupported = device({flags: {remoteRestartAvailable: false, factoryResetSupported: false}});
    useDevicesStore().all[unsupported.id] = {...unsupported};

    expect(
      shallowMount(DeviceRemoteRestartButton, {props: {device: unsupported}, global: {plugins: [pinia]}})
        .find('button')
        .exists()
    ).toBe(false);
    expect(
      shallowMount(DeviceRemoteFactoryResetButton, {props: {device: unsupported}})
        .find('button')
        .exists()
    ).toBe(false);
  });
});
