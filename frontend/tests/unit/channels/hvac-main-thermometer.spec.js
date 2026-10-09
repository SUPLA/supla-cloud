import {isMainThermometerCandidate} from '@/channels/hvac/main-thermometer-candidates';
import {mount} from '@vue/test-utils';
import {createTestingPinia} from '@pinia/testing';
import HvacParams from '@/channels/params/channel-params-hvac-thermostat.vue';

const destination = {id: 10, iodeviceId: 1, locationId: 1};
const candidate = {id: 20, iodeviceId: 2, locationId: 2, function: {name: 'THERMOMETER'}, type: {name: 'THERMOMETER'}, connected: false};
const devices = {1: {flags: {suplanSupported: true}}, 2: {flags: {suplanSupported: true}, connected: false}};

describe('M3 HVAC MainThermometer candidates', () => {
  it('offers an offline SupLAN thermometer on another device and Location', () => {
    expect(isMainThermometerCandidate(destination, candidate, devices)).toBe(true);
  });
  it('offers local cross-Location thermometers on a legacy device', () => {
    expect(isMainThermometerCandidate(destination, {...candidate, iodeviceId: 1}, {})).toBe(true);
  });
  it.each([{1: {flags: {suplanSupported: false}}, 2: devices[2]}, {1: devices[1], 2: {flags: {suplanSupported: false}}}, {}])(
    'rejects remote candidates unless both devices support SupLAN',
    (flags) => {
      expect(isMainThermometerCandidate(destination, candidate, flags)).toBe(false);
    }
  );
  it('rejects incompatible functions, types and self references', () => {
    expect(isMainThermometerCandidate(destination, {...candidate, function: {name: 'HUMIDITY'}}, devices)).toBe(false);
    expect(isMainThermometerCandidate(destination, {...candidate, type: {name: 'RELAY'}}, devices)).toBe(false);
    expect(isMainThermometerCandidate(destination, {...candidate, id: destination.id}, devices)).toBe(false);
  });
  it('uses account thermometer candidates for SupLAN and same-device candidates for legacy', () => {
    expect(HvacParams.computed.mainThermometerParams.call({channel: destination, devices})).toBe('function=THERMOMETER,HUMIDITYANDTEMPERATURE');
    expect(HvacParams.computed.mainThermometerParams.call({channel: destination, devices: {}})).toBe('function=THERMOMETER,HUMIDITYANDTEMPERATURE&deviceIds=1');
  });
});

describe('M3 HVAC selectors', () => {
  it('offers all six selectors in the shared role-aware scope', () => {
    const channel = {
      ...destination,
      function: {name: 'HVAC_THERMOSTAT'},
      config: {
        subfunction: 'HEAT',
        temperatures: {freezeProtection: 5, heatProtection: 35},
        temperatureConstraints: {},
        heatingModeAvailable: true,
        coolingModeAvailable: true,
        availableAlgorithms: [],
        localUILockingCapabilities: [],
        hiddenConfigFields: ['localUILock'],
        masterThermostatAvailable: true,
        pumpSwitchAvailable: true,
        heatOrColdSourceSwitchAvailable: true,
      },
    };
    const wrapper = mount(HvacParams, {
      props: {channel},
      global: {
        renderStubDefaultSlot: false,
        plugins: [createTestingPinia({initialState: {devices: {all: devices}}})],
        stubs: {
          ChannelsIdDropdown: {name: 'ChannelsIdDropdown', props: ['params', 'filter', 'destination', 'referenceRole'], template: '<div />'},
          AccordionRoot: {template: '<div><slot /></div>'},
          AccordionItem: {template: '<div><slot /></div>'},
          TransitionExpand: {template: '<div><slot /></div>'},
          SameDifferentThanMasterThermostat: true,
          SimpleDropdown: true,
          Toggler: true,
        },
      },
    });
    const selectors = wrapper.findAllComponents({name: 'ChannelsIdDropdown'});
    expect(selectors).toHaveLength(6);
    const params = selectors.map((selector) => selector.props('params'));
    expect(params.filter((param) => param.includes('deviceIds=1'))).toHaveLength(0);
    expect(selectors.every((selector) => selector.props('destination')?.id === channel.id && selector.props('referenceRole'))).toBe(true);
    const main = selectors.find((selector) => selector.props('referenceRole') === 'mainThermometerChannelId');
    expect(main.props('filter')(candidate)).toBe(true);
    channel.config.auxThermometerChannelId = candidate.id;
    expect(main.props('filter')(candidate)).toBe(false);
    wrapper.unmount();
  });
});

it('Main wrapper rejects local and remote Type 3000', () => {
  const old = {...candidate, type: {name: 'THERMOMETERDS18B20'}};
  expect(isMainThermometerCandidate(destination, old, devices)).toBe(false);
  expect(isMainThermometerCandidate(destination, {...old, iodeviceId: 1}, {})).toBe(false);
});
