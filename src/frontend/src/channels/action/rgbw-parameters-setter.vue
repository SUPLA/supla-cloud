<template>
  <div>
    <div v-if="hasBrightness" class="rgbw-parameter">
      <label>{{ $t('Brightness') }}</label>
      <NumberInput v-model="brightness" :min="0" :max="100" />
    </div>
    <hr v-if="hasBrightness && hasColor" />
    <div v-if="hasColor" class="rgbw-parameter">
      <label>{{ $t('Color') }}</label>
      <div class="radio">
        <label>
          <input v-model="hueMode" type="radio" value="choose" />
          {{ $t('Choose') }}
        </label>
      </div>
      <div v-if="hueMode === 'choose'">
        <HueColorpicker v-model="hue" />
      </div>
      <div class="radio">
        <label>
          <input v-model="hueMode" type="radio" value="random" />
          {{ $t('Random') }}
        </label>
      </div>
      <div class="radio">
        <label>
          <input v-model="hueMode" type="radio" value="white" />
          {{ $t('White') }}
        </label>
      </div>
    </div>
    <div v-if="hasColor" class="rgbw-parameter">
      <label>{{ $t('Color brightness') }}</label>
      <NumberInput v-model="colorBrightness" :min="0" :max="100" />
    </div>
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
  import HueColorpicker from './hue-colorpicker.vue';
  import ChannelFunction from '../../common/enums/channel-function';
  import {computed, nextTick, onMounted} from 'vue';
  import NumberInput from '@/common/number-input.vue';

  const props = defineProps({subject: Object});

  const model = defineModel();

  const hueMode = computed({
    get: () => (['random', 'white'].includes(model.value.hue) ? model.value.hue : 'choose'),
    set: (value) => (hue.value = value === 'choose' ? 0 : value),
  });

  const hue = computed({
    get: () => model.value?.hue || 0,
    set: (hue) => (model.value = {...currentModelValue.value, hue}),
  });

  const colorBrightness = computed({
    get: () => model.value?.color_brightness,
    set: (color_brightness) => (model.value = {...currentModelValue.value, color_brightness}),
  });

  const brightness = computed({
    get: () => model.value?.brightness,
    set: (brightness) => (model.value = {...currentModelValue.value, brightness}),
  });

  const functionId = computed(() => props.subject.functionId);
  const hasBrightness = computed(() => [ChannelFunction.DIMMER, ChannelFunction.DIMMERANDRGBLIGHTING].includes(functionId.value));
  const hasColor = computed(() => [ChannelFunction.RGBLIGHTING, ChannelFunction.DIMMERANDRGBLIGHTING].includes(functionId.value));

  const currentModelValue = computed(() => {
    const value = {};
    if (hasBrightness.value) {
      value.brightness = brightness.value;
    }
    if (hasColor.value) {
      value.hue = hue.value;
      value.color_brightness = colorBrightness.value;
    }
    return value;
  });

  onMounted(() => {
    if (Object.keys(model.value || {}).length === 0) {
      if (props.subject.state) {
        model.value = {
          color_brightness: props.subject.state.color_brightness || 0,
          brightness: props.subject.state.brightness || 0,
          hue: props.subject.state.hue || 0,
        };
      }
      nextTick(() => (model.value = currentModelValue.value));
    }
  });

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
  .rgbw-parameter {
    clear: both;
    width: 100%;
    margin-bottom: 1em;
    &:last-child {
      margin-bottom: 0;
    }
  }
</style>
