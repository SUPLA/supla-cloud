import {beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import {api} from '@/api/api.js';
import {useDevicesStore} from '@/stores/devices-store';
import IdentifyDeviceButton from '@/devices/details/device-identify-device-button.vue';

vi.mock('@/api/api.js', () => ({api: {patch: vi.fn()}}));

describe('device identify submission feedback', () => {
  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
  });

  it('shows queued acceptance without starting the immediate-path timer', async () => {
    const device = {id: 7, flags: {identifyDeviceAvailable: true}, calcfgQueueableActions: {identifyDevice: true}};
    useDevicesStore().all[device.id] = {...device, connected: false};
    api.patch.mockResolvedValue({body: {actionResult: {status: 'QUEUED', id: 7, queuedAt: 1793577600}}});

    const wrapper = mount(IdentifyDeviceButton, {props: {device}});
    await wrapper.find('button').trigger('click');
    await flushPromises();

    expect(wrapper.text()).toContain('calcfg_feedback_QUEUED');
    expect(wrapper.text()).not.toContain('Identify request sent.');
    expect(wrapper.find('button').element.disabled).toBe(false);
  });

  it('keeps an offline non-eligible device gated', () => {
    const device = {id: 7, flags: {identifyDeviceAvailable: true}, calcfgQueueableActions: {identifyDevice: false}};
    useDevicesStore().all[device.id] = {...device, connected: false};

    const wrapper = mount(IdentifyDeviceButton, {props: {device}});

    expect(wrapper.find('button').element.disabled).toBe(true);
    expect(api.patch).not.toHaveBeenCalled();
  });

  it('releases loading after an HTTP rejection without showing acceptance feedback', async () => {
    const device = {id: 7, flags: {identifyDeviceAvailable: true}, calcfgQueueableActions: {identifyDevice: true}};
    useDevicesStore().all[device.id] = {...device, connected: true};
    api.patch.mockRejectedValue({status: 503});

    const wrapper = mount(IdentifyDeviceButton, {props: {device}});
    await wrapper.find('button').trigger('click');
    await flushPromises();

    expect(wrapper.find('.alert').exists()).toBe(false);
    expect(wrapper.find('button').element.disabled).toBe(false);
  });

  it('clears immediate feedback before a deferred repeat and does not retain its timer', async () => {
    vi.useFakeTimers();
    const device = {id: 7, flags: {identifyDeviceAvailable: true}, calcfgQueueableActions: {identifyDevice: true}};
    useDevicesStore().all[device.id] = {...device, connected: true};
    api.patch.mockResolvedValueOnce({body: {}}).mockResolvedValueOnce({body: {actionResult: {status: 'WAITING_FOR_RESULT'}}});

    const wrapper = mount(IdentifyDeviceButton, {props: {device}});
    await wrapper.find('button').trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('Identify request sent.');

    await wrapper.find('button').trigger('click');
    await flushPromises();
    expect(wrapper.text()).toContain('calcfg_feedback_WAITING_FOR_RESULT');
    expect(wrapper.text()).not.toContain('Identify request sent.');

    wrapper.unmount();
    vi.runOnlyPendingTimers();
    vi.useRealTimers();
  });
});
