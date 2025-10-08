<template>
  <div>
    <SelectForSubjects
      v-model="chosenSchedule"
      class="schedules-dropdown"
      :options="schedulesForDropdown"
      :caption="scheduleCaption"
      :search-text="scheduleSearchText"
      :option-html="scheduleHtml"
      choose-prompt-i18n="choose the schedule"
    />
  </div>
</template>

<script>
  import SelectForSubjects from '@/devices/select-for-subjects.vue';
  import {api} from '@/api/api.js';

  export default {
    components: {SelectForSubjects},
    props: ['value', 'filter'],
    data() {
      return {
        schedules: undefined,
      };
    },
    computed: {
      schedulesForDropdown() {
        if (!this.schedules) {
          return [];
        }
        this.$emit('update', this.schedules);
        return this.schedules;
      },
      chosenSchedule: {
        get() {
          return this.value;
        },
        set(schedule) {
          this.$emit('input', schedule);
        },
      },
    },
    mounted() {
      this.fetchSchedules();
    },
    methods: {
      fetchSchedules() {
        this.schedules = undefined;
        api.get('schedules?include=closestExecutions').then(({body: schedules}) => {
          this.schedules = schedules.filter(this.filter || (() => true));
        });
      },
      scheduleCaption(schedule) {
        return schedule.caption || `ID${schedule.id}`;
      },
      scheduleSearchText(schedule) {
        return `${schedule.caption || ''} ID${schedule.id}`;
      },
      scheduleHtml(schedule, escape) {
        return `<div>
                            <div class="subject-dropdown-option d-flex">
                                <div class="flex-grow-1">
                                    <h5 class="my-1">
                                        <span class="line-clamp line-clamp-2">${escape(schedule.fullCaption)}</span>
                                        ${schedule.caption ? `<span class="small text-muted">ID${schedule.id}</span>` : ''}
                                    </h5>
                                </div>
                            </div>
                        </div>`;
      },
    },
  };
</script>
