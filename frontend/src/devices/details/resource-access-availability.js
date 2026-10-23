import {computed, onBeforeUnmount, ref, watch} from 'vue';
import {api} from '@/api/api';

export const resourceAccessAvailabilityKey = Symbol('resource-access-availability');

export function useResourceAccessAvailability(deviceId = () => undefined) {
  const permissions = ref(null);
  const requests = ref(null);
  const pendingCount = ref(0);
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
    const pendingQuery = new URLSearchParams({status: 'PENDING', ...Object.fromEntries(query), limit: '50'});
    const [hasPermissions, hasRequests, count] = await Promise.all([
      probe(`resource-access/permissions?${query}`, (body) => body.items.length > 0),
      probe(`resource-access?${query}`, (body) => body.length > 0),
      countPending(pendingQuery, current),
    ]);
    if (current !== generation) return;
    // Publish one refresh together; mixing fresh and old presence can hide a populated tab.
    permissionsFailed.value = hasPermissions === null;
    requestsFailed.value = hasRequests === null;
    if (hasPermissions !== null) permissions.value = hasPermissions;
    if (hasRequests !== null) requests.value = hasRequests;
    if (count !== null) pendingCount.value = count;
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

  async function countPending(query, current) {
    let count = 0;
    try {
      while (current === generation) {
        const response = await api.get(`resource-access?${query}`, {skipErrorHandler: true});
        if (current !== generation) return null;
        count += response.body.length;
        const cursor = response.headers?.get('X-Next-Cursor');
        if (!cursor) return count;
        query.set('beforeId', cursor);
      }
    } catch {
      // Keep the last complete count if any page fails.
    }
    return null;
  }

  watch(
    deviceId,
    () => {
      permissions.value = requests.value = null;
      permissionsFailed.value = requestsFailed.value = false;
      pendingCount.value = 0;
      reload();
    },
    {immediate: true, flush: 'sync'}
  );
  onBeforeUnmount(() => generation++);
  return {permissions, requests, pendingCount, permissionsFailed, requestsFailed, failed, hasEntries, empty, reload};
}
