import {describe, expect, it, vi} from 'vitest';
import {mount} from '@vue/test-utils';
import ChannelExtendedStateDisplay from '@/channels/action/channel-extended-state-display.vue';

const mocks = vi.hoisted(() => ({channels: [], devices: {}}));
vi.mock('@/stores/channels-store', () => ({useChannelsStore: () => ({list: mocks.channels})}));
vi.mock('@/stores/devices-store', () => ({useDevicesStore: () => ({all: mocks.devices})}));
vi.mock('@/api/use-supla-api', () => ({useSuplaApi: () => ({patch: () => ({execute: vi.fn()})})}));
vi.mock('@vueuse/core', () => ({useTimeoutPoll: vi.fn()}));
vi.mock('vue-i18n', () => ({useI18n: () => ({t: (key) => key})}));

function renderChannel(extendedState, {connected = true, config = {isBatteryAvailable: true}} = {}) {
  mocks.devices = {1: {id: 1, connected}};
  const channel = {id: 2, iodeviceId: 1, config, state: {extendedState}};
  return mount(ChannelExtendedStateDisplay, {props: {channel, condensed: true}});
}

describe('extended battery state presentation', () => {
  it('keeps percentage, power and health presentation when both level and state are reported', () => {
    const wrapper = renderChannel({batteryLevel: 72, batteryState: 'LOW', deviceBatteryLevel: true, batteryPowered: true, batteryHealth: 95});
    expect(wrapper.findAll('dd').map((element) => element.text())).toEqual(['battery', '72%', '95%']);
    expect(wrapper.findAll('dt').map((element) => element.text())).toEqual(['Power supply', 'Battery level', 'Battery health']);
    expect(wrapper.text()).not.toContain('Low battery');
  });

  it.each([
    ['OK', 'OK', false],
    ['LOW', 'Low battery', true],
  ])('displays %s without a fabricated percentage', (status, text, warning) => {
    const wrapper = renderChannel({batteryState: status});
    expect(wrapper.find('dt').text()).toBe('Battery status');
    expect(wrapper.find('dd').text()).toBe(text);
    expect(wrapper.text()).not.toContain('%');
    expect(wrapper.find('.label-warning').exists()).toBe(warning);
  });

  it('identifies device-wide state without inferring it for other channels', () => {
    const wrapper = renderChannel({batteryState: 'LOW', deviceBatteryState: true});
    expect(wrapper.find('dt').text()).toBe('Device battery status');
    expect(wrapper.find('dd').text()).toBe('Low battery');
    expect(renderChannel({deviceBatteryState: true}).text()).toBe('');
  });

  it('treats a false device marker as channel scope', () => {
    const wrapper = renderChannel({batteryState: 'OK', deviceBatteryState: false});
    expect(wrapper.find('dt').text()).toBe('Battery status');
    expect(wrapper.find('dd').text()).toBe('OK');
  });

  it('does not show a channel-scoped state in the device view', () => {
    mocks.devices = {1: {id: 1, connected: true}};
    mocks.channels = [{id: 2, iodeviceId: 1, state: {extendedState: {batteryState: 'OK'}}}];
    expect(mount(ChannelExtendedStateDisplay, {props: {device: {id: 1}}}).text()).toBe('');
  });

  it('shows explicitly device-wide status from the device view source', () => {
    mocks.devices = {1: {id: 1, connected: true}};
    mocks.channels = [{id: 2, iodeviceId: 1, state: {extendedState: {batteryState: 'LOW', deviceBatteryState: true}}}];
    const wrapper = mount(ChannelExtendedStateDisplay, {props: {device: {id: 1}}});
    expect(wrapper.find('dt').text()).toBe('Device battery status');
    expect(wrapper.find('dd').text()).toBe('Low battery');
  });

  it('does not copy a later channel report to the device view source', () => {
    mocks.devices = {1: {id: 1, connected: true}};
    mocks.channels = [
      {id: 2, iodeviceId: 1, state: {extendedState: {}}},
      {id: 3, iodeviceId: 1, state: {extendedState: {batteryState: 'LOW', deviceBatteryState: true}}},
    ];
    const wrapper = mount(ChannelExtendedStateDisplay, {props: {device: {id: 1}}});
    expect(wrapper.text()).toBe('');
  });

  it.each([undefined, {}, {batteryState: 'UNKNOWN'}, {batteryState: true}, {batteryState: false}, {deviceBatteryState: false}])(
    'does not invent status for %j',
    (state) => {
      expect(renderChannel(state).text()).toBe('');
    }
  );

  it('does not treat a false capability as a reported healthy state', () => {
    expect(renderChannel({}, {config: {isBatteryAvailable: false}}).text()).toBe('');
  });

  it('displays an actual report independently of the capability flag', () => {
    const wrapper = renderChannel({batteryState: 'LOW'}, {config: {isBatteryAvailable: false}});
    expect(wrapper.find('dd').text()).toBe('Low battery');
  });

  it('marks offline status as last known even in condensed view', () => {
    const wrapper = renderChannel({batteryState: 'OK'}, {connected: false});
    expect(wrapper.text()).toContain('The device is not connected. The values below represent the last known state.');
    expect(wrapper.find('dt').text()).toBe('Battery status');
    expect(wrapper.find('dd').text()).toBe('OK');
  });

  it('retains the legacy device-level placement', () => {
    expect(renderChannel({batteryForDevice: true, batteryLevel: 72}).text()).toBe('');
    mocks.channels = [{id: 2, iodeviceId: 1, state: {extendedState: {batteryForDevice: true, batteryLevel: 72}}}];
    const wrapper = mount(ChannelExtendedStateDisplay, {props: {device: {id: 1}}});
    expect(wrapper.find('dt').text()).toBe('Battery level');
    expect(wrapper.find('dd').text()).toBe('72%');
  });
});
