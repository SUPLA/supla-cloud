<template>
  <square-link :class="`clearfix pointer with-label ${backgroundColor}`">
    <router-link :to="linkSpec">
      <h3>{{ caption }}</h3>
      <dl>
        <dd>{{ device.gUIDString }}</dd>
        <dt></dt>
      </dl>
      <div class="separator invisible"></div>
      <dl>
        <dd>ID</dd>
        <dt>{{ device.id }}</dt>
        <dd v-if="device.comment">{{ $t('Name') }}</dd>
        <dt v-if="device.comment">{{ device.name }}</dt>
        <dd>{{ $t('SoftVer') }}</dd>
        <dt>{{ device.softwareVersion }}</dt>
      </dl>
      <dl v-if="!device.locked" class="ellipsis">
        <dd>{{ $t('Location') }}</dd>
        <dt>ID{{ device.locationId }} {{ location.caption || '' }}</dt>
      </dl>
      <div v-if="device.locked" class="square-link-label">
        <span class="label label-warning">{{ $t('Locked') }}</span>
      </div>
      <div v-else class="square-link-label">
        <span v-if="device.relationsCount && device.relationsCount.channelsWithConflict > 0" class="label label-danger">{{ $t('Conflict') }}</span>
        <ConnectionStatusLabel :model="device" />
      </div>
    </router-link>
  </square-link>
</template>

<script>
  import {mapState} from 'pinia';
  import {useLocationsStore} from '@/stores/locations-store';
  import ConnectionStatusLabel from '@/devices/list/connection-status-label.vue';
  import SquareLink from '@/common/tiles/square-link.vue';
  import {deviceTitle} from '@/common/filters.js';

  export default {
    components: {SquareLink, ConnectionStatusLabel},
    props: ['device', 'noLink'],
    computed: {
      ...mapState(useLocationsStore, {locations: 'all'}),
      location() {
        return this.locations[this.device.locationId] || {};
      },
      caption() {
        return deviceTitle(this.device);
      },
      linkSpec() {
        return this.noLink ? {} : {name: 'device', params: {id: this.device.id}};
      },
      backgroundColor() {
        if (this.device.relationsCount?.channelsWithConflict > 0) {
          return 'yellow';
        } else if (!this.device.enabled || this.device.locked) {
          return 'grey';
        } else {
          return 'green';
        }
      },
    },
  };
</script>
