<template>
  <div>
    <dl>
      <dd>{{ $t('Filtering time') }}</dd>
      <dt>
        <NumberInput
          v-model="channel.config.filteringTimeMs"
          :min="30"
          :max="3000"
          suffix=" ms"
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
