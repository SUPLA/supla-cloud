<template>
  <div>
    <div class="container">
      <h3>{{ $t('Resource access') }}</h3>
      <ul class="nav nav-tabs mb-4">
        <li :class="{active: view === 'permissions'}">
          <router-link :to="tab('permissions')">{{ $t('Permissions') }}</router-link>
        </li>
        <li :class="{active: view === 'requests'}">
          <router-link :to="tab('requests')">{{ $t('Requests') }}</router-link>
        </li>
      </ul>
    </div>
    <ServerPermissions v-if="view === 'permissions'" :device="device" />
    <ResourceAccess v-else :device="device" :decision-id="decisionId" />
  </div>
</template>

<script setup>
  import {computed} from 'vue';
  import {useRoute} from 'vue-router';
  import ServerPermissions from '@/devices/details/server-permissions.vue';
  import ResourceAccess from '@/devices/details/resource-access.vue';
  defineOptions({compatConfig: {MODE: 3}});
  defineProps({device: Object});
  const route = useRoute();
  const view = computed(() => (route.query.view === 'requests' ? 'requests' : 'permissions'));
  const decisionId = computed(() => (typeof route.query.decisionId === 'string' ? route.query.decisionId : undefined));
  const tab = (view) => ({name: route.name, params: route.params, query: {view}});
</script>
