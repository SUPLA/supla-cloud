<script>
  export default {compatConfig: {MODE: 3}};
</script>

<script setup>
  import EnergyCostTariffPicker from './energy-cost-tariff-picker.vue';
  import EnergyCostCustomComposer from './energy-cost-custom-composer.vue';

  defineProps({
    mode: {type: String, default: 'tariff'},
    selectedId: String,
    tariffs: {type: Array, required: true},
    period: Object,
    configuration: Object,
    disabled: Boolean,
    composerReady: {type: Boolean, default: true},
    composerError: String,
    showMoreSettingsInfo: Boolean,
    allowNoTariff: Boolean,
    idPrefix: String,
  });
  const emit = defineEmits(['update:mode', 'update:selectedId', 'update:period', 'update:busy']);
</script>

<template>
  <fieldset :disabled="disabled">
    <ul class="nav nav-tabs mb-3">
      <li :class="{active: mode === 'tariff'}">
        <a href="#" :aria-disabled="disabled" @click.prevent="!disabled && emit('update:mode', 'tariff')">{{ $t('Choose existing tariff') }}</a>
      </li>
      <li :class="{active: mode === 'custom'}">
        <a href="#" :aria-disabled="disabled" @click.prevent="!disabled && emit('update:mode', 'custom')">{{ $t('Compose custom tariff') }}</a>
      </li>
    </ul>
    <energy-cost-tariff-picker
      v-if="mode === 'tariff'"
      :model-value="selectedId"
      :tariffs="tariffs"
      :id-prefix="idPrefix"
      :allow-no-tariff="allowNoTariff"
      @update:model-value="emit('update:selectedId', $event)"
    />
    <template v-else>
      <energy-cost-custom-composer
        v-if="composerReady"
        :period="period"
        :configuration="configuration"
        @update:period="emit('update:period', $event)"
        @update:busy="emit('update:busy', $event)"
      />
      <p v-else-if="!composerError">{{ $t('Loading') }}...</p>
      <p v-if="composerError" class="text-danger">{{ $t(composerError) }}</p>
      <p v-if="showMoreSettingsInfo" class="alert alert-info">
        {{ $t('After assigning the tariff, more settings will be available in Tariff history / settings.') }}
      </p>
    </template>
  </fieldset>
</template>
