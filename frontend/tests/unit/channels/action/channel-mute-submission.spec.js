import {beforeEach, describe, expect, it, vi} from 'vitest';
import {flushPromises, mount} from '@vue/test-utils';
import ChannelMuteAlarmButton from '@/channels/action/channel-mute-alarm-button.vue';
import {channelsApi} from '@/api/channels-api';

const mocks = vi.hoisted(() => ({fetchStates: vi.fn()}));
vi.mock('@/api/channels-api', () => ({channelsApi: {muteAlarm: vi.fn()}}));
vi.mock('@/stores/channels-store', () => ({useChannelsStore: vi.fn(() => ({fetchStates: mocks.fetchStates}))}));

describe('channel mute submission feedback', () => {
  beforeEach(() => vi.clearAllMocks());

  it('shows queued acceptance without polling for updated channel state', async () => {
    const channel = {
      id: 4,
      connected: false,
      calcfgQueueableActions: {muteAlarm: true},
      state: {soundAlarmOn: true},
    };
    channelsApi.muteAlarm.mockResolvedValue({body: {actionResult: {status: 'QUEUED', id: 4, queuedAt: 1793577600}}});

    const wrapper = mount(ChannelMuteAlarmButton, {props: {channel}, global: {stubs: {'transition-expand': true, fa: true, 'button-loading-dots': true}}});
    await wrapper.find('button').trigger('click');
    await flushPromises();
    await wrapper.vm.$nextTick();

    expect(channelsApi.muteAlarm).toHaveBeenCalledWith(channel);
    await expect(channelsApi.muteAlarm.mock.results[0].value).resolves.toEqual({body: {actionResult: {status: 'QUEUED', id: 4, queuedAt: 1793577600}}});
    await wrapper.vm.$nextTick();
    expect(wrapper.text()).toContain('calcfg_feedback_QUEUED');
    expect(wrapper.text()).not.toContain('Mute command sent');
    expect(mocks.fetchStates).not.toHaveBeenCalled();
    expect(wrapper.find('button').element.disabled).toBe(false);
  });

  it('keeps a disconnected channel without queue eligibility disabled', () => {
    const channel = {
      id: 4,
      connected: false,
      calcfgQueueableActions: {muteAlarm: false},
      state: {soundAlarmOn: true},
    };

    const wrapper = mount(ChannelMuteAlarmButton, {props: {channel}, global: {stubs: {'transition-expand': true, fa: true, 'button-loading-dots': true}}});

    expect(wrapper.find('button').element.disabled).toBe(true);
    expect(channelsApi.muteAlarm).not.toHaveBeenCalled();
  });

  it('offers mute when an offline eligible channel has no live alarm state', () => {
    const channel = {id: 4, connected: false, calcfgQueueableActions: {muteAlarm: true}, state: {}};
    const wrapper = mount(ChannelMuteAlarmButton, {props: {channel}, global: {stubs: {Fa: true, ButtonLoadingDots: true}}});
    expect(wrapper.find('button').exists()).toBe(true);
    expect(wrapper.find('button').element.disabled).toBe(false);
  });

  it('updates button visibility when the channel state changes', async () => {
    const wrapper = mount(ChannelMuteAlarmButton, {props: {channel: {id: 4, connected: true, state: {soundAlarmOn: false}}}});
    expect(wrapper.find('button').exists()).toBe(false);
    await wrapper.setProps({channel: {id: 4, connected: true, state: {soundAlarmOn: true}}});
    expect(wrapper.find('button').exists()).toBe(true);
  });

  it('cancels the delayed state refresh when unmounted', async () => {
    vi.useFakeTimers();
    try {
      channelsApi.muteAlarm.mockResolvedValueOnce({body: {actionResult: {status: 'ACCEPTED'}}});
      const wrapper = mount(ChannelMuteAlarmButton, {props: {channel: {id: 4, connected: true, state: {soundAlarmOn: true}}}});
      await wrapper.find('button').trigger('click');
      expect(wrapper.text()).toContain('Mute command sent');
      wrapper.unmount();
      await vi.advanceTimersByTimeAsync(4000);
      expect(mocks.fetchStates).not.toHaveBeenCalled();
    } finally {
      vi.useRealTimers();
    }
  });

  it('clears prior sent feedback and its refresh when a repeat is deferred', async () => {
    vi.useFakeTimers();
    try {
      const channel = {id: 4, connected: true, state: {soundAlarmOn: true}};
      channelsApi.muteAlarm.mockResolvedValueOnce({body: {actionResult: {status: 'ACCEPTED'}}});
      channelsApi.muteAlarm.mockResolvedValueOnce({body: {actionResult: {status: 'WAITING_FOR_RESULT'}}});
      const wrapper = mount(ChannelMuteAlarmButton, {props: {channel}});
      await wrapper.find('button').trigger('click');
      await vi.waitFor(() => expect(wrapper.text()).toContain('Mute command sent'));
      await wrapper.find('button').trigger('click');
      await vi.waitFor(() => expect(wrapper.text()).toContain('calcfg_feedback_WAITING_FOR_RESULT'));
      expect(wrapper.text()).not.toContain('Mute command sent');
      await vi.advanceTimersByTimeAsync(4000);
      expect(mocks.fetchStates).not.toHaveBeenCalled();
      wrapper.unmount();
    } finally {
      vi.useRealTimers();
    }
  });
});
