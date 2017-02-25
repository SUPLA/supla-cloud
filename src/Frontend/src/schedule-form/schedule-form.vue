<template>
    <div>
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
                        <schedule-form-mode-minutely v-if="scheduleMode == 'minutely'"></schedule-form-mode-minutely>
                        <schedule-form-mode-hourly v-if="scheduleMode == 'hourly'"></schedule-form-mode-hourly>
                    </div>
                    <next-run-dates-preview></next-run-dates-preview>
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
    import NextRunDatesPreview from "./next-run-dates-preview.vue"
    import {mapState} from "vuex";

    export default {
        name: 'schedule-form',
        computed: mapState(['scheduleMode']),
        components: {
            ScheduleModeChooser,
            ScheduleFormModeOnce,
            ScheduleFormModeMinutely,
            ScheduleFormModeHourly,
            NextRunDatesPreview
        }
    }
</script>

<style lang="scss" rel="stylesheet/scss" scoped>
    .well {
        h3 {
            margin-top: 0;
        }
    }
</style>
