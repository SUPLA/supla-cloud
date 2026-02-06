<script setup>
  import {computed} from 'vue';
  import ChannelStateColorPreview from '@/channels/state/ChannelStateColorPreview.vue';

  const props = defineProps({temperature: Number});

  const backgroundColor = computed(() => {
    const step = props.temperature;

    const interpolateColor = (step) => {
      let ratio;
      let color1, color2;

      if (step <= 50) {
        ratio = step / 50;
        color1 = {r: 0xff, g: 0x9e, b: 0x4f};
        color2 = {r: 0xff, g: 0xff, b: 0xff};
      } else {
        ratio = (step - 50) / 50;
        color1 = {r: 0xff, g: 0xff, b: 0xff};
        color2 = {r: 0xc7, g: 0xde, b: 0xfe};
      }

      const r = Math.round(color1.r + (color2.r - color1.r) * ratio);
      const g = Math.round(color1.g + (color2.g - color1.g) * ratio);
      const b = Math.round(color1.b + (color2.b - color1.b) * ratio);

      return `#${r.toString(16).padStart(2, '0')}${g.toString(16).padStart(2, '0')}${b.toString(16).padStart(2, '0')}`;
    };

    return interpolateColor(step);
  });
</script>

<template>
  <ChannelStateColorPreview :color="backgroundColor">{{ temperature }}%</ChannelStateColorPreview>
</template>
