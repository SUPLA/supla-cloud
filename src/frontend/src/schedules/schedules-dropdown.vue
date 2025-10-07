<template>
    <div>
        <SelectForSubjects
            class="schedules-dropdown"
            :options="schedulesForDropdown"
            :caption="scheduleCaption"
            :search-text="scheduleSearchText"
            :option-html="scheduleHtml"
            choose-prompt-i18n="choose the schedule"
            v-model="chosenSchedule"/>
    </div>
</template>

<script>
    import SelectForSubjects from "@/devices/select-for-subjects.vue";

    export default {
        props: ['value', 'filter'],
        components: {SelectForSubjects},
        data() {
            return {
                schedules: undefined,
            };
        },
        mounted() {
            this.fetchSchedules();
        },
        methods: {
            fetchSchedules() {
                this.schedules = undefined;
                this.$http.get('schedules?include=closestExecutions').then(({body: schedules}) => {
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
                }
            }
        },
    };
</script>
