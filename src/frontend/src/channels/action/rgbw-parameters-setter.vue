<template>
  <div>
    <AccordionRoot multiple>
      <AccordionItem v-if="hasColor" title-i18n="Color" v-model="rgbOpened" :iconOpened="faCheckCircle">
        <ColorColorpicker v-if="rgbOpened" v-model="rgb" :brightness="colorBrightness" @manual-color-change="model.color_brightness = $event.v" />
      </AccordionItem>
      <AccordionItem v-if="hasColor" title-i18n="Color brightness" v-model="colorBrightnessOpened" :iconOpened="faCheckCircle">
        <ColorBrightnessColorpicker v-if="colorBrightnessOpened" v-model="colorBrightness" :color="rgb" />
      </AccordionItem>
      <AccordionItem v-if="hasBrightness" title-i18n="White brightness" v-model="brightnessOpened" :iconOpened="faCheckCircle">
        <ColorBrightnessColorpicker v-if="brightnessOpened" v-model="brightness" />
      </AccordionItem>
      <AccordionItem v-if="hasWhiteTemperature" title-i18n="White temperature" v-model="whiteTemperatureOpened" :iconOpened="faCheckCircle">
        <WhiteTemperatureColorpicker v-if="whiteTemperatureOpened" v-model="whiteTemperature" />
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
  import ChannelFunction from '../../common/enums/channel-function';
  import {computed} from 'vue';
  import ColorColorpicker from '@/channels/action/color/color-colorpicker.vue';
  import AccordionRoot from '@/common/gui/accordion/accordion-root.vue';
  import AccordionItem from '@/common/gui/accordion/accordion-item.vue';
  import ColorBrightnessColorpicker from '@/channels/action/color/color-brightness-colorpicker.vue';
  import WhiteTemperatureColorpicker from '@/channels/action/color/white-temperature-colorpicker.vue';
  import {faCheckCircle} from '@fortawesome/free-solid-svg-icons';

  const props = defineProps({subject: Object});

  const model = defineModel();

  const rgb = computed({
    get: () => model.value.color || '#FFFFFF',
    set: (color) => (model.value = {...model.value, color}),
  });

  const rgbOpened = computed({
    get: () => model.value.color !== undefined,
    set: (value) => (model.value = {...model.value, color: value ? '#FFFFFF' : undefined}),
  });

  const colorBrightness = computed({
    get: () => model.value?.color_brightness || 100,
    set: (color_brightness) => (model.value = {...model.value, color_brightness}),
  });

  const colorBrightnessOpened = computed({
    get: () => model.value.color_brightness !== undefined,
    set: (value) => (model.value = {...model.value, color_brightness: value ? 100 : undefined}),
  });

  const brightness = computed({
    get: () => model.value?.brightness,
    set: (brightness) => (model.value = {...model.value, brightness}),
  });

  const brightnessOpened = computed({
    get: () => model.value.brightness !== undefined,
    set: (value) => (model.value = {...model.value, brightness: value ? 100 : undefined}),
  });

  const whiteTemperature = computed({
    get: () => model.value?.white_temperature,
    set: (white_temperature) => (model.value = {...model.value, white_temperature}),
  });

  const whiteTemperatureOpened = computed({
    get: () => model.value.white_temperature !== undefined,
    set: (value) => (model.value = {...model.value, white_temperature: value ? 100 : undefined}),
  });

  const functionId = computed(() => props.subject.functionId);
  const hasBrightness = computed(() =>
    [ChannelFunction.DIMMER, ChannelFunction.DIMMERANDRGBLIGHTING, ChannelFunction.DIMMER_CCT, ChannelFunction.DIMMER_CCT_AND_RGB].includes(functionId.value)
  );
  const hasColor = computed(() =>
    [ChannelFunction.RGBLIGHTING, ChannelFunction.DIMMERANDRGBLIGHTING, ChannelFunction.DIMMER_CCT_AND_RGB].includes(functionId.value)
  );
  const hasWhiteTemperature = computed(() => [ChannelFunction.DIMMER_CCT, ChannelFunction.DIMMER_CCT_AND_RGB].includes(functionId.value));

  // onMounted(() => {
  //   if (Object.keys(model.value || {}).length === 0) {
  //     if (props.subject.state) {
  //       model.value = {
  //         color_brightness: props.subject.state.color_brightness || 0,
  //         brightness: props.subject.state.brightness || 0,
  //         hue: props.subject.state.hue || 0,
  //       };
  //     }
  //     nextTick(() => (model.value = currentModelValue.value));
  //   }
  // });
</script>
