<template>
  <div class="container server-permissions">
    <div class="clearfix left-right-header">
      <h4>{{ $t('Server permissions') }}</h4>
      <button class="btn btn-white" type="button" :disabled="loading" @click="reload">{{ $t('Refresh') }}</button>
    </div>
    <p class="small text-muted">
      {{
        $t(
          'These are permissions authorized by the server, not confirmation of delivery availability. Peer access changes reach an offline source after reconnecting. Server READ does not require a peer association.'
        )
      }}
    </p>
    <p v-if="networkError" class="text-warning" role="alert">{{ $t('Could not load permissions. Check your connection and refresh to try again.') }}</p>
    <form class="permission-filters" @submit.prevent="reload">
      <label>{{ $t('Source device ID') }}<input v-model="filters.sourceDeviceId" class="form-control" type="number" min="1" /></label>
      <label>{{ $t('Requesting device ID') }}<input v-model="filters.destinationDeviceId" class="form-control" type="number" min="1" /></label>
      <label
        >{{ $t('Permissions')
        }}<select v-model="filters.permission" class="form-control">
          <option value="">{{ $t('All') }}</option>
          <option value="1">READ</option>
          <option value="2">CONTROL</option>
          <option value="4">ACTION</option>
        </select></label
      >
      <label
        >{{ $t('Origin')
        }}<select v-model="filters.origin" class="form-control">
          <option value="">{{ $t('All') }}</option>
          <option value="configuration">{{ $t('Configuration') }}</option>
          <option value="user-approved">{{ $t('User-approved') }}</option>
          <option value="other">{{ $t('Other') }}</option>
        </select></label
      >
      <button class="btn btn-white" type="submit" :disabled="loading">{{ $t('Filter') }}</button>
    </form>
    <LoadingCover :loading="loading">
      <table v-if="entries.length" class="table table-striped table-valign-middle permissions-table">
        <thead>
          <tr>
            <th>{{ $t('Requesting device') }}</th>
            <th>{{ $t('Permissions') }}</th>
            <th>{{ $t('Origin') }}</th>
            <th>{{ $t('Source device and channel') }}</th>
            <th>{{ $t('Details') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="entry in entries" :key="key(entry)">
            <td>
              <span class="field-label">{{ $t('Requesting device') }}</span
              ><router-link :to="{name: 'device.details', params: {id: entry.destinationDeviceId}}"
                >{{ entry.destinationName || $t('Device') }} (ID{{ entry.destinationDeviceId }})</router-link
              >
            </td>
            <td>
              <span class="field-label">{{ $t('Permissions') }}</span
              >{{ labels(entry.permissions) }}
            </td>
            <td>
              <span class="field-label">{{ $t('Origin') }}</span
              >{{ $t(originLabels[entry.originSummary]) }}
            </td>
            <td>
              <span class="field-label">{{ $t('Source device and channel') }}</span>
              <div v-if="entry.resourceType === 1">
                <router-link :to="{name: 'channel', params: {id: entry.resourceId}}"
                  >{{ entry.sourceCaption || $t('Channel') }} (ID{{ entry.resourceId }})</router-link
                >
                <div class="small text-muted">{{ entry.sourceFunction ? $t(entry.sourceFunction) : '' }}</div>
              </div>
              <div v-else>{{ $t('Whole device') }} (ID{{ entry.resourceId }})</div>
              <span v-if="entry.sourceIsVirtual">{{ $t('Virtual / integration source') }}</span>
              <router-link v-else :to="{name: 'device.details', params: {id: entry.sourceDeviceId}}"
                >{{ entry.sourceDeviceName || $t('Device') }} (ID{{ entry.sourceDeviceId }})</router-link
              >
            </td>
            <td>
              <span class="field-label">{{ $t('Details') }}</span
              ><button class="btn btn-white btn-xs" type="button" :disabled="loading" @click="showDetails(entry)">{{ $t('Details') }}</button>
            </td>
          </tr>
        </tbody>
      </table>
      <EmptyListPlaceholder v-else-if="!loading && !networkError" class="inline" />
    </LoadingCover>
    <div class="d-flex justify-content-between mb-4">
      <button class="btn btn-white" type="button" :disabled="loading || !history.length" @click="previous">{{ $t('Previous') }}</button>
      <button class="btn btn-white" type="button" :disabled="loading || !nextCursor" @click="next">{{ $t('Next') }}</button>
    </div>
    <section v-if="detail" class="permission-details" aria-live="polite">
      <div class="clearfix left-right-header">
        <h4>{{ $t('Permission origins') }}</h4>
        <button class="btn btn-white btn-xs" @click="closeDetails">{{ $t('Close') }}</button>
      </div>
      <p v-if="detail.authorizationKind === 'SERVER_READ'">{{ $t('Server READ authorization; no peer association or source provisioning is required.') }}</p>
      <p>{{ labels(detail.permissions) }} — {{ detail.sourceCaption || detail.sourceDeviceName }} → {{ detail.destinationName }}</p>
      <p class="small text-muted">
        {{
          $t(
            'Removing one origin can leave access authorized by another origin. Configuration permissions are changed in the channel configuration; user approvals are changed in Requests.'
          )
        }}
      </p>
      <ul class="list-group">
        <li v-for="origin in detail.origins" :key="`${origin.originType}:${origin.originId}`" class="list-group-item">
          <strong>{{ $t(originLabels[origin.kind]) }}</strong> — {{ labels(origin.permissions) }}
          <div class="small text-muted">{{ $t('Origin') }} {{ origin.originType }} / {{ origin.originId }}</div>
          <div v-if="origin.field">
            {{ $t(origin.field.label) }}<span v-if="origin.field.slot !== null"> — {{ $t('Slot') }} {{ origin.field.slot + 1 }}</span>
          </div>
          <router-link v-if="origin.configuringChannel" :to="{name: 'channel', params: {id: origin.configuringChannel.id}}"
            >{{ $t('Edit channel configuration') }}: {{ origin.configuringChannel.caption || $t('Channel') }} (ID{{
              origin.configuringChannel.id
            }})</router-link
          >
          <router-link v-if="origin.decisionId" :to="requestLink(origin.decisionId)">{{ $t('View accepted request') }} (ID{{ origin.decisionId }})</router-link>
        </li>
      </ul>
      <button v-if="detail.nextOriginCursor" class="btn btn-white" :disabled="detailLoading" @click="moreOrigins">{{ $t('More origins') }}</button>
    </section>
  </div>
</template>

<script setup>
  import {onMounted, onBeforeUnmount, ref, watch} from 'vue';
  import {api} from '@/api/api';
  import {permissionLabels} from '@/devices/details/resource-access-labels';
  import {i18n} from '@/locale';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  defineOptions({compatConfig: {MODE: 3}});
  const props = defineProps({device: Object});
  const emit = defineEmits(['updated']);
  const entries = ref([]),
    loading = ref(false),
    detailLoading = ref(false),
    detail = ref();
  const networkError = ref(false);
  const filters = ref({sourceDeviceId: '', destinationDeviceId: '', permission: '', origin: ''});
  const appliedFilters = ref({...filters.value});
  const cursor = ref(),
    nextCursor = ref(),
    history = ref([]);
  let generation = 0,
    detailGeneration = 0;
  const originLabels = {configuration: 'Configuration', 'user-approved': 'User-approved', mixed: 'Mixed', other: 'Other'}; // i18n
  const labels = (mask) =>
    permissionLabels(mask)
      .map((label) => i18n.global.t(label))
      .join(', ');
  const key = (entry) => `${entry.resourceKey || entry.associationId}/${entry.resourceType}/${entry.resourceId}`;
  const requestLink = (id) => ({
    name: props.device ? 'device.resource-access' : 'devices.resource-access',
    params: props.device ? {id: props.device.id} : {},
    query: {view: 'requests', decisionId: id},
  });
  function closeDetails() {
    detailGeneration++;
    detail.value = undefined;
    detailLoading.value = false;
  }
  async function load() {
    const current = ++generation;
    loading.value = true;
    networkError.value = false;
    closeDetails();
    const query = new URLSearchParams({limit: '25'});
    for (const [key, value] of Object.entries(appliedFilters.value)) if (value !== '') query.set(key, value);
    if (props.device) query.set('deviceId', props.device.id);
    if (cursor.value) query.set('cursor', cursor.value);
    try {
      const response = await api.get(`resource-access/permissions?${query}`);
      if (current === generation) {
        entries.value = response.body.items;
        nextCursor.value = response.body.nextCursor;
      }
    } catch (error) {
      if (current === generation && !error?.status) networkError.value = true;
    } finally {
      if (current === generation) loading.value = false;
    }
  }
  function reload() {
    appliedFilters.value = {...filters.value};
    cursor.value = undefined;
    history.value = [];
    entries.value = [];
    nextCursor.value = undefined;
    return load().then(() => emit('updated'));
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
  async function showDetails(entry) {
    const current = ++detailGeneration;
    detail.value = undefined;
    detailLoading.value = true;
    networkError.value = false;
    try {
      const response = await api.get(`resource-access/permissions/${key(entry)}`);
      if (current === detailGeneration) detail.value = response.body;
    } catch (error) {
      if (current === detailGeneration && !error?.status) networkError.value = true;
    } finally {
      if (current === detailGeneration) detailLoading.value = false;
    }
  }
  async function moreOrigins() {
    const current = ++detailGeneration,
      entry = detail.value;
    detailLoading.value = true;
    networkError.value = false;
    try {
      const response = await api.get(`resource-access/permissions/${key(entry)}?cursor=${entry.nextOriginCursor}`);
      if (current === detailGeneration) detail.value = {...response.body, origins: [...entry.origins, ...response.body.origins]};
    } catch (error) {
      if (current === detailGeneration && !error?.status) networkError.value = true;
    } finally {
      if (current === detailGeneration) detailLoading.value = false;
    }
  }
  onMounted(load);
  watch(() => props.device?.id, reload, {flush: 'sync'});
  onBeforeUnmount(() => {
    generation++;
    detailGeneration++;
  });
</script>

<style scoped lang="scss">
  .permission-filters {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
    margin-bottom: 20px;
    label {
      flex: 1 1 150px;
      min-width: 0;
    }
  }
  .permissions-table {
    width: 100%;
    table-layout: fixed;
    td {
      overflow-wrap: anywhere;
    }
    th:nth-child(4) {
      width: 30%;
    }
  }
  .permission-details {
    overflow-wrap: anywhere;
  }
  .field-label {
    display: none;
  }
  @media (max-width: 991px) {
    .permissions-table {
      display: block;
      thead {
        display: none;
      }
      tbody,
      tr,
      td {
        display: block;
        width: 100%;
      }
    }
    .field-label {
      display: block;
      font-weight: bold;
    }
  }
</style>
