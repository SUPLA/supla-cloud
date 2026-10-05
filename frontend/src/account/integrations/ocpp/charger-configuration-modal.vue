<!-- SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O. -->
<!-- SPDX-License-Identifier: GPL-2.0-or-later -->
<template>
  <Modal :header="connection ? $t('Charger configuration') : $t('Add charger')" @cancel="close" @confirm="connection ? saveName() : create()">
    <template v-if="!connection">
      <p>{{ $t('A charger creates a virtual device with an electricity meter and an on/off switch.') }}</p>
      <div class="form-group">
        <label for="ocpp-name">{{ $t('Charger name') }}</label>
        <input id="ocpp-name" v-model="name" class="form-control" maxlength="100" :disabled="loading" autofocus />
      </div>
      <div class="form-group">
        <label for="ocpp-location">{{ $t('Location') }}</label>
        <select id="ocpp-location" v-model="locationId" class="form-control" :disabled="loading">
          <option :value="null" disabled>{{ $t('Choose location') }}</option>
          <option v-for="location in locations.list" :key="location.id" :value="location.id">{{ location.caption || location.id }}</option>
        </select>
      </div>
    </template>
    <template v-else>
      <div class="form-group">
        <label for="ocpp-name">{{ $t('Charger name') }}</label>
        <input id="ocpp-name" v-model="name" class="form-control" maxlength="200" :disabled="loading" />
      </div>
      <ChargerConnectionSettings :connection="connection" />
      <template v-if="!connection.authorizationKey">
        <p>{{ $t('The old key cannot be retrieved. Generate a new one if you have lost it.') }}</p>
        <div v-if="confirmReset" class="alert alert-warning">
          <p>
            {{ $t('Replace the authorization key? Update the charger settings and reconnect it. Existing connections may remain active until disconnected.') }}
          </p>
          <button class="btn btn-danger mr-2" :disabled="loading" @click="regenerateKey">{{ $t('Generate new key') }}</button>
          <button class="btn btn-default" :disabled="loading" @click="confirmReset = false">{{ $t('Cancel') }}</button>
        </div>
        <button v-else class="btn btn-default" :disabled="loading" @click="confirmReset = true">{{ $t('Generate new key') }}</button>
      </template>
    </template>
    <p v-if="error" class="text-danger" role="alert">{{ error }}</p>
    <template #footer>
      <button v-if="!connection" class="btn btn-default mr-2" :disabled="loading" @click="close">{{ $t('Cancel') }}</button>
      <button v-if="!connection" class="btn btn-green" :disabled="loading || !name.trim() || !locationId" @click="create">{{ $t('Add charger') }}</button>
      <template v-else>
        <button class="btn btn-default mr-2" :disabled="loading" @click="close">{{ $t('Close') }}</button>
        <button class="btn btn-green" :disabled="loading || !canSaveName" @click="saveName">{{ $t('Save changes') }}</button>
      </template>
    </template>
  </Modal>
</template>

<script setup>
  import {computed, ref} from 'vue';
  import {useI18n} from 'vue-i18n';
  import {api} from '@/api/api';
  import {useLocations} from '@/stores/locations-store';
  import Modal from '@/common/modal.vue';
  import ChargerConnectionSettings from './charger-connection-settings.vue';

  defineOptions({compatConfig: {MODE: 3}});
  const props = defineProps({station: Object});
  const emit = defineEmits(['close', 'created', 'updated']);
  const {t} = useI18n();
  const locations = useLocations();
  const name = ref(props.station?.name || '');
  const savedName = ref(name.value);
  const locationId = ref(null);
  const connection = ref(props.station ? {...props.station} : null);
  const loading = ref(false);
  const error = ref('');
  const confirmReset = ref(false);
  const canSaveName = computed(() => !!name.value.trim() && name.value.trim() !== savedName.value);

  function close() {
    if (!loading.value) emit('close');
  }

  async function create() {
    if (loading.value || connection.value || !name.value.trim() || !locationId.value) return;
    loading.value = true;
    error.value = '';
    try {
      const {body} = await api.post('integrations/ocpp', {name: name.value.trim(), locationId: locationId.value}, {skipErrorHandler: true});
      connection.value = body;
      name.value = body.name;
      savedName.value = body.name;
      emit('created');
    } catch (e) {
      error.value = t(e.body?.message || 'Could not add the charger. Try again in a while.');
    } finally {
      loading.value = false;
    }
  }

  async function saveName() {
    if (loading.value || !connection.value || !canSaveName.value) return;
    loading.value = true;
    error.value = '';
    try {
      const newName = name.value.trim();
      const {body} = await api.put(`iodevices/${connection.value.iodeviceId}?safe=1`, {comment: newName}, {skipErrorHandler: true});
      name.value = newName;
      savedName.value = newName;
      emit('updated', body);
    } catch (e) {
      error.value = t(e.body?.message || 'Could not save charger name. Try again in a while.');
    } finally {
      loading.value = false;
    }
  }

  async function regenerateKey() {
    if (loading.value || !confirmReset.value) return;
    loading.value = true;
    error.value = '';
    try {
      const {body} = await api.post(`integrations/ocpp/${connection.value.id}/authorization-key`, {}, {skipErrorHandler: true});
      connection.value = body;
      confirmReset.value = false;
    } catch (e) {
      error.value = t(e.body?.message || 'Could not generate the key. Try again in a while.');
    } finally {
      loading.value = false;
    }
  }
</script>
