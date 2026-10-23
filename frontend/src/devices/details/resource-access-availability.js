import {computed, onBeforeUnmount, ref, watch} from 'vue';
import {api} from '@/api/api';

export const resourceAccessAvailabilityKey = Symbol('resource-access-availability');

export function useResourceAccessAvailability(deviceId = () => undefined) {
  const permissions = ref(null);
  const requests = ref(null);
  const pending = ref(false);
  const permissionsFailed = ref(false);
  const requestsFailed = ref(false);
  const failed = computed(() => permissionsFailed.value || requestsFailed.value);
  const hasEntries = computed(() => permissions.value === true || requests.value === true || failed.value);
  const empty = computed(() => permissions.value === false && requests.value === false && !failed.value);
  let generation = 0;

  async function reload() {
    const current = ++generation;
    const query = new URLSearchParams({limit: '1'});
    if (deviceId() !== undefined) query.set('deviceId', deviceId());
    const pendingQuery = new URLSearchParams({status: 'PENDING', ...Object.fromEntries(query)});
    const [hasPermissions, hasRequests, hasPending] = await Promise.all([
      probe(`resource-access/permissions?${query}`, (body) => body.items.length > 0),
      probe(`resource-access?${query}`, (body) => body.length > 0),
      probe(`resource-access?${pendingQuery}`, (body) => body.length > 0),
    ]);
    if (current !== generation) return;
    // Publish one refresh together; mixing fresh and old presence can hide a populated tab.
    permissionsFailed.value = hasPermissions === null;
    requestsFailed.value = hasRequests === null;
    if (hasPermissions !== null) permissions.value = hasPermissions;
    if (hasRequests !== null) requests.value = hasRequests;
    if (hasPending !== null) pending.value = hasPending;
  }

  async function probe(endpoint, present) {
    try {
      const response = await api.get(endpoint, {skipErrorHandler: true});
      return present(response.body);
    } catch {
      // A failed probe does not prove the list is empty. Keep the last known state.
      return null;
    }
  }

  watch(
    deviceId,
    () => {
      permissions.value = requests.value = null;
      permissionsFailed.value = requestsFailed.value = false;
      pending.value = false;
      reload();
    },
    {immediate: true, flush: 'sync'}
  );
  onBeforeUnmount(() => generation++);
  return {permissions, requests, pending, permissionsFailed, requestsFailed, failed, hasEntries, empty, reload};
}
