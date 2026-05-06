<template>
  <span class="timezone-picker">
    <SelectForSubjects
      v-model="chosenTimezone"
      class="timezones-dropdown"
      do-not-hide-selected
      :options="availableTimezones"
      :caption="(timezone) => `${timezone.name} (UTC${timezone.offset >= 0 ? '+' : ''}${timezone.offset}) ${timezone.currentTime}`"
      choose-prompt-i18n="choose the timezone"
      @input="updateTimezone($event)"
    />
  </span>
</template>

<script>
  import {DateTime, Settings} from 'luxon';
  import TIMEZONES_LIST from './timezones-list.json';
  import SelectForSubjects from '@/devices/select-for-subjects.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {SelectForSubjects},
    props: ['timezone'],
    data() {
      return {chosenTimezone: undefined};
    },
    computed: {
      availableTimezones() {
        return TIMEZONES_LIST.map(function (timezone) {
          return {
            id: timezone,
            name: timezone,
            offset: DateTime.now().setZone(timezone).offset / 60,
            currentTime: DateTime.now().setZone(timezone).toLocaleString(DateTime.TIME_SIMPLE),
          };
        }).sort(function (timezone1, timezone2) {
          if (timezone1.offset == timezone2.offset) {
            return timezone1.name < timezone2.name ? -1 : 1;
          } else {
            return timezone1.offset - timezone2.offset;
          }
        });
      },
    },
    mounted() {
      this.chosenTimezone = this.timezone ? {id: this.timezone} : undefined;
    },
    methods: {
      updateTimezone(newTimezone) {
        Settings.defaultZone = newTimezone.id;
        api.patch('users/current', {timezone: newTimezone.id, action: 'change:userTimezone'});
      },
    },
  };
</script>
