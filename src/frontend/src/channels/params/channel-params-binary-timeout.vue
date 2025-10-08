<template>
  <div>
    <dl>
      <dd>{{ $t('Timeout') }}</dd>
      <dt>
        <NumberInput
          v-model="channel.config.timeoutS"
          :min="0.1"
          :max="3600"
          :precision="1"
          suffix=" s"
          class="form-control text-center mt-2"
          @input="emit('change')"
        />
      </dt>
    </dl>
  </div>
</template>

<script setup>
  import NumberInput from '@/common/number-input.vue';

  defineProps({channel: Object});
  const emit = defineEmits(['change']);
</script>
