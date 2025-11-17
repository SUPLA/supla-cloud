<template>
  <div>
    <!-- i18n: ['timeMarginMode_off', 'timeMarginMode_device', 'timeMarginMode_custom'] -->
    <DropdownMenu>
      <DropdownMenuTrigger button>
        {{ $t(`timeMarginMode_${timeMarginMode}`) }}
      </DropdownMenuTrigger>
      <DropdownMenuContent>
        <li v-for="mode in ['off', 'device', 'custom']" :key="mode">
          <a
            v-show="mode !== timeMarginMode"
            @click="
              timeMarginMode = mode;
              onChange();
            "
          >
            {{ $t(`timeMarginMode_${mode}`) }}
          </a>
        </li>
      </DropdownMenuContent>
    </DropdownMenu>

    <transition-expand>
      <NumberInput v-if="timeMarginMode === 'custom'" v-model="timeMarginValue" :min="1" :max="100" suffix="%" @update:modelValue="onChange()" />
    </transition-expand>
  </div>
</template>

<script>
  import NumberInput from '@/common/number-input.vue';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import DropdownMenu from '@/common/gui/dropdown/dropdown-menu.vue';
  import DropdownMenuTrigger from '@/common/gui/dropdown/dropdown-menu-trigger.vue';
  import DropdownMenuContent from '@/common/gui/dropdown/dropdown-menu-content.vue';

  export default {
    components: {
      DropdownMenuContent,
      DropdownMenuTrigger,
      DropdownMenu,
      TransitionExpand,
      NumberInput,
    },
    props: {
      value: [Number, String],
    },
    data() {
      return {
        timeMarginMode: 'off',
        timeMarginValue: 1,
      };
    },
    mounted() {
      this.initFromModel();
    },
    methods: {
      initFromModel() {
        if (this.value === 'DEVICE_SPECIFIC') {
          this.timeMarginMode = 'device';
        } else if (this.value > 0) {
          this.timeMarginMode = 'custom';
          this.timeMarginValue = this.value;
        } else {
          this.timeMarginMode = 'off';
        }
      },
      onChange() {
        let value = 0;
        if (this.timeMarginMode === 'device') {
          value = 'DEVICE_SPECIFIC';
        } else if (this.timeMarginMode === 'custom') {
          value = this.timeMarginValue;
        }
        this.$emit('input', value);
      },
    },
  };
</script>
