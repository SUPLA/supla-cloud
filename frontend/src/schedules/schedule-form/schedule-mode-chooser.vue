<template>
  <div class="btn-group btn-group-lg">
    <button
      v-for="mode in availableModes"
      :key="mode"
      class="btn btn-white"
      type="button"
      :class="{'active btn-green': mode === value}"
      @click="changeMode(mode)"
    >
      {{ $t(`scheduleMode_${mode}`) }}
    </button>
  </div>
</template>

<script>
  export default {
    name: 'ScheduleFormChooser',
    props: ['value'],
    data() {
      return {
        // i18n:['scheduleMode_daily', 'scheduleMode_minutely', 'scheduleMode_once', 'scheduleMode_crontab']
        availableModes: ['daily', 'minutely', 'once', 'crontab'],
      };
    },
    methods: {
      changeMode(mode) {
        this.$emit('input', mode);
      },
    },
  };
</script>
