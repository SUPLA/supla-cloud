import {mount} from '@vue/test-utils';
import {createTestingPinia} from '@pinia/testing';
import TankParams from '@/channels/params/channel-params-septic-tank.vue';
import ValveParams from '@/channels/params/channel-params-valveopenclose.vue';
import {isChannelReferenceCandidate, localFirstCandidates} from '@/channels/channel-reference-candidates';
import ChannelsDropdown from '@/devices/channels-dropdown.vue';
import {api} from '@/api/api';
import ChannelsIdDropdown from '@/devices/channels-id-dropdown.vue';
import SelectForSubjects from '@/devices/select-for-subjects.vue';

const destination = {id: 10, iodeviceId: 1};
const devices = {1: {name: 'HVAC', flags: {suplanSupported: true}}, 2: {name: 'Remote sensor', connected: false, flags: {suplanSupported: true}}};
const candidate = {
  id: 20,
  caption: 'Sensor',
  iodeviceId: 2,
  locationId: 9,
  connected: false,
  type: {name: 'SENSORNO'},
  function: {name: 'FLOOD_SENSOR'},
  config: {},
};
const roles = [
  ['mainThermometerChannelId', 'THERMOMETER', 'THERMOMETER'],
  ['mainThermometerChannelId', 'HUMIDITYANDTEMPSENSOR', 'HUMIDITYANDTEMPERATURE'],
  ['auxThermometerChannelId', 'THERMOMETER', 'THERMOMETER'],
  ['auxThermometerChannelId', 'HUMIDITYANDTEMPSENSOR', 'HUMIDITYANDTEMPERATURE'],
  ['binarySensorChannelId', 'SENSORNO', 'HOTELCARDSENSOR'],
  ['masterThermostatChannelId', 'HVAC', 'HVAC_THERMOSTAT'],
  ['pumpSwitchChannelId', 'RELAY', 'PUMPSWITCH'],
  ['heatOrColdSourceSwitchChannelId', 'RELAY', 'HEATORCOLDSOURCESWITCH'],
  ['levelSensorChannelIds', 'SENSORNO', 'CONTAINER_LEVEL_SENSOR'],
  ['floodSensorChannelIds', 'SENSORNO', 'FLOOD_SENSOR'],
];

describe('M4 common channel candidates', () => {
  it.each(roles)('offers offline remote and legacy cross-Location local for %s', (role, type, fn) => {
    const source = {...candidate, type: {name: type}, function: {name: fn}};
    expect(isChannelReferenceCandidate(destination, source, devices, role)).toBe(true);
    expect(isChannelReferenceCandidate(destination, {...source, iodeviceId: 1}, {}, role)).toBe(true);
    expect(isChannelReferenceCandidate(destination, source, {...devices, 2: {flags: {suplanSupported: false}}}, role)).toBe(false);
    expect(isChannelReferenceCandidate(destination, source, {...devices, 1: {}}, role)).toBe(false);
    expect(isChannelReferenceCandidate(destination, {...source, id: destination.id}, devices, role)).toBe(false);
    expect(isChannelReferenceCandidate(destination, {...source, type: {name: 'RGBLEDCONTROLLER'}}, devices, role)).toBe(false);
    expect(isChannelReferenceCandidate(destination, {...source, function: {name: 'NONE'}, funcList: [fn]}, devices, role)).toBe(false);
  });
  it.each(roles)('groups and orders local first regardless of API order for %s', (role, type, fn) => {
    const remote = {...candidate, type: {name: type}, function: {name: fn}};
    const local = {...remote, id: 99, iodeviceId: 1};
    for (const order of [
      [remote, local],
      [local, remote],
    ]) {
      expect(localFirstCandidates(destination, order, devices, role).map((ch) => [ch.id, ch.group])).toEqual([
        [99, 'local'],
        [20, 'remote'],
      ]);
    }
  });
  it.each([
    ['pumpSwitchChannelId', 'PUMPSWITCH'],
    ['heatOrColdSourceSwitchChannelId', 'HEATORCOLDSOURCESWITCH'],
  ])('rejects RELAY2XG5LA1A locally and remotely for %s', (role, fn) => {
    const source = {...candidate, type: {name: 'RELAY2XG5LA1A'}, function: {name: fn}};
    expect(isChannelReferenceCandidate(destination, source, devices, role)).toBe(false);
    expect(isChannelReferenceCandidate(destination, {...source, iodeviceId: 1}, {}, role)).toBe(false);
  });
  it('preserves established binary/flood local type compatibility', () => {
    expect(isChannelReferenceCandidate(destination, {...candidate, iodeviceId: 1, function: {name: 'MAILSENSOR'}}, {}, 'floodSensorChannelIds')).toBe(true);
    expect(isChannelReferenceCandidate(destination, {...candidate, function: {name: 'MAILSENSOR'}}, devices, 'floodSensorChannelIds')).toBe(true);
    expect(isChannelReferenceCandidate(destination, {...candidate, type: {name: 'SENSORNC'}}, devices, 'floodSensorChannelIds')).toBe(false);
  });
  it('rejects a thermostat that already has a master', () => {
    expect(
      isChannelReferenceCandidate(
        destination,
        {...candidate, type: {name: 'HVAC'}, function: {name: 'HVAC_THERMOSTAT'}, config: {masterThermostatChannelId: 30}},
        devices,
        'masterThermostatChannelId'
      )
    ).toBe(false);
  });
  it('distinguishes remote devices with identical channel captions', () => {
    const caption = ChannelsDropdown.methods.channelCaption.call(
      {devices, referenceRole: 'binarySensorChannelId', destination, $t: (s) => s},
      {...candidate, caption: 'Sensor'}
    );
    expect(caption).toBe('Sensor / Remote sensor');
  });
  it('warns on a stale selection without selecting another candidate', () => {
    const vm = {referenceRole: 'binarySensorChannelId', value: {id: 666}, channelsForDropdown: [candidate]};
    expect(ChannelsDropdown.computed.staleSelection.call(vm)).toBe(true);
    expect(vm.value.id).toBe(666);
  });
  it('silently synchronizes TomSelect so removed/stale values cannot emit an unset', () => {
    const dropdown = {
      clear: vi.fn(),
      clearOptions: vi.fn(),
      clearOptionGroups: vi.fn(),
      addOptionGroup: vi.fn(),
      settings: {},
      inputState: vi.fn(),
      enable: vi.fn(),
      disable: vi.fn(),
      addOption: vi.fn(),
      setValue: vi.fn(),
    };
    SelectForSubjects.methods.syncDropdown.call({dropdown, optionGroups: [], options: [], value: {id: 666}, $t: (s) => s});
    expect(dropdown.setValue).toHaveBeenCalledWith(undefined, true);
  });
  it('keeps a 404 selected ID and ignores a late GET from a previous selection', async () => {
    const get = vi.spyOn(api, 'get').mockRejectedValueOnce({status: 404});
    const vm = {value: 666, emitChannel: vi.fn()};
    ChannelsIdDropdown.methods.updateChannel.call(vm);
    await Promise.resolve();
    await Promise.resolve();
    expect(vm.channel).toEqual({id: 666});
    expect(vm.emitChannel).not.toHaveBeenCalled();
    let finish;
    get.mockImplementationOnce(
      () =>
        new Promise((resolve) => {
          finish = resolve;
        })
    );
    ChannelsIdDropdown.methods.updateChannel.call(vm);
    vm.value = 777;
    vm.channel = {id: 777};
    finish({body: {id: 666}});
    await Promise.resolve();
    expect(vm.channel.id).toBe(777);
    expect(vm.emitChannel).not.toHaveBeenCalled();
    get.mockRestore();
  });

  it.each([
    [TankParams, 'levelSensors', [{channelId: 666, fillLevel: 75}, null, {channelId: 20, fillLevel: 95}]],
    [ValveParams, 'floodSensorChannelIds', [666, null, 20]],
  ])('renders stale and unset array slots and removes only the explicit slot', (component, field, slots) => {
    const channel = {...destination, config: {[field]: slots, closeValveOnFloodType: 'ON_CHANGE'}};
    const wrapper = mount(component, {
      props: {channel},
      global: {
        plugins: [createTestingPinia({initialState: {channels: {all: {20: candidate}}, devices: {all: devices}}})],
        stubs: {ChannelsDropdown: true, NumberInput: true, SimpleDropdown: true, Toggler: true, TransitionExpand: true, fa: true},
      },
    });
    expect(wrapper.find('[role="alert"]').text()).toContain('666');
    expect(channel.config[field]).toHaveLength(3);
    const removeButtons = wrapper.findAll('a.text-default');
    expect(removeButtons).toHaveLength(3);
    removeButtons[1].trigger('click');
    expect(channel.config[field]).toEqual([slots[0], slots[2]]);
    wrapper.unmount();
  });
});

describe('M4B follow-up compatibility', () => {
  it.each(['mainThermometerChannelId', 'auxThermometerChannelId'])('rejects Type 3000 locally and remotely for %s', (role) => {
    const old = {...candidate, type: {name: 'THERMOMETERDS18B20'}, function: {name: 'THERMOMETER'}};
    expect(isChannelReferenceCandidate(destination, old, devices, role)).toBe(false);
    expect(localFirstCandidates(destination, [old], devices, role)).toEqual([]);
    expect(isChannelReferenceCandidate(destination, {...old, iodeviceId: 1}, {}, role)).toBe(false);
    for (const type of ['THERMOMETER', 'HUMIDITYANDTEMPSENSOR']) {
      expect(isChannelReferenceCandidate(destination, {...old, type: {name: type}}, devices, role)).toBe(true);
    }
  });
  it.each(['binarySensorChannelId', 'levelSensorChannelIds', 'floodSensorChannelIds'])('accepts all established active binary functions for %s', (role) => {
    const active = [
      'OPENINGSENSOR_GATEWAY',
      'OPENINGSENSOR_GATE',
      'OPENINGSENSOR_GARAGEDOOR',
      'OPENINGSENSOR_DOOR',
      'NOLIQUIDSENSOR',
      'OPENINGSENSOR_ROLLERSHUTTER',
      'OPENINGSENSOR_ROOFWINDOW',
      'OPENINGSENSOR_WINDOW',
      'HOTELCARDSENSOR',
      'ALARM_ARMAMENT_SENSOR',
      'MAILSENSOR',
      'CONTAINER_LEVEL_SENSOR',
      'FLOOD_SENSOR',
      'MOTION_SENSOR',
      'BINARY_SENSOR',
    ];
    for (const fn of active) {
      expect(isChannelReferenceCandidate(destination, {...candidate, function: {name: fn}}, devices, role)).toBe(true);
    }
    for (const fn of ['NONE', 'THERMOMETER', 'LIGHTSWITCH']) {
      expect(isChannelReferenceCandidate(destination, {...candidate, function: {name: fn}}, devices, role)).toBe(false);
    }
  });
});
