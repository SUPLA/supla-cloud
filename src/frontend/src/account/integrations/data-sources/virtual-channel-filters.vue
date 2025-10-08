<template>
  <div class="grid-filters">
    <btn-filters id="channelsFiltersSort" v-model="sort" :default-sort="hasDevice && 'channelNumber'" :filters="sorts" @input="filter()"></btn-filters>
    <btn-filters
      v-model="virtualType"
      class="always-dropdown"
      :filters="[
        {label: $t('All'), value: '*'},
        {label: $t('Weather'), value: 1},
      ]"
      @input="filter()"
    ></btn-filters>
    <input v-model="search" type="text" class="form-control" :placeholder="$t('Search')" @input="filter()" />
  </div>
</template>

<script>
  import BtnFilters from '@/common/btn-filters.vue';
  import latinize from 'latinize';
  import {DateTime} from 'luxon';
  import {mapState} from 'pinia';
  import {useLocationsStore} from '@/stores/locations-store';
  import {useDevicesStore} from '@/stores/devices-store';

  export default {
    components: {BtnFilters},
    props: {
      hasDevice: {
        type: Boolean,
        default: false,
      },
    },
    data() {
      return {
        virtualType: '*',
        search: '',
        sort: 'caption',
      };
    },
    mounted() {
      this.filter();
    },
    methods: {
      filter() {
        this.$emit('filter-function', (device) => this.matches(device));
        this.$emit('compare-function', (a, b) => this.compare(a, b));
      },
      matches(channel) {
        if (this.virtualType && this.virtualType !== '*') {
          if (channel.config.virtualChannelType !== this.virtualType) {
            return false;
          }
        }
        if (this.search) {
          const location = this.locations[channel.locationId] || {};
          const device = this.devices[channel.iodeviceId] || {};
          const searchString = latinize(
            [channel.id, channel.caption, device.name, this.$t(channel.type.caption), location.id, location.caption, this.$t(channel.function.caption)].join(
              ' '
            )
          ).toLowerCase();
          return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
        }
        return true;
      },
      compare(a, b) {
        if (this.sort === 'caption') {
          return this.captionForSort(a) < this.captionForSort(b) ? -1 : 1;
        } else if (this.sort === 'regDate') {
          const deviceA = this.devices[a.iodeviceId] || {};
          const deviceB = this.devices[b.iodeviceId] || {};
          return DateTime.fromISO(deviceB.regDate).diff(DateTime.fromISO(deviceA.regDate)).milliseconds;
        } else {
          const locationA = this.locations[a.locationId] || {};
          const locationB = this.locations[b.locationId] || {};
          return this.captionForSort(locationA) < this.captionForSort(locationB) ? -1 : 1;
        }
      },
      captionForSort(channel) {
        return latinize(channel.caption || (channel.function && this.$t(channel.function.caption)) || '')
          .toLowerCase()
          .trim();
      },
    },
    computed: {
      sorts() {
        return [
          {label: this.$t('A-Z'), value: 'caption'},
          {label: this.$t('Created'), value: 'regDate'},
          {label: this.$t('Location'), value: 'location'},
        ];
      },
      ...mapState(useLocationsStore, {locations: 'all'}),
      ...mapState(useDevicesStore, {devices: 'all'}),
    },
  };
</script>
