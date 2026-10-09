import {isChannelReferenceCandidate} from '@/channels/channel-reference-candidates';

export function isMainThermometerCandidate(destination, candidate, devices) {
  return isChannelReferenceCandidate(destination, candidate, devices, 'mainThermometerChannelId');
}
