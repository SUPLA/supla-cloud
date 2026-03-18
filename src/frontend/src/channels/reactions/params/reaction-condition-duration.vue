<template>
  <div class="reaction-condition-duration">
    <AccordionRoot>
      <AccordionItem title-i18n="Delay" v-model="isOpened">
        <div class="px-4">
          <TimeIntervalSlider v-model="model" :min="1" :max="3600" seconds class="mt-5" no-fractions />
        </div>
        <div class="alert alert-info d-flex align-items-center mt-4 mx-5">
          <fa :icon="faLightbulb" size="xl" />
          <p class="ml-3">{{ $t('The reaction will fire only afther the condition is met for the specified duration.') }}</p>
        </div>
      </AccordionItem>
    </AccordionRoot>
  </div>
</template>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import TimeIntervalSlider from '@/scenes/time-interval-slider.vue';
  import AccordionRoot from '@/common/gui/accordion/accordion-root.vue';
  import AccordionItem from '@/common/gui/accordion/accordion-item.vue';
  import {computed} from 'vue';
  import {faLightbulb} from '@fortawesome/free-solid-svg-icons';

  const model = defineModel({type: Number});

  const isOpened = computed({
    get: () => model.value > 0,
    set: (v) => {
      model.value = v ? 10 : 0;
    },
  });
</script>
