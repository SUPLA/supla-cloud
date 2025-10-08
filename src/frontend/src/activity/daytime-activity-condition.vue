<template>
  <div>
    <div class="alert alert-info">
      <div class="d-flex align-items-center">
        <div class="flex-grow-1">
          <div>
            <strong>{{ $t('Active from') }}:</strong>
            {{ timeFormatter(model[0]) }}
          </div>
          <div>
            <strong>{{ $t('Active to') }}:</strong>
            {{ timeFormatter(model[1]) }}
          </div>
        </div>
        <a @click="swapTimes()">
          {{ $t('swap') }}
          <fa :icon="faRandom"/>
        </a>
      </div>
      <transition-expand>
        <div class="text-center mt-2" v-if="closestSunrise && changed">
          {{
            $t('Today, it would be active from {from} to {to}.', {
              from: humanizedTimes[0],
              to: humanizedTimes[1]
            })
          }}
        </div>
      </transition-expand>
    </div>
    <div class="mt-5 mb-5 px-2">
      <div class="d-flex">
        <div class="flex-grow-1">

          <FormSlider v-model="offsetSunrise" :min="-120" :max="0" :marks="marks.morning"
            :order="false"
            tooltip="none" :class="{inverted: !isNightMode}" @update:modelValue="changed = true">
            <template #label="{ label }">
              <div class="vue-slider-mark-label">
                <img v-if="label === 'sunrise'" src="../assets/icons/sunrise.svg" alt="sunrise">
                <img v-else-if="label === 'midnight'" src="../assets/icons/moon.svg" alt="midnight">
              </div>
            </template>
          </FormSlider>
        </div>
        <div class="px-1">
          <fa :icon="faSun"/>
        </div>
        <div class="flex-grow-1">
          <FormSlider v-model="offsetSunset" :min="1" :max="120" :marks="marks.evening"
            :order="false"
            tooltip="none" :class="{inverted: isNightMode}" @update:modelValue="changed = true">
            <template #label="{ label }">
              <div class="vue-slider-mark-label">
                <img v-if="label === 'sunset'" src="../assets/icons/sunset.svg" alt="sunset">
                <img v-else-if="label === 'midnight'" src="../assets/icons/moon.svg" alt="midnight">
              </div>
            </template>
          </FormSlider>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  }
</script>

<script setup>
  import {DateTime} from "luxon";
  import {formatDate} from "@/common/filters-date";
  import TransitionExpand from "@/common/gui/transition-expand.vue";
  import {faRandom} from "@fortawesome/free-solid-svg-icons";
  import FormSlider from "@/common/form/FormSlider.vue";
  import {faSun} from "@fortawesome/free-regular-svg-icons";
  import {computed, ref} from "vue";
  import {useSuplaApi} from "@/api/use-supla-api.js";
  import {useI18n} from "vue-i18n";

  const model = defineModel();
  const i18n = useI18n();

  const offsetSunrise = computed({
    get: () => model.value[isNightMode.value ? 1 : 0] || -60,
    set: (value) => model.value = isNightMode.value ? [offsetSunset.value, value] : [value, offsetSunset.value],
  })

  const offsetSunset = computed({
    get: () => model.value[isNightMode.value ? 0 : 1] || 60,
    set: (value) => model.value = isNightMode.value ? [value, offsetSunrise.value] : [offsetSunrise.value, value],
  })

  const marks = {
    morning: {'-120': 'midnight', '-60': 'sunrise'},
    evening: {'60': 'sunset', '120': 'midnight'},
  }

  const {data: closestSun} = useSuplaApi(`users/current?include=sun`).json();

  const closestSunrise = computed(() => closestSun.value?.closestSunrise ? DateTime.fromSeconds(closestSun.value.closestSunrise) : undefined);
  const closestSunset = computed(() => closestSun.value?.closestSunset ? DateTime.fromSeconds(closestSun.value.closestSunset) : undefined);

  function timeFormatter(value) {
    if (value === -120 || value === 120) {
      return i18n.t('midnight');
    } else if (value < -60) {
      return i18n.t('{count} minutes before sunrise', {count: Math.abs(value + 60)});
    } else if (value === -60) {
      return i18n.t('sunrise');
    } else if (value < 0) {
      return i18n.t('{count} minutes after sunrise', {count: Math.abs(value + 60)});
    } else if (value < 60) {
      return i18n.t('{count} minutes before sunset', {count: 60 - value});
    } else if (value === 60) {
      return i18n.t('sunset');
    } else {
      return i18n.t('{count} minutes after sunset', {count: value - 60});
    }
  }

  const isNightMode = computed({
    get: () => model.value[0] > 0,
    set: (value) => {
      if (value) {
        model.value = [offsetSunset.value, offsetSunrise.value];
      } else {
        model.value = [offsetSunrise.value, offsetSunset.value];
      }
    }
  })
  const changed = ref(false);

  function swapTimes() {
    isNightMode.value = !isNightMode.value;
    changed.value = true;
    model.value = [model.value[1], model.value[0]];
  }

  const humanizedTimes = computed(() => {
    return model.value.map(value => {
      let date;
      if (value === -120) {
        date = closestSunrise.value.startOf('day');
      } else if (value === 120) {
        date = closestSunrise.value.endOf('day');
      } else if (value < -60) {
        date = closestSunrise.value.minus({minutes: Math.abs(value + 60)});
      } else if (value === -60) {
        date = closestSunrise.value;
      } else if (value < 0) {
        date = closestSunrise.value.plus({minutes: Math.abs(value + 60)});
      } else if (value < 60) {
        date = closestSunset.value.minus({minutes: 60 - value});
      } else if (value === 60) {
        date = closestSunset.value;
      } else {
        date = closestSunset.value.plus({minutes: value - 60});
      }
      return formatDate(date, DateTime.TIME_24_SIMPLE)
    });
  });
</script>

<style lang="scss">
  @use "../styles/variables" as *;
  @use 'sass:color';

  $activeColor: $supla-green;
  $activeColorHover: color.adjust($supla-green, $lightness: 5%);
  $inactiveColor: color.adjust($supla-grey-light, $lightness: -10%);

  .vue-slider {
    .vue-slider-rail {
      background-color: $inactiveColor;
      transition: background-color .2s linear;
    }
    .vue-slider-process {
      background-color: $activeColor;
      transition: background-color .2s linear;
    }
    .vue-slider-dot-handle {
      border-color: $activeColor;
    }
    .vue-slider-mark-step {
      box-shadow: 0 0 0 2px $inactiveColor;
      &.midday {
        position: relative;
        width: 18px;
        box-shadow: none;
        border-radius: 0;
        > span {
          display: block;
          position: absolute;
          width: 18px;
          text-align: center;
          color: $supla-grey-dark;
          top: -12px;
        }

      }
    }
    .vue-slider-mark-label {
      img {
        max-height: 28px;
      }
    }
    &:hover {
      .vue-slider-process {
        background-color: $activeColorHover;
      }
      .vue-slider-dot-handle {
        &, &:hover {
          border-color: $activeColorHover;
        }
      }
      .vue-slider-mark-step {
        box-shadow: 0 0 0 2px $inactiveColor;
        &.midday {
          box-shadow: none;
          border-radius: 0;
        }
      }
    }
  }

  .vue-slider.inverted {
    .vue-slider-rail {
      background-color: $activeColor;
    }
    .vue-slider-process {
      background-color: $inactiveColor;
    }

    &:hover {
      .vue-slider-rail {
        background-color: $activeColorHover;
      }
      .vue-slider-process {
        background-color: $inactiveColor;
      }
    }
  }
</style>
