<template>
  <div>
    <FormSlider
      v-model="sliderValue"
      :data="possibleValues"
      :lazy="true"
      class="green"
      tooltip="always"
      tooltip-placement="top"
      :tooltip-formatter="formattedValue"
    ></FormSlider>
    <div class="d-flex">
      <a class="mx-1" @click.stop="less()"><fa :icon="faMinus()" /></a>
      <a class="mx-1" @click.stop="more()"><fa :icon="faPlus()" /></a>
      <slot name="buttons"></slot>
    </div>
  </div>
</template>

<script>
  import {prettyMilliseconds} from '../common/filters';
  import FormSlider from '@/common/form/FormSlider.vue';
  import {faMinus, faPlus} from '@fortawesome/free-solid-svg-icons';

  export default {
    compatConfig: {
      MODE: 3,
    },
    components: {FormSlider},
    props: {
      modelValue: Number,
      seconds: Boolean,
      noFractions: Boolean,
      min: {
        type: Number,
        default: 0,
      },
      max: {
        type: Number,
        default: 1000 * 60 * 60 * 24 * 365,
      },
    },
    data() {
      return {
        possibleValues: [
          250,
          500,
          1000,
          1500,
          2000,
          2500,
          3000,
          3500,
          4000,
          4500, // ms
          ...[...Array(26).keys()].map((k) => k * 1000 + 5000), // s 5 - 30
          ...[...Array(5).keys()].map((k) => k * 5000 + 1000 * 35), // s 35 - 55
          ...[...Array(30).keys()].map((k) => k * 60000 + 60000), // min 1 - 30
          ...[...Array(5).keys()].map((k) => k * 5 * 60000 + 60000 * 35), // min 35-60
          ...[...Array(23).keys()].map((k) => k * 3600000 + 3600000), // h 1-24
          ...[...Array(29).keys()].map((k) => k * 86400000 + 86400000), // d 1-30
          ...[...Array(6).keys()].map((k) => k * 86400000 * 30 + 86400000 * 30), // d 1-30
        ],
      };
    },
    computed: {
      multiplier() {
        return this.seconds ? 1000 : 1;
      },
      sliderValue: {
        get() {
          return this.modelValue * this.multiplier;
        },
        set(value) {
          this.$emit('update:modelValue', value / this.multiplier);
        },
      },
    },
    mounted() {
      this.possibleValues = this.possibleValues
        .filter((v) => v >= this.min * this.multiplier && v <= this.max * this.multiplier)
        .filter((v) => !this.noFractions || v % 1000 === 0);
      if (this.sliderValue > 0 && this.possibleValues.indexOf(this.sliderValue) === -1) {
        this.possibleValues.push(parseInt(this.sliderValue));
        this.possibleValues.sort((a, b) => a - b);
      }
    },
    methods: {
      faPlus() {
        return faPlus;
      },
      faMinus() {
        return faMinus;
      },
      formattedValue(sliderValue = this.sliderValue) {
        return prettyMilliseconds(+sliderValue);
      },
      less() {
        const index = this.possibleValues.indexOf(this.sliderValue);
        this.sliderValue = this.possibleValues[Math.max(0, index - 1)];
      },
      more() {
        const index = this.possibleValues.indexOf(this.sliderValue);
        this.sliderValue = this.possibleValues[Math.min(this.possibleValues.length - 1, index + 1)];
      },
    },
  };
</script>
