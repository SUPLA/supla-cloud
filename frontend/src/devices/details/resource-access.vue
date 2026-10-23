<template>
  <div class="container resource-access">
    <div class="clearfix left-right-header">
      <h4 class="no-margin-top">{{ $t('Requests') }}</h4>
      <div>
        <button type="button" class="btn btn-white" :disabled="loading" @click="reload">
          <i class="pe-7s-refresh-2" aria-hidden="true"></i>
          {{ $t('Refresh') }}
        </button>
      </div>
    </div>
    <p>{{ $t('Devices request access to specific channels. Your decision does not configure the requesting device.') }}</p>
    <p class="small text-muted">
      {{
        $t(
          'A decision records permission, not delivery availability. Peer access changes reach an offline source after reconnecting. Server-delivered READ uses committed server authorization.'
        )
      }}
    </p>
    <p v-if="conflict" class="text-warning" role="alert">{{ $t('The request changed. Refresh and confirm the new permissions.') }}</p>
    <p v-if="networkError && !selection" class="text-warning" role="alert">
      {{ $t('Could not load requests. Check your connection and refresh to try again.') }}
    </p>
    <p v-if="!decisionId" class="small text-muted">{{ $t('Sorting applies to this page.') }}</p>
    <p v-if="decisionId">{{ $t('Selected request') }}: ID{{ decisionId }}</p>
    <section v-for="group in groups" :key="group.key" class="mb-4" :aria-label="group.label ? $t(group.label) : undefined">
      <h4 v-if="group.label">
        {{ $t(group.label) }} <small>({{ group.entries.length }})</small>
      </h4>
      <ResourceAccessList :entries="group.entries" :loading="loading" :failed="networkError" :compact-empty="!!device" @select="select" />
    </section>
    <div v-if="!decisionId" class="d-flex justify-content-between mb-4">
      <button type="button" class="btn btn-white" :disabled="loading || !history.length" @click="previous">{{ $t('Previous') }}</button>
      <button type="button" class="btn btn-white" :disabled="loading || !nextCursor" @click="next">{{ $t('Next') }}</button>
    </div>
    <ModalConfirm v-if="selection" :header="$t('Confirm access decision')" :loading="loading" @cancel="selection = undefined" @confirm="confirm">
      <p>{{ $t(confirmationText(selection.entry.status, selection.action, selection.entry.requestedDeliveryMode)) }}</p>
      <p>
        {{ selection.entry.destinationName }} → {{ selection.entry.sourceIsVirtual ? $t('Virtual / integration source') : selection.entry.sourceDeviceName }} /
        ID{{ selection.entry.sourceChannelId }}
      </p>
      <strong>{{
        permissionLabels(selection.entry.permissions)
          .map((label) => $t(label))
          .join(', ')
      }}</strong>
      <p v-if="selection.action === 'approve'" class="small text-muted">
        {{
          $t(
            'Manual approval is independent of configuration permissions. It remains until you remove it, even if the configuration granting the same access is removed.'
          )
        }}
      </p>
      <div v-if="networkError" class="text-warning" role="alert">
        <p>{{ $t('Could not confirm the result. The decision may have reached the server. Refresh to check its status before trying again.') }}</p>
        <button type="button" class="btn btn-white" :disabled="loading" @click="reload">
          <i class="pe-7s-refresh-2" aria-hidden="true"></i>
          {{ $t('Refresh request') }}
        </button>
      </div>
    </ModalConfirm>
  </div>
</template>

<script setup>
  import {computed, onBeforeUnmount, onMounted, ref, watch} from 'vue';
  import {api} from '@/api/api';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import ResourceAccessList from '@/devices/details/resource-access-list.vue';
  import {permissionLabels, confirmationText} from '@/devices/details/resource-access-labels';

  defineOptions({compatConfig: {MODE: 3}});
  const props = defineProps({device: Object, decisionId: String});
  const emit = defineEmits(['updated', 'close-selected-request']);
  const entries = ref([]);
  const loading = ref(false);
  const conflict = ref(false);
  const networkError = ref(false);
  const cursor = ref(),
    nextCursor = ref(),
    history = ref([]);
  const selection = ref();
  let loadRevision = 0;
  let contextRevision = 0;

  const visibleEntries = computed(() => (props.decisionId ? entries.value.filter((entry) => String(entry.id) === props.decisionId) : entries.value));
  const groups = computed(() =>
    props.device
      ? [
          {
            key: 'requested',
            label: 'Requests made by this device',
            entries: visibleEntries.value.filter((entry) => entry.destinationDeviceId === props.device.id),
          }, // i18n
          {
            key: 'provided',
            label: "Requests for this device's channels",
            entries: visibleEntries.value.filter((entry) => entry.sourceDeviceId === props.device.id),
          }, // i18n
        ]
      : [{key: 'all', label: null, entries: visibleEntries.value}]
  );

  async function load() {
    const revision = ++loadRevision;
    const deviceId = props.device?.id;
    loading.value = true;
    networkError.value = false;
    try {
      let response;
      if (props.decisionId) {
        response = await api.get(`resource-access/${props.decisionId}`, {skipErrorHandler: [404]});
      } else {
        const query = new URLSearchParams();
        if (deviceId !== undefined) query.set('deviceId', deviceId);
        if (cursor.value) query.set('beforeId', cursor.value);
        response = await api.get(`resource-access${query.size ? `?${query}` : ''}`);
      }
      if (revision === loadRevision && deviceId === props.device?.id) {
        entries.value = props.decisionId ? [response.body] : response.body;
        nextCursor.value = response.headers?.get('X-Next-Cursor');
        return true;
      }
    } catch (error) {
      if (revision === loadRevision && props.decisionId && error?.status === 404) {
        closeSelectedRequest();
      }
      if (revision === loadRevision && !error?.status) networkError.value = true;
    } finally {
      if (revision === loadRevision) loading.value = false;
    }
    return false;
  }
  function reload() {
    selection.value = undefined;
    cursor.value = undefined;
    nextCursor.value = undefined;
    history.value = [];
    return load().then((loaded) => {
      if (loaded) emit('updated');
    });
  }
  function next() {
    history.value.push(cursor.value);
    cursor.value = nextCursor.value;
    return load();
  }
  function previous() {
    cursor.value = history.value.pop();
    return load();
  }
  function select(entry, action) {
    // Confirmation is an immutable displayed snapshot, even if a later refresh changes the list.
    selection.value = {entry: {...entry}, action};
    conflict.value = false;
    networkError.value = false;
  }
  function closeSelectedRequest() {
    entries.value = [];
    selection.value = undefined;
    conflict.value = false;
    emit('close-selected-request');
  }
  async function confirm() {
    if (!selection.value || loading.value) return;
    const context = contextRevision;
    const {entry, action} = selection.value;
    loading.value = true;
    networkError.value = false;
    try {
      await api.post(
        `resource-access/${entry.id}/${action}`,
        {revision: entry.revision, status: entry.status, permissions: entry.permissions},
        {skipErrorHandler: [404, 409]}
      );
      if (context !== contextRevision) return;
      selection.value = undefined;
      if (action === 'remove' && props.decisionId) {
        closeSelectedRequest();
      } else if (await load()) {
        emit('updated');
      }
    } catch (error) {
      if (context !== contextRevision) return;
      if (error.status === 404 || error.status === 409) {
        conflict.value = true;
        selection.value = undefined;
        if (error.status === 404 && props.decisionId) {
          closeSelectedRequest();
        } else if (await load()) {
          emit('updated');
        }
      } else if (!error?.status) {
        networkError.value = true;
      }
    } finally {
      if (context === contextRevision) loading.value = false;
    }
  }
  onMounted(load);
  watch(
    () => [props.device?.id, props.decisionId],
    () => {
      contextRevision++;
      entries.value = [];
      selection.value = undefined;
      conflict.value = false;
      reload();
    },
    {flush: 'sync'}
  );
  onBeforeUnmount(() => {
    loadRevision++;
    contextRevision++;
  });
</script>
