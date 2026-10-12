<template>
  <div class="container resource-access">
    <div class="clearfix left-right-header">
      <h4 class="no-margin-top">{{ $t('Requests') }}</h4>
      <div>
        <button type="button" class="btn btn-white" :disabled="loading" @click="load">
          <i class="pe-7s-refresh"></i>
          {{ $t('Refresh') }}
        </button>
      </div>
    </div>
    <p>{{ $t('Devices request access to specific channels. Your decision does not configure the requesting device.') }}</p>
    <p class="small text-muted">
      {{
        $t(
          'A decision is not confirmation that access has reached the source. An offline source applies changes after reconnecting; removal is not immediate there.'
        )
      }}
    </p>
    <p v-if="conflict" class="text-warning" role="alert">{{ $t('The request changed. Refresh and confirm the new permissions.') }}</p>
    <p v-if="decisionId">{{ $t('Selected request') }}: ID{{ decisionId }}</p>
    <section v-for="group in groups" :key="group.key" class="mb-4" :aria-label="group.label ? $t(group.label) : undefined">
      <h4 v-if="group.label">
        {{ $t(group.label) }} <small>({{ group.entries.length }})</small>
      </h4>
      <ResourceAccessList :entries="group.entries" :loading="loading" :compact-empty="!!device" @select="select" />
    </section>
    <ModalConfirm v-if="selection" :header="$t('Confirm access decision')" :loading="loading" @cancel="selection = undefined" @confirm="confirm">
      <p>{{ $t(confirmationText(selection.entry.status, selection.action)) }}</p>
      <p>{{ selection.entry.destinationName }} → {{ selection.entry.sourceDeviceName }} / ID{{ selection.entry.sourceChannelId }}</p>
      <strong>{{
        permissionLabels(selection.entry.permissions)
          .map((label) => $t(label))
          .join(', ')
      }}</strong>
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
  const entries = ref([]);
  const loading = ref(false);
  const conflict = ref(false);
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
    try {
      const query = deviceId !== undefined ? `?deviceId=${deviceId}` : '';
      const response = await api.get(`resource-access${query}`);
      if (revision === loadRevision && deviceId === props.device?.id) {
        entries.value = response.body;
      }
    } finally {
      if (revision === loadRevision) loading.value = false;
    }
  }
  function select(entry, action) {
    // Confirmation is an immutable displayed snapshot, even if a later refresh changes the list.
    selection.value = {entry: {...entry}, action};
    conflict.value = false;
  }
  async function confirm() {
    if (!selection.value || loading.value) return;
    const context = contextRevision;
    const {entry, action} = selection.value;
    loading.value = true;
    try {
      await api.post(
        `resource-access/${entry.id}/${action}`,
        {revision: entry.revision, status: entry.status, permissions: entry.permissions},
        {skipErrorHandler: [409]}
      );
      if (context !== contextRevision) return;
      selection.value = undefined;
      await load();
    } catch (error) {
      if (context !== contextRevision) return;
      if (error.status === 409) {
        conflict.value = true;
        selection.value = undefined;
        await load();
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
      load();
    },
    {flush: 'sync'}
  );
  onBeforeUnmount(() => {
    loadRevision++;
    contextRevision++;
  });
</script>
