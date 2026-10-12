<template>
  <div>
    <div class="resource-access-sort form-group">
      <label :for="sortId">{{ $t('Sort by') }}</label>
      <div class="d-flex">
        <select :id="sortId" class="form-control" v-model="sortKey">
          <option v-for="column in sortableColumns" :key="column.key" :value="column.key">{{ $t(column.label) }}</option>
        </select>
        <button type="button" class="btn btn-white ml-2" :aria-label="$t('Reverse sort order')" @click="reverseSort">
          <fa :icon="sortDirection === 'asc' ? faSortUp : faSortDown" />
        </button>
      </div>
    </div>
    <LoadingCover :loading="loading">
      <table v-if="entries.length" class="table table-striped table-valign-middle resource-access-table">
        <thead>
          <tr>
            <th v-for="column in sortableColumns.slice(0, 4)" :key="column.key" scope="col" :aria-sort="ariaSort(column.key)">
              <button type="button" class="resource-access-sort-button" :data-sort="column.key" @click="sortBy(column.key)">
                {{ $t(column.label) }}
                <fa :icon="sortIcon(column.key)" fixed-width />
              </button>
              <div v-if="column.key === 'status'" class="small text-muted">
                <button type="button" class="resource-access-sort-button" data-sort="lastDecision" @click="sortBy('lastDecision')">
                  {{ $t('Last decision') }} <fa :icon="sortIcon('lastDecision')" fixed-width />
                </button>
              </div>
            </th>
            <th scope="col" class="text-right">{{ $t('Actions') }}</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="entry in sortedEntries" :key="entry.id">
            <td>
              <span class="resource-access-field-label">{{ $t('Requesting device') }}</span>
              <router-link v-if="entry.destinationName != null" :to="{name: 'device.details', params: {id: entry.destinationDeviceId}}">
                {{ entry.destinationName || $t('Device') }}
              </router-link>
              <span v-else>{{ $t('Unavailable') }}</span>
              <div class="small text-muted">ID{{ entry.destinationDeviceId }}</div>
            </td>
            <td>
              <span class="resource-access-field-label">{{ $t('Permissions') }}</span>
              <div>
                {{
                  permissionLabels(entry.permissions)
                    .map((label) => $t(label))
                    .join(', ')
                }}
              </div>
              <div v-if="entry.acceptedManualPermissions && entry.status !== 'ACCEPTED'" class="small text-muted">
                {{ $t('Already accepted') }}:
                {{
                  permissionLabels(entry.acceptedManualPermissions)
                    .map((label) => $t(label))
                    .join(', ')
                }}
              </div>
              <div v-if="entry.independentPermissions" class="small text-muted">
                {{ $t('Access from configuration or policy') }}:
                {{
                  permissionLabels(entry.independentPermissions)
                    .map((label) => $t(label))
                    .join(', ')
                }}
              </div>
            </td>
            <td>
              <span class="resource-access-field-label">{{ $t('Status') }}</span>
              <span :class="statusStyles[entry.status].class">
                <fa :icon="statusStyles[entry.status].icon" fixed-width /> {{ $t(statusLabel(entry.status)) }}
              </span>
              <div class="small text-muted" :title="entry.requestedAt ? $t('Requested: {date}', {date: formatDateTime(entry.requestedAt)}) : undefined">
                <time v-if="entry.lastDecisionAt" :datetime="entry.lastDecisionAt">{{ formatDateTime(entry.lastDecisionAt) }}</time>
                <span v-else>—</span>
              </div>
            </td>
            <td>
              <span class="resource-access-field-label">{{ $t('Source device and channel') }}</span>
              <div>
                <router-link v-if="entry.sourceFunctionId != null" :to="{name: 'channel', params: {id: entry.sourceChannelId}}">
                  {{ entry.sourceCaption || $t('Channel') }} (ID{{ entry.sourceChannelId }})
                </router-link>
                <span v-else>{{ entry.sourceCaption || $t('Unavailable') }} (ID{{ entry.sourceChannelId }})</span>
              </div>
              <div class="small">
                <router-link v-if="entry.sourceDeviceName != null" :to="{name: 'device.details', params: {id: entry.sourceDeviceId}}">
                  {{ entry.sourceDeviceName || $t('Device') }} (ID{{ entry.sourceDeviceId }})
                </router-link>
                <span v-else class="text-muted">{{ $t('Unavailable') }} (ID{{ entry.sourceDeviceId }})</span>
              </div>
              <div class="small text-muted">
                {{ entry.sourceFunction ? $t(entry.sourceFunction) : `${$t('Function')} ${entry.sourceFunctionId ?? ''}` }}
              </div>
            </td>
            <td class="text-right">
              <span class="resource-access-field-label">{{ $t('Actions') }}</span>
              <div class="resource-access-actions">
                <button v-if="entry.canApprove" type="button" class="btn btn-green btn-xs" :disabled="loading" @click="emit('select', entry, 'approve')">
                  {{ $t(entry.status === 'REJECTED' ? 'Reconsider and approve all' : 'Approve all') }}
                </button>
                <button
                  v-if="entry.canManage && entry.status === 'PENDING'"
                  type="button"
                  class="btn btn-white btn-xs"
                  :disabled="loading"
                  @click="emit('select', entry, 'reject')"
                >
                  {{ $t('Reject') }}
                </button>
                <button v-if="entry.canManage" type="button" class="btn btn-red btn-xs" :disabled="loading" @click="emit('select', entry, 'remove')">
                  {{ $t('Remove') }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <EmptyListPlaceholder v-else-if="!loading" :class="{inline: compactEmpty}" />
    </LoadingCover>
  </div>
</template>

<script setup>
  import {computed, ref, useId} from 'vue';
  import {i18n} from '@/locale';
  import {formatDateTime} from '@/common/filters-date';
  import {faCircleCheck, faClock, faCircleXmark, faSort, faSortUp, faSortDown} from '@fortawesome/free-solid-svg-icons';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import {permissionLabels, statusLabel} from '@/devices/details/resource-access-labels';

  defineOptions({compatConfig: {MODE: 3}});
  const props = defineProps({entries: {type: Array, required: true}, loading: Boolean, compactEmpty: Boolean});
  const emit = defineEmits(['select']);
  const statusStyles = {
    PENDING: {class: 'text-warning', icon: faClock},
    ACCEPTED: {class: 'text-success', icon: faCircleCheck},
    REJECTED: {class: 'text-danger', icon: faCircleXmark},
  };
  const sortableColumns = [
    {key: 'destination', label: 'Requesting device'}, // i18n
    {key: 'permissions', label: 'Permissions'}, // i18n
    {key: 'status', label: 'Status'}, // i18n
    {key: 'source', label: 'Source device and channel'}, // i18n
    {key: 'lastDecision', label: 'Last decision'}, // i18n
  ];
  const sortKey = ref('destination');
  const sortDirection = ref('asc');
  const sortId = `resource-access-sort-${useId()}`;
  const sortedEntries = computed(() => {
    const collator = new Intl.Collator(i18n.global.locale.value, {numeric: true, sensitivity: 'base'});
    const value = (entry) => {
      switch (sortKey.value) {
        case 'destination':
          return entry.destinationName || String(entry.destinationDeviceId);
        case 'permissions':
          return permissionLabels(entry.permissions)
            .map((label) => i18n.global.t(label))
            .join(', ');
        case 'status':
          return i18n.global.t(statusLabel(entry.status));
        case 'source':
          return `${entry.sourceCaption || entry.sourceChannelId} ${entry.sourceDeviceName || ''}`;
        case 'lastDecision':
          return entry.lastDecisionAt ? Date.parse(entry.lastDecisionAt) : null;
      }
    };
    return [...props.entries].sort((a, b) => {
      const left = value(a),
        right = value(b);
      if (left === null || right === null) {
        if (left !== right) return left === null ? 1 : -1;
      }
      const comparison = typeof left === 'number' && typeof right === 'number' ? left - right : collator.compare(left ?? '', right ?? '');
      return (sortDirection.value === 'asc' ? comparison : -comparison) || b.id - a.id;
    });
  });
  function reverseSort() {
    sortDirection.value = sortDirection.value === 'asc' ? 'desc' : 'asc';
  }
  function sortBy(key) {
    if (sortKey.value === key) reverseSort();
    else {
      sortKey.value = key;
      sortDirection.value = 'asc';
    }
  }
  function ariaSort(key) {
    return sortKey.value === key || (key === 'status' && sortKey.value === 'lastDecision')
      ? sortDirection.value === 'asc'
        ? 'ascending'
        : 'descending'
      : 'none';
  }
  function sortIcon(key) {
    return sortKey.value !== key ? faSort : sortDirection.value === 'asc' ? faSortUp : faSortDown;
  }
</script>

<style scoped lang="scss">
  .resource-access-table {
    width: 100%;
    table-layout: fixed;
    th:nth-child(1) {
      width: 18%;
    }
    th:nth-child(2) {
      width: 18%;
    }
    th:nth-child(3) {
      width: 16%;
    }
    th:nth-child(4) {
      width: 28%;
    }
    th:nth-child(5) {
      width: 20%;
    }
    td {
      overflow-wrap: anywhere;
    }
  }
  .resource-access-sort-button {
    border: 0;
    padding: 0;
    background: none;
    color: inherit;
    font-weight: inherit;
    text-align: left;
  }
  .resource-access-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    justify-content: flex-end;
    .btn {
      max-width: 100%;
      white-space: normal;
    }
  }
  .resource-access-sort,
  .resource-access-field-label {
    display: none;
  }
  @media (max-width: 991px) {
    .resource-access-sort {
      display: block;
    }
    .resource-access-table {
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
      td {
        border: 0;
        padding: 6px 8px;
        text-align: left;
      }
      tr {
        border-top: 1px solid #ddd;
        padding: 8px 0;
      }
    }
    .resource-access-field-label {
      display: block;
      font-weight: bold;
    }
    .resource-access-actions {
      justify-content: flex-start;
    }
  }
</style>
