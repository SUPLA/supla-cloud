<!-- SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O. -->
<!-- SPDX-License-Identifier: GPL-2.0-or-later -->
<template>
  <div class="container">
    <div class="d-flex flex-wrap mb-3">
      <h4 class="flex-grow-1">{{ $t('Electric vehicle chargers') }}</h4>
      <button class="btn btn-green" :disabled="!enabled" @click="open(null)"><i class="pe-7s-plus"></i> {{ $t('Add charger') }}</button>
    </div>
    <LoadingCover :loading="loading">
      <p v-if="error" class="alert alert-danger" role="alert">{{ error }}</p>
      <p v-else-if="!enabled" class="alert alert-info">{{ $t('OCPP is not configured on this server. Contact the administrator.') }}</p>
      <div v-for="station in stations" :key="station.id" class="well d-flex flex-wrap align-items-center">
        <div class="flex-grow-1">
          <h4>{{ devices.all[station.iodeviceId]?.comment || station.name }}</h4>
          <p>{{ station.chargePointIdentity }}</p>
        </div>
        <router-link class="btn btn-default mr-2" :to="{name: 'device.channels', params: {id: station.iodeviceId}}">{{ $t('Channels') }}</router-link>
        <button class="btn btn-green" :disabled="!enabled" @click="open(station)">{{ $t('Charger configuration') }}</button>
      </div>
      <EmptyListPlaceholder v-if="!error && enabled && !stations.length" />
    </LoadingCover>
    <ChargerConfigurationModal v-if="modalOpen" :station="selectedStation" @close="close" @created="refreshAfterCreation" />
  </div>
</template>

<script setup>
  import {onMounted, ref, watch} from 'vue';
  import {useI18n} from 'vue-i18n';
  import {useRoute, useRouter} from 'vue-router';
  import {api} from '@/api/api';
  import {useDevicesStore} from '@/stores/devices-store';
  import {useChannelsStore} from '@/stores/channels-store';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import ChargerConfigurationModal from './charger-configuration-modal.vue';

  defineOptions({compatConfig: {MODE: 3}});
  const {t} = useI18n();
  const route = useRoute();
  const router = useRouter();
  const devices = useDevicesStore();
  const stations = ref([]);
  const enabled = ref(false);
  const loading = ref(true);
  const error = ref('');
  const modalOpen = ref(false);
  const selectedStation = ref();
  function open(station) {
    selectedStation.value = station || undefined;
    modalOpen.value = true;
  }
  function openRequestedStation() {
    const deviceId = route.query.deviceId;
    if (!enabled.value || typeof deviceId !== 'string' || !/^[1-9]\d*$/.test(deviceId)) return;
    const requestedId = Number(deviceId);
    if (!Number.isSafeInteger(requestedId)) return;
    const station = stations.value.find((station) => station.iodeviceId === requestedId);
    if (station) open(station);
  }
  function close() {
    modalOpen.value = false;
    selectedStation.value = undefined;
    if (route.query.deviceId !== undefined) {
      const query = {...route.query};
      delete query.deviceId;
      router.replace({name: 'integrations.ocpp', query});
    }
  }
  async function fetchStations() {
    loading.value = true;
    error.value = '';
    try {
      const {body} = await api.get('integrations/ocpp', {skipErrorHandler: true});
      stations.value = body.stations;
      enabled.value = body.enabled;
      openRequestedStation();
    } catch (e) {
      error.value = t('Could not load chargers. Try again in a while.');
    } finally {
      loading.value = false;
    }
  }
  async function refreshAfterCreation() {
    await fetchStations();
    try {
      await Promise.all([devices.fetchAll(true), useChannelsStore().fetchAll(true)]);
    } catch (e) {
      error.value = t('Could not refresh the device list. Reload the page after saving the key.');
    }
  }
  watch(() => route.query.deviceId, openRequestedStation);
  onMounted(fetchStations);
</script>
