<template>
  <div>
    <dl>
      <dd>{{ $t('Keep history') }}</dd>
      <dt>
        <toggler v-model="props.channel.config.keepHistory" @update:model-value="$emit('change')" />
      </dt>
    </dl>
    <dl>
      <dd>{{ $t('Refresh interval (ms)') }}</dd>
      <dt>
        <NumberInput
          v-model="props.channel.config.refreshIntervalMs"
          :min="0"
          :max="65535"
          :placeholder="0"
          :precision="0"
          :default-value="0"
          @change="$emit('change')"
        />
      </dt>
    </dl>
  </div>
</template>

<script setup>
  import NumberInput from '@/common/number-input.vue';
  import Toggler from '@/common/gui/toggler.vue';

  const props = defineProps({channel: Object});
  defineEmits(['change']);
</script>
