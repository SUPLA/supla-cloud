import {beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {devicesApi} from '@/api/devices-api';
import {errorNotification, successNotification} from '@/common/notifier';
import {useDevicesStore} from '@/stores/devices-store';
import {promiseTimeout} from '@vueuse/core';
import {waitForDeviceOperation} from '@/devices/details/device-details-helpers';
import DeviceSetCfgPasswordButton from '@/devices/details/device-set-cfg-password-button.vue';
import DeviceOtaUpdatesButtons from '@/devices/details/device-ota-updates-buttons.vue';

vi.mock('@/api/devices-api', () => ({devicesApi: {setCfgModePassword: vi.fn(), otaCheckUpdates: vi.fn(), otaPerformUpdate: vi.fn()}}));
vi.mock('@/common/notifier', () => ({errorNotification: vi.fn(), successNotification: vi.fn()}));
vi.mock('@/stores/devices-store', () => ({useDevicesStore: vi.fn()}));
vi.mock('@vueuse/core', () => ({promiseTimeout: vi.fn()}));
vi.mock('@/devices/details/device-details-helpers', () => ({waitForDeviceOperation: vi.fn()}));
vi.mock('@/common/modal-confirm.vue', () => ({
  default: {
    props: ['header', 'loading'],
    emits: ['cancel', 'confirm'],
    template:
      '<div class="modal-confirm"><slot /><button class="confirm" @click="$emit(\'confirm\')">Confirm</button><button class="cancel" @click="$emit(\'cancel\')">Cancel</button></div>',
  },
}));
vi.mock('@/common/gui/FormButton.vue', () => ({
  default: {
    props: ['disabledReason', 'loading'],
    emits: ['click'],
    template: '<button :disabled="!!disabledReason || loading" @click="$emit(\'click\')"><slot /></button>',
  },
}));

const device = () => ({
  id: 7,
  locked: false,
  connected: true,
  flags: {setCfgModePasswordSupported: true, automaticFirmwareUpdatesSupported: true},
  config: {otaUpdate: {status: 'UNKNOWN'}},
  calcfgQueueableActions: {setCfgModePassword: true, otaCheckUpdates: true, otaPerformUpdate: true},
});

const mountPassword = (props = device()) =>
  mount(DeviceSetCfgPasswordButton, {
    props: {device: props},
    global: {config: {compatConfig: {MODE: 3}}, directives: {tooltip: {mounted() {}}}},
  });

const mountOta = (props = device()) =>
  mount(DeviceOtaUpdatesButtons, {
    props: {device: props},
    global: {directives: {tooltip: {mounted() {}}}},
  });

const triggerAndRender = async (wrapper, target) => {
  await target.trigger('click');
  await flushPromises();
  wrapper.vm.$forceUpdate();
  await flushPromises();
};

const fillPasswords = async (wrapper, password, confirmation) => {
  const fields = wrapper.findAll('input[type="password"]');
  fields[0].element.value = password;
  await fields[0].trigger('input');
  await fields[0].trigger('change');
  fields[1].element.value = confirmation;
  await fields[1].trigger('input');
  await fields[1].trigger('change');
  await flushPromises();
};

describe('password and firmware submission workflows', () => {
  let fetchDevice;

  beforeEach(() => {
    vi.clearAllMocks();
    fetchDevice = vi.fn().mockResolvedValue(undefined);
    useDevicesStore.mockReturnValue({fetchDevice});
    promiseTimeout.mockResolvedValue(undefined);
    waitForDeviceOperation.mockResolvedValue(true);
  });

  it('rejects a mismatched password, then permits correcting and repeating the submission', async () => {
    const wrapper = mountPassword();
    await triggerAndRender(wrapper, wrapper.find('button'));
    await fillPasswords(wrapper, 'StrongPass1', 'DifferentPass1');
    await triggerAndRender(wrapper, wrapper.find('.confirm'));
    await flushPromises();

    expect(errorNotification).toHaveBeenCalledWith('Passwords do not match.');
    expect(devicesApi.setCfgModePassword).not.toHaveBeenCalled();

    await fillPasswords(wrapper, 'StrongPass1', 'StrongPass1');
    devicesApi.setCfgModePassword.mockResolvedValueOnce({body: {actionResult: {status: 'QUEUED'}}});
    await triggerAndRender(wrapper, wrapper.find('.confirm'));
    await flushPromises();

    expect(devicesApi.setCfgModePassword).toHaveBeenCalledTimes(1);
    expect(wrapper.text()).toContain('calcfg_feedback_QUEUED');
    expect(promiseTimeout).not.toHaveBeenCalled();
    expect(waitForDeviceOperation).not.toHaveBeenCalled();
    expect(fetchDevice).not.toHaveBeenCalled();
    expect(wrapper.findAll('input[type="password"]').length).toBe(0);
  });

  it('explains a waiting password request and clears secret fields on cancel or HTTP failure', async () => {
    const wrapper = mountPassword();
    await triggerAndRender(wrapper, wrapper.find('button'));
    await fillPasswords(wrapper, 'StrongPass1', 'StrongPass1');
    devicesApi.setCfgModePassword.mockResolvedValueOnce({body: {actionResult: {status: 'WAITING_FOR_RESULT'}}});
    await triggerAndRender(wrapper, wrapper.find('.confirm'));
    await flushPromises();
    expect(wrapper.text()).toContain('calcfg_feedback_WAITING_FOR_RESULT_PASSWORD');
    expect(promiseTimeout).not.toHaveBeenCalled();
    expect(waitForDeviceOperation).not.toHaveBeenCalled();

    await wrapper.find('button').trigger('click');
    await fillPasswords(wrapper, 'StrongPass2', 'StrongPass2');
    devicesApi.setCfgModePassword.mockRejectedValueOnce({status: 503});
    await wrapper.find('.confirm').trigger('click');
    await flushPromises();
    expect(errorNotification).toHaveBeenCalledWith('Could not change the password.');
    expect(wrapper.findAll('input[type="password"]')[0].element.value).toBe('');
    await triggerAndRender(wrapper, wrapper.find('.cancel'));
    await triggerAndRender(wrapper, wrapper.find('button'));
    expect(wrapper.findAll('input[type="password"]')[0].element.value).toBe('');
  });

  it('retains immediate password verification for ACCEPTED receipts', async () => {
    const props = device();
    props.config.setCfgModePassword = {status: 'TRUE'};
    waitForDeviceOperation.mockResolvedValueOnce(true);
    const wrapper = mountPassword(props);
    await triggerAndRender(wrapper, wrapper.find('button'));
    await fillPasswords(wrapper, 'StrongPass1', 'StrongPass1');
    devicesApi.setCfgModePassword.mockResolvedValueOnce({body: {actionResult: {status: 'ACCEPTED'}}});
    await triggerAndRender(wrapper, wrapper.find('.confirm'));
    await flushPromises();

    expect(promiseTimeout).toHaveBeenCalledWith(1000);
    expect(fetchDevice).toHaveBeenCalledWith(7);
    expect(waitForDeviceOperation).toHaveBeenCalledTimes(1);
    expect(successNotification).toHaveBeenCalledWith('Password was set successfully.');
  });

  it('does not poll after a queued firmware check, but preserves polling without a receipt', async () => {
    const wrapper = mountOta();
    devicesApi.otaCheckUpdates.mockResolvedValueOnce({body: {actionResult: {status: 'QUEUED'}}});
    await triggerAndRender(wrapper, wrapper.findAll('button')[0]);
    expect(wrapper.text()).toContain('calcfg_feedback_QUEUED');
    expect(promiseTimeout).not.toHaveBeenCalled();
    expect(waitForDeviceOperation).not.toHaveBeenCalled();
    expect(fetchDevice).not.toHaveBeenCalled();

    devicesApi.otaCheckUpdates.mockResolvedValueOnce({body: {}});
    await triggerAndRender(wrapper, wrapper.findAll('button')[0]);
    expect(promiseTimeout).toHaveBeenCalledWith(1000);
    expect(fetchDevice).toHaveBeenCalledWith(7);
    expect(waitForDeviceOperation).toHaveBeenCalledTimes(1);
  });

  it('skips fixed-delay update narration for deferred firmware work', async () => {
    const wrapper = mountOta();
    devicesApi.otaPerformUpdate.mockResolvedValueOnce({body: {actionResult: {status: 'WAITING_FOR_RESULT'}}});
    await triggerAndRender(wrapper, wrapper.findAll('button')[1]);
    await triggerAndRender(wrapper, wrapper.find('.confirm'));

    expect(wrapper.text()).toContain('calcfg_feedback_WAITING_FOR_RESULT');
    expect(promiseTimeout).not.toHaveBeenCalled();
    expect(fetchDevice).not.toHaveBeenCalled();
    expect(successNotification).not.toHaveBeenCalled();
  });
});
