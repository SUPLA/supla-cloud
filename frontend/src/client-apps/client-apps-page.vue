<template>
  <div>
    <div class="container">
      <div class="clearfix left-right-header">
        <div>
          <h1 v-title>{{ $t('Client’s Apps') }}</h1>
          <h4 class="text-muted">{{ $t('smartphones, tablets, etc.') }}</h4>
        </div>
        <div>
          <devices-registration-button
            v-show="!frontendConfig.maintenanceMode"
            field="clientsRegistrationEnabled"
            caption-i18n="Client registration"
          ></devices-registration-button>
        </div>
      </div>
    </div>
    <div class="container">
      <client-app-filters @filter-function="filterFunction = $event" @compare-function="compareFunction = $event" />
    </div>
    <square-links-grid v-if="compareFunction && clientApps && filteredClientApps.length" :count="filteredClientApps.length">
      <client-app-tile-editable
        v-for="app in filteredClientApps"
        :key="app.id"
        :ref="'app-tile-' + app.id"
        :app="app"
        @change="store.fetchAll(true)"
        @delete="store.fetchAll(true)"
      />
    </square-links-grid>
    <empty-list-placeholder v-else-if="clientApps"></empty-list-placeholder>
    <loader-dots v-else></loader-dots>
  </div>
</template>

<script setup>
  import {useClientAppsStore} from '@/stores/client-apps-store';
  import {storeToRefs} from 'pinia';
  import {useTimeoutPoll} from '@vueuse/core';
  import {useFrontendConfigStore} from '@/stores/frontend-config-store';
  import {computed, ref} from 'vue';
  import LoaderDots from '../common/gui/loaders/loader-dots.vue';
  import DevicesRegistrationButton from '../devices/list/devices-registration-button.vue';
  import EmptyListPlaceholder from '../common/gui/empty-list-placeholder.vue';
  import ClientAppTileEditable from './client-app-tile-editable.vue';
  import ClientAppFilters from './client-app-filters.vue';
  import SquareLinksGrid from '@/common/tiles/square-links-grid.vue';

  const {config: frontendConfig} = storeToRefs(useFrontendConfigStore());

  const store = useClientAppsStore();
  const {list: clientApps} = storeToRefs(store);

  const filterFunction = ref(() => true);
  const compareFunction = ref(undefined);
  const filteredClientApps = computed(() => clientApps.value.filter(filterFunction.value).sort(compareFunction.value || (() => 1)));

  useTimeoutPoll(() => store.fetchAll(true), 7000, {immediate: true, immediateCallback: true});
</script>
