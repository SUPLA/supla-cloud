<template>
  <div class="hvac-setpoints-setter">
    <div v-if="heatAvailable" class="form-group form-group-sm my-1">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <IconHeating />
          {{ $t('Heat to') }}
        </div>
        <NumberInput
          v-model="temperatureHeat"
          :precision="1"
          :min="roomMin"
          :max="roomMax - offsetMin"
          @update:modelValue="onChange('heat')"
          suffix="&deg;C"
          class="flex-grow-1"
        />
      </div>
    </div>
    <div v-if="coolAvailable" class="form-group form-group-sm my-1">
      <div class="d-flex align-items-center">
        <div class="mr-3">
          <IconCooling />
          {{ $t('Cool to') }}
        </div>
        <NumberInput
          v-model="temperatureCool"
          :precision="1"
          :min="roomMin + offsetMin"
          :max="roomMax"
          @update:modelValue="onChange('cool')"
          suffix="&deg;C"
          class="flex-grow-1"
        />
      </div>
    </div>
  </div>
</template>

<script>
  import IconHeating from '@/common/icons/icon-heating.vue';
  import IconCooling from '@/common/icons/icon-cooling.vue';
  import NumberInput from '@/common/number-input.vue';

  export default {
    components: {NumberInput, IconCooling, IconHeating},
    props: {
      subject: Object,
      value: Object,
      hideHeat: Boolean,
      hideCool: Boolean,
    },
    data() {
      return {
        temperatureHeat: 0,
        temperatureCool: 0,
      };
    },
    computed: {
      offsetMin() {
        return this.subject.config.temperatureConstraints?.autoOffsetMin || 0;
      },
      temperatureConstraintName() {
        return this.subject.config?.defaultTemperatureConstraintName || 'room';
      },
      roomMin() {
        return this.subject.config?.temperatureConstraints?.[`${this.temperatureConstraintName}Min`] || 0;
      },
      roomMax() {
        return this.subject.config?.temperatureConstraints?.[`${this.temperatureConstraintName}Max`] || 100;
      },
      heatAvailable() {
        return !this.hideHeat && this.subject.config.heatingModeAvailable;
      },
      coolAvailable() {
        return !this.hideCool && this.subject.config.coolingModeAvailable;
      },
      hasHeat() {
        return this.temperatureHeat !== null && this.temperatureHeat !== '';
      },
      hasCool() {
        return this.temperatureCool !== null && this.temperatureCool !== '';
      },
      modelValue() {
        const modelValue = {};
        if (this.heatAvailable && this.hasHeat) {
          modelValue.heat = +this.temperatureHeat;
        }
        if (this.coolAvailable && this.hasCool) {
          modelValue.cool = +this.temperatureCool;
        }
        return modelValue;
      },
    },
    watch: {
      value() {
        if (this.value) {
          const {heat, cool} = this.modelValue;
          if ((this.heatAvailable && this.value.heat !== heat) || (this.coolAvailable && this.value.cool !== cool)) {
            this.initFromValue();
          }
        }
      },
    },
    mounted() {
      this.temperatureHeat = Math.min(Math.max(this.roomMin, 21), this.roomMax);
      this.temperatureCool = Math.min(Math.max(this.roomMin, 23), this.roomMax);
      if (this.value) {
        this.initFromValue();
      } else {
        this.onChange();
      }
    },
    methods: {
      initFromValue() {
        this.temperatureHeat = this.value.heat;
        this.temperatureCool = this.value.cool;
      },
      onChange(changed) {
        this.$nextTick(() => {
          if (this.hasHeat && this.hasCool) {
            if (changed === 'heat' && this.hasCool) {
              const minCool = +this.temperatureHeat + this.offsetMin;
              if (this.temperatureCool < minCool) {
                this.temperatureCool = minCool;
              }
            }
            if (changed === 'cool' && this.hasHeat) {
              const maxHeat = +this.temperatureCool - this.offsetMin;
              if (this.temperatureHeat > maxHeat) {
                this.temperatureHeat = maxHeat;
              }
            }
          }
          this.$emit('input', this.modelValue);
        });
      },
    },
  };
</script>

<style lang="scss">
  .hvac-setpoints-setter {
    .input-group-addon {
      border: 0 !important;
    }
  }
</style>
