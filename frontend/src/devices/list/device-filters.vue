<template>
  <div class="grid-filters">
    <btn-filters
      id="deviceFiltersSort"
      v-model="sort"
      :filters="[
        {label: 'A-Z', value: 'az'},
        {label: $t('Last access'), value: 'lastAccess'},
        {label: $t('Registered'), value: 'regDate'},
        {label: $t('Location'), value: 'location'},
      ]"
      @input="filter()"
    ></btn-filters>
    <btn-filters
      v-model="enabled"
      :filters="[
        {label: $t('All'), value: undefined},
        {label: $t('Enabled'), value: true},
        {label: $t('Disabled'), value: false},
      ]"
      @input="filter()"
    ></btn-filters>
    <btn-filters
      v-model="connected"
      :filters="[
        {label: $t('All'), value: undefined},
        {label: $t('Connected'), value: true},
        {label: $t('Disconnected'), value: false},
      ]"
      @input="filter()"
    ></btn-filters>
    <input v-model="search" type="text" class="form-control" :placeholder="$t('Search')" @input="filter()" />
  </div>
</template>

<script>
  import BtnFilters from '../../common/btn-filters.vue';
  import latinize from 'latinize';
  import {DateTime} from 'luxon';
  import {mapState} from 'pinia';
  import {useLocationsStore} from '@/stores/locations-store';
  import {escapeI18n} from '@/locale';

  export default {
    components: {BtnFilters},
    data() {
      return {
        sort: 'az',
        enabled: undefined,
        connected: undefined,
        search: '',
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
      matches(device) {
        if (this.enabled !== undefined && this.enabled != device.enabled) {
          return false;
        }
        if (this.connected !== undefined && this.connected != device.connected) {
          return false;
        }
        if (this.search) {
          const location = this.locations[device.locationId];
          const searchString = latinize(
            [device.id, device.name, device.gUIDString, device.softwareVersion, device.comment, location.id, location.caption].join(' ')
          ).toLowerCase();
          return searchString.indexOf(latinize(this.search).toLowerCase()) >= 0;
        }
        return true;
      },
      compare(a, b) {
        if (this.sort === 'lastAccess') {
          return DateTime.fromISO(b.lastConnected).diff(DateTime.fromISO(a.lastConnected)).milliseconds;
        } else if (this.sort === 'regDate') {
          return DateTime.fromISO(b.regDate).diff(DateTime.fromISO(a.regDate)).milliseconds;
        } else if (this.sort === 'location') {
          const locationA = this.locations[a.locationId] || {};
          const locationB = this.locations[b.locationId] || {};
          return this.captionForSort(locationA) < this.captionForSort(locationB) ? -1 : 1;
        } else {
          return this.captionForSort(a) < this.captionForSort(b) ? -1 : 1;
        }
      },
      captionForSort(model) {
        return latinize(model.comment || model.caption || this.$t(escapeI18n(model.name)) || '')
          .toLowerCase()
          .trim();
      },
    },
    computed: {
      ...mapState(useLocationsStore, {locations: 'all'}),
    },
  };
</script>
