<!-- SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O. -->
<!-- SPDX-License-Identifier: GPL-2.0-or-later -->
<template>
  <div>
    <p>{{ $t('Select OCPP 1.6 JSON with TLS and HTTP Basic authentication (security profile 2) in your charger.') }}</p>
    <div v-for="field in fields" :key="field.key" class="form-group">
      <label :for="'ocpp-' + field.key">{{ $t(field.label) }}</label>
      <div class="d-flex">
        <input :id="'ocpp-' + field.key" :value="connection[field.key]" class="form-control" readonly @focus="$event.target.select()" />
        <CopyButton :text="connection[field.key]" class="ml-2" />
      </div>
    </div>
    <p class="small">
      {{ $t('If your charger has a separate identity field, use the base URL. Otherwise, use the full URL. Do not append the identity twice.') }}
    </p>
    <div v-if="connection.authorizationKey" class="form-group">
      <label for="ocpp-key">{{ $t('Authorization key / password') }}</label>
      <div class="d-flex">
        <input id="ocpp-key" :value="connection.authorizationKey.match(/.{1,4}/g).join('-')" class="form-control" readonly autocomplete="off" />
        <CopyButton :text="connection.authorizationKey" class="ml-2" />
      </div>
      <p class="small">{{ $t('Enter the key without hyphens. The copy button copies it without separators.') }}</p>
      <div class="alert alert-warning">{{ $t('Save this key now. It is shown only once; the server stores only its hash.') }}</div>
    </div>
  </div>
</template>

<script setup>
  import CopyButton from '@/common/copy-button.vue';

  defineOptions({compatConfig: {MODE: 3}});

  defineProps({connection: {type: Object, required: true}});
  const fields = [
    {key: 'url', label: 'Full charger URL'}, // i18n
    {key: 'baseUrl', label: 'OCPP base URL'}, // i18n
    {key: 'chargePointIdentity', label: 'Charge Point identity'}, // i18n
    {key: 'username', label: 'HTTP Basic username'}, // i18n
  ];
</script>
