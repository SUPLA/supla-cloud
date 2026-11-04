// Stores contain only resources authorized for the authenticated account.
const temperatureFunctions = ['THERMOMETER', 'HUMIDITYANDTEMPERATURE'];
const temperatureTypes = ['THERMOMETER', 'HUMIDITYANDTEMPSENSOR'];
const hvacFunctions = ['HVAC_THERMOSTAT', 'HVAC_THERMOSTAT_HEAT_COOL', 'HVAC_THERMOSTAT_DIFFERENTIAL', 'HVAC_DOMESTIC_HOT_WATER'];
const binaryFunctions = [
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

export function isChannelReferenceCandidate(destination, candidate, devices, role) {
  if (!candidate || candidate.id === destination.id || (!devices[candidate.iodeviceId] && candidate.iodeviceId !== destination.iodeviceId)) {
    return false;
  }
  const local = candidate.iodeviceId === destination.iodeviceId;
  if (!local && !(devices[destination.iodeviceId]?.flags?.suplanSupported && devices[candidate.iodeviceId]?.flags?.suplanSupported)) {
    return false;
  }
  const type = candidate.type?.name;
  const fn = candidate.function?.name;
  switch (role) {
    case 'mainThermometerChannelId':
    case 'auxThermometerChannelId':
      return temperatureTypes.includes(type) && temperatureFunctions.includes(fn);
    case 'binarySensorChannelId':
    case 'levelSensorChannelIds':
    case 'floodSensorChannelIds':
      return type === 'SENSORNO' && binaryFunctions.includes(fn);
    case 'masterThermostatChannelId':
      return type === 'HVAC' && hvacFunctions.includes(fn) && !candidate.config?.masterThermostatChannelId;
    case 'pumpSwitchChannelId':
    case 'heatOrColdSourceSwitchChannelId':
      return type === 'RELAY' && fn === (role === 'pumpSwitchChannelId' ? 'PUMPSWITCH' : 'HEATORCOLDSOURCESWITCH');
    default:
      return false;
  }
}

export function localFirstCandidates(destination, candidates, devices, role) {
  return candidates
    .filter((candidate) => isChannelReferenceCandidate(destination, candidate, devices, role))
    .map((candidate) => ({...candidate, group: candidate.iodeviceId === destination.iodeviceId ? 'local' : 'remote'}))
    .sort((a, b) => (a.group === 'remote') - (b.group === 'remote') || a.id - b.id);
}
