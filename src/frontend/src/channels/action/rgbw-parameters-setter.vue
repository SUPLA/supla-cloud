<template>
  <div>
    <div v-if="rgbwCommands.length > 0" class="mb-3 rgbw-parameters-command-selector">
      <select class="form-control" v-model="rgbwCommand">
        <option v-for="command in rgbwCommands" :key="command" :value="command">{{ $t(`rgbwCommand_label_${command}`) }}</option>
      </select>
    </div>
    <AccordionRoot multiple>
      <AccordionItem v-if="hasColor && shouldDisplayColor" title-i18n="Color" v-model="rgbOpened" :iconOpened="faCheckCircle">
        <ColorColorpicker v-if="rgbOpened" v-model="rgb" :brightness="colorBrightness" @onNewBrightness="model.color_brightness = $event" />
      </AccordionItem>
      <AccordionItem v-if="hasColor && shouldDisplayColorBrightness" title-i18n="Color brightness" v-model="colorBrightnessOpened" :iconOpened="faCheckCircle">
        <ColorBrightnessColorpicker v-if="colorBrightnessOpened" v-model="colorBrightness" :color="rgb" />
      </AccordionItem>
      <AccordionItem v-if="hasBrightness && shouldDisplayBrightness" title-i18n="White brightness" v-model="brightnessOpened" :iconOpened="faCheckCircle">
        <ColorBrightnessColorpicker v-if="brightnessOpened" v-model="brightness" />
      </AccordionItem>
      <AccordionItem
        v-if="hasWhiteTemperature && shouldDisplayWhiteTemperature"
        title-i18n="White temperature"
        v-model="whiteTemperatureOpened"
        :iconOpened="faCheckCircle"
      >
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
  import {computed, reactive} from 'vue';
  import ColorColorpicker from '@/channels/action/color/color-colorpicker.vue';
  import AccordionRoot from '@/common/gui/accordion/accordion-root.vue';
  import AccordionItem from '@/common/gui/accordion/accordion-item.vue';
  import ColorBrightnessColorpicker from '@/channels/action/color/color-brightness-colorpicker.vue';
  import WhiteTemperatureColorpicker from '@/channels/action/color/white-temperature-colorpicker.vue';
  import {faCheckCircle} from '@fortawesome/free-solid-svg-icons';

  const props = defineProps({subject: Object});

  const model = defineModel();

  const rgbwCommand = computed({
    get: () => model.value?.rgbw_command || 'NOT_SET',
    set: (rgbw_command) => (model.value = {...model.value, rgbw_command}),
  });

  const rgb = computed({
    get: () => model.value?.color,
    set: (color) => (model.value = {...model.value, color}),
  });

  const colorBrightness = computed({
    get: () => model.value?.color_brightness,
    set: (color_brightness) => (model.value = {...model.value, color_brightness}),
  });

  const brightness = computed({
    get: () => model.value?.brightness,
    set: (brightness) => (model.value = {...model.value, brightness}),
  });

  const whiteTemperature = computed({
    get: () => model.value?.white_temperature,
    set: (white_temperature) => (model.value = {...model.value, white_temperature}),
  });

  const defaultValues = reactive({
    color: '#FFFFFF',
    color_brightness: 100,
    brightness: 100,
    white_temperature: 100,
  });

  const useSectionOpened = (field) =>
    computed({
      get: () => model.value[field] !== undefined,
      set: (value) => {
        if (value && model.value[field] !== undefined) return;
        if (!value && model.value[field] === undefined) return;
        if (!value) {
          defaultValues[field] = model.value[field];
        }
        model.value = {...model.value, [field]: value ? defaultValues[field] : undefined};
      },
    });

  const rgbOpened = useSectionOpened('color');
  const colorBrightnessOpened = useSectionOpened('color_brightness');
  const brightnessOpened = useSectionOpened('brightness');
  const whiteTemperatureOpened = useSectionOpened('white_temperature');

  const functionId = computed(() => props.subject.functionId);
  const hasBrightness = computed(() =>
    [ChannelFunction.DIMMER, ChannelFunction.DIMMERANDRGBLIGHTING, ChannelFunction.DIMMER_CCT, ChannelFunction.DIMMER_CCT_AND_RGB].includes(functionId.value)
  );
  const hasColor = computed(() =>
    [ChannelFunction.RGBLIGHTING, ChannelFunction.DIMMERANDRGBLIGHTING, ChannelFunction.DIMMER_CCT_AND_RGB].includes(functionId.value)
  );
  const hasWhiteTemperature = computed(() => [ChannelFunction.DIMMER_CCT, ChannelFunction.DIMMER_CCT_AND_RGB].includes(functionId.value));

  const rgbwCommands = computed(() => props.subject.config?.availableRgbwCommands || []);

  const shouldDisplayColor = computed(() => ['NOT_SET', 'SET_RGB_WITHOUT_TURN_ON'].includes(rgbwCommand.value));
  const shouldDisplayColorBrightness = computed(() => ['NOT_SET', 'SET_COLOR_BRIGHTNESS_WITHOUT_TURN_ON'].includes(rgbwCommand.value));
  const shouldDisplayBrightness = computed(() => ['NOT_SET', 'SET_BRIGHTNESS_WITHOUT_TURN_ON'].includes(rgbwCommand.value));
  const shouldDisplayWhiteTemperature = computed(() => ['NOT_SET', 'SET_DIMMER_CCT_WITHOUT_TURN_ON'].includes(rgbwCommand.value));
</script>
