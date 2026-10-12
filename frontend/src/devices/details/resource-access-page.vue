<template>
  <div>
    <div class="container">
      <h3>{{ $t('Resource access') }}</h3>
      <ul class="nav nav-tabs mb-4">
        <li :class="{active: view === 'permissions'}">
          <router-link :to="tab('permissions')">{{ $t('Permissions') }}</router-link>
        </li>
        <li :class="{active: view === 'requests'}">
          <router-link :to="tab('requests')">
            {{ $t('Requests') }}
            <span v-if="pending" class="text-warning ml-1 requests-pending-indicator" :title="$t('Pending decisions need your attention.')">
              <fa :icon="faClock" />
              <span class="sr-only">{{ $t('Pending decisions need your attention.') }}</span>
            </span>
          </router-link>
        </li>
      </ul>
    </div>
    <ServerPermissions v-if="view === 'permissions'" :device="device" @updated="loadPending" />
    <ResourceAccess v-else :device="device" :decision-id="decisionId" @updated="loadPending" />
  </div>
</template>

<script setup>
  import {computed, ref, watch, onBeforeUnmount} from 'vue';
  import {useRoute} from 'vue-router';
  import ServerPermissions from '@/devices/details/server-permissions.vue';
  import ResourceAccess from '@/devices/details/resource-access.vue';
  import {api} from '@/api/api';
  import {faClock} from '@fortawesome/free-solid-svg-icons';
  defineOptions({compatConfig: {MODE: 3}});
  const props = defineProps({device: Object});
  const pending = ref(false);
  let generation = 0;
  const route = useRoute();
  const view = computed(() => (route.query.view === 'requests' ? 'requests' : 'permissions'));
  const decisionId = computed(() => (typeof route.query.decisionId === 'string' ? route.query.decisionId : undefined));
  const tab = (view) => ({name: route.name, params: route.params, query: {view}});
  async function loadPending() {
    const current = ++generation;
    const query = new URLSearchParams({status: 'PENDING', limit: '1'});
    if (props.device) query.set('deviceId', props.device.id);
    try {
      const response = await api.get(`resource-access?${query}`, {skipErrorHandler: true});
      if (current === generation) pending.value = response.body.length > 0;
    } catch {
      // Advisory indicator: keep the last known state and the main screen's error feedback.
    }
  }
  watch(
    () => [props.device?.id, view.value],
    () => {
      pending.value = false;
      loadPending();
    },
    {immediate: true, flush: 'sync'}
  );
  onBeforeUnmount(() => generation++);
</script>
