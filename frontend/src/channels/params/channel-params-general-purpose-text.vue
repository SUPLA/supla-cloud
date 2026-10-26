<template>
  <div>
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

  const props = defineProps({channel: Object});
  defineEmits(['change']);
</script>
