<template>
  <div v-if="!empty">
    <div class="container">
      <h3>{{ $t('Resource access') }}</h3>
      <ul class="nav nav-tabs mb-4">
        <li v-if="permissions || permissionsFailed" :class="{active: view === 'permissions'}">
          <router-link :to="tab('permissions')">{{ $t('Permissions') }}</router-link>
        </li>
        <li v-if="requests || requestsFailed" :class="{active: view === 'requests'}">
          <router-link :to="tab('requests')">
            {{ $t('Requests') }}
            <span v-if="pending" class="text-warning ml-1 requests-pending-indicator" :title="$t('Pending decisions need your attention.')">
              <fa :icon="faClock" />
              <span class="sr-only">{{ $t('Pending decisions need your attention.') }}</span>
            </span>
          </router-link>
        </li>
      </ul>
      <p v-if="failed" class="text-warning" role="alert">{{ $t('Error when communicating the server. Try again in a while.') }}</p>
    </div>
    <ServerPermissions v-if="view === 'permissions'" :device="device" @updated="updated" />
    <ResourceAccess v-else :device="device" :decision-id="decisionId" @updated="updated" @close-selected-request="clearSelectedRequest" />
  </div>
</template>

<script setup>
  import {computed, inject, watch} from 'vue';
  import {useRoute, useRouter} from 'vue-router';
  import ServerPermissions from '@/devices/details/server-permissions.vue';
  import ResourceAccess from '@/devices/details/resource-access.vue';
  import {faClock} from '@fortawesome/free-solid-svg-icons';
  import {resourceAccessAvailabilityKey, useResourceAccessAvailability} from '@/devices/details/resource-access-availability';
  defineOptions({compatConfig: {MODE: 3}});
  const props = defineProps({device: Object});
  const route = useRoute();
  const router = useRouter();
  const access = inject(resourceAccessAvailabilityKey, null) || useResourceAccessAvailability(() => props.device?.id);
  const {permissions, requests, pending, permissionsFailed, requestsFailed, failed, empty} = access;
  const view = computed(() => {
    const requested = route.query.view === 'requests' ? 'requests' : 'permissions';
    if (requested === 'permissions' && permissions.value === false && !permissionsFailed.value && (requests.value || requestsFailed.value)) return 'requests';
    if (requested === 'requests' && requests.value === false && !requestsFailed.value && (permissions.value || permissionsFailed.value)) return 'permissions';
    return requested;
  });
  const decisionId = computed(() => (typeof route.query.decisionId === 'string' ? route.query.decisionId : undefined));
  const tab = (view) => ({name: route.name, params: route.params, query: {view}});
  function clearSelectedRequest() {
    const query = {...route.query};
    delete query.decisionId;
    router.replace({name: route.name, params: route.params, query});
  }
  async function updated() {
    if (view.value === 'requests' && route.query.view !== 'requests') {
      await router.replace({name: route.name, params: route.params, query: {...route.query, view: 'requests'}});
    }
    access.reload();
  }
  watch(
    empty,
    (value) => {
      if (value) {
        router.replace(props.device ? {name: props.device.locked ? 'device.unlock' : 'device.channels', params: {id: props.device.id}} : {name: 'safety.log'});
      }
    },
    {immediate: true}
  );
</script>
