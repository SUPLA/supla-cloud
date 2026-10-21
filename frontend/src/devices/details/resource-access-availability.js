import {computed, onBeforeUnmount, ref, watch} from 'vue';
import {api} from '@/api/api';

export const resourceAccessAvailabilityKey = Symbol('resource-access-availability');

export function useResourceAccessAvailability(deviceId = () => undefined) {
  const permissions = ref(null);
  const requests = ref(null);
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
    await Promise.all(
      [
        ['resource-access/permissions', permissions, (body) => body.items.length > 0, permissionsFailed],
        ['resource-access', requests, (body) => body.length > 0, requestsFailed],
      ].map(async ([endpoint, target, present, error]) => {
        try {
          const response = await api.get(`${endpoint}?${query}`, {skipErrorHandler: true});
          if (current === generation) {
            target.value = present(response.body);
            error.value = false;
          }
        } catch {
          // A failed probe does not prove the list is empty. Keep the last known state.
          if (current === generation) error.value = true;
        }
      })
    );
  }

  watch(
    deviceId,
    () => {
      permissions.value = requests.value = null;
      permissionsFailed.value = requestsFailed.value = false;
      reload();
    },
    {immediate: true, flush: 'sync'}
  );
  onBeforeUnmount(() => generation++);
  return {permissions, requests, permissionsFailed, requestsFailed, failed, hasEntries, empty, reload};
}
