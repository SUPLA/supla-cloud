<template>
  <div>
    <AccordionRoot multiple>
      <AccordionItem v-if="hasColor" title-i18n="Color" v-model="rgbOpened">
        <ColorColorpicker v-if="rgbOpened" v-model="rgb" :brightness="colorBrightness" @manual-color-change="model.color_brightness = $event.v" />
      </AccordionItem>
      <AccordionItem v-if="hasColor" title-i18n="Color brightness" v-model="colorBrightnessOpened">
        <ColorBrightnessColorpicker v-if="colorBrightnessOpened" v-model="colorBrightness" :color="rgb" />
      </AccordionItem>
      <AccordionItem v-if="hasBrightness" title-i18n="White brightness" v-model="brightnessOpened">
        <ColorBrightnessColorpicker v-if="brightnessOpened" v-model="brightness" />
      </AccordionItem>
      <AccordionItem v-if="hasWhiteTemperature" title-i18n="White temperature" v-model="whiteTemperatureOpened">
        <WhiteTemperatureColorpicker v-if="whiteTemperatureOpened" v-model="whiteTemperature" />
      </AccordionItem>
    </AccordionRoot>
    {{ model }}
  </div>
  <!--    <div v-if="hasBrightness" class="rgbw-parameter">-->
  <!--      <label>{{ $t('Brightness') }}</label>-->
  <!--      <NumberInput v-model="brightness" :min="0" :max="100" />-->
  <!--    </div>-->
  <!--    <hr v-if="hasBrightness && hasColor" />-->
  <!--    <div v-if="hasColor" class="rgbw-parameter">-->
  <!--      <label>{{ $t('Color') }}</label>-->
  <!--      <div class="radio">-->
  <!--        <label>-->
  <!--          <input v-model="hueMode" type="radio" value="choose" />-->
  <!--          {{ $t('Choose') }}-->
  <!--        </label>-->
  <!--      </div>-->
  <!--      <div v-if="hueMode === 'choose'">-->
  <!--        <ColorColorpicker v-model="color" />-->
  <!--      </div>-->
  <!--      {{ model }}-->
  <!--      <div class="radio">-->
  <!--        <label>-->
  <!--          <input v-model="hueMode" type="radio" value="random" />-->
  <!--          {{ $t('Random') }}-->
  <!--        </label>-->
  <!--      </div>-->
  <!--      &lt;!&ndash;      <div class="radio">&ndash;&gt;-->
  <!--      &lt;!&ndash;        <label>&ndash;&gt;-->
  <!--      &lt;!&ndash;          <input v-model="hueMode" type="radio" value="white" />&ndash;&gt;-->
  <!--      &lt;!&ndash;          {{ $t('White') }}&ndash;&gt;-->
  <!--      &lt;!&ndash;        </label>&ndash;&gt;-->
  <!--      &lt;!&ndash;      </div>&ndash;&gt;-->
  <!--    </div>-->
  <!--    &lt;!&ndash;    <div v-if="hasColor" class="rgbw-parameter">&ndash;&gt;-->
  <!--    &lt;!&ndash;      <label>{{ $t('Color brightness') }}</label>&ndash;&gt;-->
  <!--    &lt;!&ndash;      <NumberInput v-model="colorBrightness" :min="0" :max="100" />&ndash;&gt;-->
  <!--    &lt;!&ndash;    </div>&ndash;&gt;-->
  <!--  </div>-->
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
  import {computed, nextTick, onMounted} from 'vue';
  import NumberInput from '@/common/number-input.vue';
  import ColorColorpicker from '@/channels/action/color/color-colorpicker.vue';
  import AccordionRoot from '@/common/gui/accordion/accordion-root.vue';
  import AccordionItem from '@/common/gui/accordion/accordion-item.vue';
  import ColorBrightnessColorpicker from '@/channels/action/color/color-brightness-colorpicker.vue';
  import WhiteTemperatureColorpicker from '@/channels/action/color/white-temperature-colorpicker.vue';

  const props = defineProps({subject: Object});

  const model = defineModel();
  //
  // const hueMode = computed({
  //   get: () => (['random', 'white'].includes(model.value.hue) ? model.value.hue : 'choose'),
  //   set: (value) => (model.value = {...currentModelValue, hue: value === 'choose' ? 0 : value}),
  // });

  const rgb = computed({
    get: () => model.value.rgb || '#FFFFFF',
    set: (value) => (model.value = {...model.value, rgb: value}),
  });

  const rgbOpened = computed({
    get: () => model.value.rgb !== undefined,
    set: (value) => (model.value = {...model.value, rgb: value ? '#FFFFFF' : undefined}),
  });

  // const hue = computed({
  //   get: () => model.value?.hue || 0,
  //   set: (hue) => (model.value = {...currentModelValue.value, hue}),
  // });

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
  //
  // const currentModelValue = computed(() => {
  //   const value = {};
  //   if (hasBrightness.value) {
  //     value.brightness = brightness.value;
  //   }
  //   if (hasColor.value) {
  //     value.hue = color.value.hue;
  //     value.color_brightness = color.value.colorBrightness;
  //   }
  //   return value;
  // });
  //
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

  // export default {
  //   methods: {
  //     onChange() {
  //       let value = {};
  //       if (this.hasBrightness) {
  //         this.brightness = this.ensureBetween(this.brightness, 0, 100);
  //         value.brightness = this.brightness;
  //       }
  //       if (this.hasColor) {
  //         if (this.hueMode === 'choose') {
  //           value.hue = this.ensureBetween(this.hue, 0, 360);
  //         } else {
  //           value.hue = this.hueMode === 'random' ? 'random' : 'white';
  //         }
  //         this.colorBrightness = this.ensureBetween(this.colorBrightness, 0, 100);
  //         value.color_brightness = this.colorBrightness;
  //       }
  //       this.$emit('input', value);
  //     },
  //     ensureBetween(value, min, max) {
  //       if (value < min) {
  //         return min;
  //       } else if (value > max) {
  //         return max;
  //       } else {
  //         return +value;
  //       }
  //     }
  //   },
  //   computed: {},
  // };
</script>

<style lang="scss">
  //.rgbw-parameter {
  //  clear: both;
  //  width: 100%;
  //  margin-bottom: 1em;
  //  &:last-child {
  //    margin-bottom: 0;
  //  }
  //}
</style>
