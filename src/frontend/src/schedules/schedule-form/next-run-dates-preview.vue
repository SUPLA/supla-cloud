<template>
    <div class="next-run-dates-preview">
        <div class="text-center"
            v-if="value.fetchingNextRunDates">
            <button-loading-dots></button-loading-dots>
        </div>
        <div class="forum-group"
            v-if="value.length > 0"
            :class="{opacity60: value.fetching}">
            <h4>{{ $t('The next available executions') }}</h4>
            <div class="list-group">
                <div class="list-group-item"
                    :key="nextScheduleExecution.plannedTimestamp"
                    v-for="nextScheduleExecution in value">
                    <span class="pull-right small text-muted">
                        <span v-if="nextScheduleExecution.action"
                            class="label label-default">
                            {{ actionCaption(nextScheduleExecution.action, schedule.subject) }}
                        </span>
                        <span class="label label-default">
                            {{ humanizeNextRunDate(nextScheduleExecution.plannedTimestamp).fromNow }}
                        </span>
                    </span>
                    {{ humanizeNextRunDate(nextScheduleExecution.plannedTimestamp).date }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ButtonLoadingDots from "../../common/gui/loaders/button-loading-dots.vue";
    import Vue from "vue";
    import {formatDateTimeLong} from "../../common/filters-date";
    import {DateTime} from "luxon";
    import {actionCaption} from "../../channels/channel-helpers";

    export default {
        props: ['value', 'schedule', 'config'],
        components: {ButtonLoadingDots},
        computed: {
            nextRunDatesQuery() {
                return {
                    mode: this.schedule.mode,
                    config: this.schedule.config,
                    dateStart: this.schedule.mode == 'once' ? undefined : this.schedule.dateStart,
                    dateEnd: this.schedule.mode == 'once' ? undefined : this.schedule.dateEnd
                };
            },
            scheduleConfig() {
                return JSON.stringify(this.schedule.config);
            },
        },
        mounted() {
            this.fetchNextScheduleExecutions();
        },
        methods: {
            actionCaption,
            fetchNextScheduleExecutions() {
                const query = this.nextRunDatesQuery;
                if (!query.config) {
                    this.$emit('input', []);
                } else {
                    this.$set(this.value, 'fetching', true);
                    Vue.http.post('schedules/next-schedule-executions', query)
                        .then(({body: nextScheduleExecutions}) => {
                            this.$emit('input', nextScheduleExecutions);
                            if (query != this.nextRunDatesQuery) {
                                this.fetchNextScheduleExecutions();
                            }
                        })
                        .catch(() => this.$emit('input', []));
                }
            },
            humanizeNextRunDate(dateString) {
                return {
                    date: formatDateTimeLong(dateString),
                    fromNow: DateTime.fromISO(dateString).toRelative(),
                };
            },
        },
        watch: {
            'schedule.dateStart'() {
                this.fetchNextScheduleExecutions();
            },
            'schedule.dateEnd'() {
                this.fetchNextScheduleExecutions();
            },
            scheduleConfig() {
                this.fetchNextScheduleExecutions();
            }
        },
    };
</script>

<style lang="scss">
    .opacity60 {
        opacity: .6;
    }

    .next-run-dates-preview {
        .label {
            margin-left: .5em;
        }
    }
</style>
