<script setup>
  import {computed} from 'vue';
  import {storeToRefs} from 'pinia';
  import {useDevicesStore} from '@/stores/devices-store.js';
  import {useChannelsStore} from '@/stores/channels-store.js';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';
  import {channelTitle, deviceTitle} from '@/common/filters.js';
  import ActionableSubjectType from '@/common/enums/actionable-subject-type.js';

  const props = defineProps({entity: Object, current: String});

  const {all: devices} = storeToRefs(useDevicesStore());
  const {all: channels} = storeToRefs(useChannelsStore());

  const channel = computed(() => channels.value[props.entity.subjectId]);
  const device = computed(() => devices.value[channel.value.iodeviceId]);
</script>

<template>
  <BreadcrumbList :current="current" v-if="entity?.subjectType === ActionableSubjectType.CHANNEL && channel">
    <RouterLink :to="{name: 'me'}">{{ $t('My SUPLA') }}</RouterLink>
    <RouterLink :to="{name: 'device', params: {id: channel.iodeviceId}}">{{ deviceTitle(device) }}</RouterLink>
    <RouterLink :to="{name: 'channel', params: {id: channel.id}}">{{ channelTitle(channel) }}</RouterLink>
    <template #alt>
      <slot />
    </template>
  </BreadcrumbList>
  <BreadcrumbList v-else :current="false">
    <template #alt>
      <slot />
    </template>
  </BreadcrumbList>
</template>
