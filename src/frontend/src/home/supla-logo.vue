<template>
  <img :src="logoPath" alt="SUPLA" :height="size" />
</template>

<script setup>
  import logoUrl from '@/assets/img/logo.svg';
  import logoWinterUrl from '@/assets/img/logo-winter.svg';
  import {DateTime} from 'luxon';
  import {computed} from 'vue';

  defineProps({
    size: {
      type: Number,
      default: 60,
    },
  });

  const logoPath = computed(() => {
    const now = DateTime.now();
    const month = now.month;
    if (month === 12 || month === 1) {
      return logoWinterUrl;
    }
    return logoUrl;
  });
</script>
