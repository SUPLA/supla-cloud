// The channels/devices stores contain only the authenticated account's resources.
export function isMainThermometerCandidate(destination, candidate, devices) {
  if (candidate.id === destination.id || !['THERMOMETER', 'HUMIDITYANDTEMPERATURE'].includes(candidate.function.name)) {
    return false;
  }
  if (!['THERMOMETER', 'THERMOMETERDS18B20', 'HUMIDITYANDTEMPSENSOR'].includes(candidate.type.name)) {
    return false;
  }
  return (
    candidate.iodeviceId === destination.iodeviceId ||
    !!(devices[destination.iodeviceId]?.flags?.suplanSupported && devices[candidate.iodeviceId]?.flags?.suplanSupported)
  );
}
