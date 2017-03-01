<template>
    <div>
        <div class="progress" v-if="scheduleId && !channel">
            <div class="progress-bar progress-bar-success progress-bar-striped active" style="width: 100%"></div>
        </div>
        <div v-if="!scheduleId || channel">
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label>{{ $t("Schedule mode") }}</label>
                        <div class="clearfix"></div>
                        <schedule-mode-chooser></schedule-mode-chooser>
                    </div>
                </div>
            </div>
            <div class="row" v-show="scheduleMode">
                <div class="col-md-6">
                    <div class="well">
                        <h3 class="no-margin-top">{{ $t('When') }}?</h3>
                        <div class="form-group">
                            <schedule-form-mode-once v-if="scheduleMode == 'once'"></schedule-form-mode-once>
                            <schedule-form-mode-minutely
                                    v-if="scheduleMode == 'minutely'"></schedule-form-mode-minutely>
                            <schedule-form-mode-hourly v-if="scheduleMode == 'hourly'"></schedule-form-mode-hourly>
                            <schedule-form-mode-daily v-if="scheduleMode == 'daily'"></schedule-form-mode-daily>
                        </div>
                        <div v-if="scheduleMode != 'once'">
                            <schedule-form-start-end-date></schedule-form-start-end-date>
                        </div>
                        <next-run-dates-preview></next-run-dates-preview>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="well">
                        <h3 class="no-margin-top">{{ $t('Action') }}</h3>
                        <schedule-form-action-chooser></schedule-form-action-chooser>
                        <div class="text-right">
                            <button class="btn btn-success btn-lg"
                                    :disabled="action == undefined || !nextRunDates.length || fetchingNextRunDates || submitting"
                                    @click="submit()">
                                <i class="pe-7s-plus" v-show="!scheduleId"></i>
                                {{ $t(scheduleId ? 'Save' : 'Add') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import ScheduleModeChooser from "./schedule-mode-chooser.vue"
    import ScheduleFormModeOnce from "./modes/schedule-form-mode-once.vue"
    import ScheduleFormModeMinutely from "./modes/schedule-form-mode-minutely.vue"
    import ScheduleFormModeHourly from "./modes/schedule-form-mode-hourly.vue"
    import ScheduleFormModeDaily from "./modes/schedule-form-mode-daily.vue"
    import NextRunDatesPreview from "./next-run-dates-preview.vue"
    import ScheduleFormActionChooser from "./actions/schedule-form-action-chooser.vue"
    import ScheduleFormStartEndDate from "./schedule-form-start-end-date.vue"
    import {mapState, mapActions} from "vuex";
    import 'imports-loader?define=>false,exports=>false!eonasdan-bootstrap-datetimepicker';
    import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css';

    export default {
        name: 'schedule-form',
        computed: mapState(['scheduleMode', 'nextRunDates', 'fetchingNextRunDates', 'channel', 'action', 'scheduleId', 'submitting']),
        mounted() {
            if (this.scheduleId) {
                this.$http.get('schedule/' + this.scheduleId).then(({body}) => {
                    this.loadScheduleToEdit(body.schedule);
                });
            }
        },
        components: {
            ScheduleModeChooser,
            ScheduleFormModeOnce,
            ScheduleFormModeMinutely,
            ScheduleFormModeHourly,
            ScheduleFormModeDaily,
            NextRunDatesPreview,
            ScheduleFormActionChooser,
            ScheduleFormStartEndDate
        },
        methods: mapActions(['submit', 'loadScheduleToEdit'])
    }
</script>

<style>
    .no-margin-top {
        margin-top: 0 !important;
    }
</style>
