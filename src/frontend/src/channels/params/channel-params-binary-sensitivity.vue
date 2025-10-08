<template>
  <div>
    <dl>
      <dd>{{ $t('Sensitivity') }}</dd>
      <dt>
        <NumberInput
          v-model="channel.config.sensitivity"
          :min="0"
          :max="100"
          :precision="1"
          suffix=" %"
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
