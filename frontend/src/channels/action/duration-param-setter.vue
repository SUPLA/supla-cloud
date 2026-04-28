<template>
  <div>
    <div v-show="!hideNoTimer" class="radio mt-0">
      <label>
        <input v-model="countdownMode" type="radio" value="none" @change="onChange()" />
        {{ $t('Until the next change') }}
      </label>
    </div>
    <div v-if="withCalendar || !hideNoTimer" class="radio">
      <label>
        <input v-model="countdownMode" type="radio" value="delay" @change="onChange()" />
        {{ $t('For a period') }}
      </label>
    </div>
    <transition-expand>
      <div v-if="countdownMode === 'delay'" class="form-group my-1">
        <span class="input-group">
          <input v-model="countdownValue" type="number" class="form-control" min="0" @change="onChange()" />
          <span class="input-group-btn">
            <a class="btn btn-white" @click="changeMultiplier()">
              <span v-if="multiplier === 1">{{ $t('milliseconds') }}</span>
              <span v-if="multiplier === 1000">{{ $t('seconds') }}</span>
              <span v-if="multiplier === 60000">{{ $t('minutes') }}</span>
              <span v-if="multiplier === 3600000">{{ $t('hours') }}</span>
              <span v-if="multiplier === 86400000">{{ $t('days') }}</span>
            </a>
          </span>
        </span>
      </div>
    </transition-expand>
    <div v-if="withCalendar" class="radio">
      <label>
        <input v-model="countdownMode" type="radio" value="calendar" @change="onDateChange()" />
        {{ $t('Until a date and time') }}
      </label>
    </div>
    <transition-expand>
      <div v-if="countdownMode === 'calendar'" class="form-group">
        <input v-model="countdownDate" type="datetime-local" class="form-control" :min="minDate" @change="onDateChange()" />
      </div>
    </transition-expand>
  </div>
</template>

<script>
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import {formatDateForHtmlInput} from '@/common/filters-date';
  import {DateTime} from 'luxon';

  export default {
    components: {TransitionExpand},
    props: {
      value: Number,
      withCalendar: Boolean,
      hideNoTimer: Boolean,
      disableMs: Boolean,
    },
    data() {
      return {
        countdownMode: 'none',
        multiplier: 60000,
        countdownValue: 15,
        countdownDate: undefined,
        dateUpdateInterval: undefined,
      };
    },
    computed: {
      minDate() {
        return formatDateForHtmlInput(DateTime.now().toISO());
      },
    },
    watch: {
      value() {
        if (this.value !== this.multiplier * this.countdownValue) {
          this.setValueAndMultiplier(this.value);
        }
      },
    },
    beforeMount() {
      this.dateUpdateInterval = setInterval(() => {
        if (this.countdownMode === 'calendar' && this.countdownDate) {
          this.onDateChange();
        }
      }, 60000);
      this.setValueAndMultiplier(this.value);
    },
    beforeUnmount() {
      clearInterval(this.dateUpdateInterval);
    },
    methods: {
      setValueAndMultiplier(value) {
        for (const multiplier of [86400000, 3600000, 60000, 1000, 1]) {
          if (value % multiplier === 0) {
            this.multiplier = multiplier;
            this.countdownValue = Math.round(value / multiplier);
            break;
          }
        }
        if (this.value || this.hideNoTimer) {
          this.countdownMode = 'delay';
        }
        if (!this.value && this.countdownMode === 'delay') {
          this.onChange();
        }
      },
      onChange() {
        if (this.countdownMode === 'delay' && !this.countdownValue) {
          this.countdownValue = 15;
          this.multiplier = 60000;
        } else if (this.countdownMode === 'none') {
          this.countdownValue = 0;
        }
        this.$emit('input', this.countdownValue * this.multiplier);
      },
      changeMultiplier() {
        if (this.multiplier === 1) {
          this.multiplier = 1000;
        } else if (this.multiplier === 1000) {
          this.multiplier = 60000;
        } else if (this.multiplier === 60000) {
          this.multiplier = 3600000;
        } else if (this.multiplier === 3600000) {
          this.multiplier = 86400000;
        } else {
          this.multiplier = this.disableMs ? 1000 : 1;
        }
        this.onChange();
      },
      onDateChange() {
        this.multiplier = 60000;
        const targetDate = DateTime.fromISO(this.countdownDate).startOf('minute');
        const now = DateTime.now().startOf('minute');
        if (now < targetDate) {
          this.countdownValue = targetDate.diff(now).as('minutes');
        } else {
          this.countdownValue = 0;
        }
        this.onChange();
      },
    },
  };
</script>
