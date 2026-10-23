<template>
  <div class="details-tabs">
    <div v-if="availableTabs.length > 1" class="container">
      <div class="form-group">
        <ul class="nav nav-tabs">
          <li v-for="tabDefinition in availableTabs" :key="tabDefinition.id" :class="{active: $route.name === tabDefinition.route}">
            <router-link :to="{name: tabDefinition.route, params: {id: device.id}}">
              {{ $t(tabDefinition.header) }}
              <span
                v-if="tabDefinition.route === 'device.resource-access' && access.pending.value"
                class="text-warning ml-1 requests-pending-indicator"
                :title="$t('Pending decisions need your attention.')"
              >
                <fa :icon="faClock" />
                <span class="sr-only">{{ $t('Pending decisions need your attention.') }}</span>
              </span>
              <span v-if="tabDefinition.count !== undefined">({{ tabDefinition.count() }})</span>
            </router-link>
          </li>
        </ul>
      </div>
    </div>
    <RouterView :device="device" />
  </div>
</template>

<script setup>
  import {computed, onMounted, provide} from 'vue';
  import {useRoute, useRouter} from 'vue-router';
  import {faClock} from '@fortawesome/free-solid-svg-icons';
  import {resourceAccessAvailabilityKey, useResourceAccessAvailability} from '@/devices/details/resource-access-availability';

  defineOptions({compatConfig: {MODE: 3}});
  const props = defineProps({device: Object});
  const router = useRouter();
  const route = useRoute();
  const access = useResourceAccessAvailability(() => props.device.id);
  provide(resourceAccessAvailabilityKey, access);

  const availableTabs = computed(() => {
    const tabs = [];
    if (props.device.locked) {
      tabs.push({header: 'Unlock the device', route: 'device.unlock'}); // i18n
      tabs.push({header: 'Device', route: 'device.details'}); // i18n
      if (access.hasEntries.value) tabs.push({header: 'Resource access', route: 'device.resource-access'}); // i18n
    } else {
      tabs.push({header: 'Channels', route: 'device.channels'}); // i18n
      tabs.push({header: 'Device', route: 'device.details'}); // i18n
      if (access.hasEntries.value) tabs.push({header: 'Resource access', route: 'device.resource-access'}); // i18n
      if (props.device.relationsCount.managedNotifications) {
        tabs.push({header: 'Notifications', route: 'device.notifications'}); // i18n
      }
    }
    return tabs;
  });

  onMounted(() => {
    if (availableTabs.value.length) {
      if (route.name === 'device' || (route.name !== 'device.resource-access' && !availableTabs.value.map((t) => t.route).includes(route.name))) {
        router.replace({name: availableTabs.value[0].route, params: {id: props.device.id}});
      }
    }
  });
</script>
