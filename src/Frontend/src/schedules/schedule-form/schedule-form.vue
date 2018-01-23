<template>
    <div>
        <loading-dots v-if="scheduleId && !channel"></loading-dots>
        <div v-else>
            <div class="row">
                <div class="col-sm-12">
                    <div class="form-group">
                        <label>{{ $t("Name") }}</label>
                        <input type="text"
                            class="form-control"
                            v-model="caption">
                    </div>
                    <div class="form-group">
                        <label>{{ $t("Schedule mode") }}</label>
                        <div class="clearfix"></div>
                        <schedule-mode-chooser></schedule-mode-chooser>
                    </div>
                </div>
            </div>
            <div class="row"
                v-show="scheduleMode">
                <div class="col-md-6">
                    <div class="well">
                        <h3 class="no-margin-top">{{ $t('When') }}?</h3>
                        <div class="form-group">
                            <schedule-form-mode-once v-if="scheduleMode == 'once'"></schedule-form-mode-once>
                            <schedule-form-mode-minutely v-if="scheduleMode == 'minutely'"></schedule-form-mode-minutely>
                            <schedule-form-mode-hourly v-if="scheduleMode == 'hourly'"></schedule-form-mode-hourly>
                            <schedule-form-mode-daily v-if="scheduleMode == 'daily'"></schedule-form-mode-daily>
                        </div>
                        <div v-if="scheduleMode != 'once'">
                            <schedule-form-start-end-date></schedule-form-start-end-date>
                        </div>
                        <next-run-dates-preview></next-run-dates-preview>
                        <switches v-model="retry"
                            :label="$t('Retry when fail')"
                            type-bold="true"
                            color="green"
                            :emit-on-mount="false"></switches>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="well">
                        <h3 class="no-margin-top">{{ $t('Action') }}</h3>
                        <schedule-form-action-chooser></schedule-form-action-chooser>
                        <div class="text-right"
                            v-if="!submitting">
                            <button class="btn btn-white btn-lg"
                                v-if="schedule.enabled === false"
                                :disabled="action == undefined || !nextRunDates.length || fetchingNextRunDates"
                                @click="submit()">
                                <i class="pe-7s-diskette"></i>
                                {{ $t('Save') }}
                            </button>
                            <button class="btn btn-green btn-lg"
                                :disabled="action == undefined || !nextRunDates.length || fetchingNextRunDates"
                                @click="submit(true)">
                                <i :class="scheduleId ? 'pe-7s-diskette' : 'pe-7s-plus'"></i>
                                {{ $t(scheduleId ? (schedule.enabled ? 'Save' : 'Save and enable') : 'Add') }}
                            </button>
                        </div>
                        <div class="text-right"
                            v-if="submitting">
                            <button-loading></button-loading>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import ScheduleModeChooser from "./schedule-mode-chooser.vue";
    import ScheduleFormModeOnce from "./modes/schedule-form-mode-once.vue";
    import ScheduleFormModeMinutely from "./modes/schedule-form-mode-minutely.vue";
    import ScheduleFormModeHourly from "./modes/schedule-form-mode-hourly.vue";
    import ScheduleFormModeDaily from "./modes/schedule-form-mode-daily.vue";
    import NextRunDatesPreview from "./next-run-dates-preview.vue";
    import ScheduleFormActionChooser from "./actions/schedule-form-action-chooser.vue";
    import ScheduleFormStartEndDate from "./schedule-form-start-end-date.vue";
    import ButtonLoading from "../../common/gui/loaders/button-loading.vue";
    import LoadingDots from "../../common/gui/loaders/loader-dots.vue";
    import Vuex, {mapActions, mapState} from "vuex";
    import 'imports-loader?define=>false,exports=>false!eonasdan-bootstrap-datetimepicker';
    import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css';
    import {actions, mutations} from "./schedule-form-store";
    import Switches from "vue-switches";

    export default {
        name: 'schedule-form',
        props: ['scheduleId'],
        store: new Vuex.Store({
            state: {
                caption: '',
                scheduleMode: 'once',
                timeExpression: '',
                dateStart: moment().format(),
                dateEnd: '',
                fetchingNextRunDates: false,
                nextRunDates: [],
                retry: true,
                channel: undefined,
                action: undefined,
                actionParam: undefined,
                submitting: false,
                schedule: {},
            },
            mutations: mutations,
            actions: actions,
            strict: process.env.NODE_ENV !== 'production'
        }),
        computed: {
            caption: {
                get() {
                    return this.$store.state.caption;
                },
                set(caption) {
                    this.$store.commit('updateCaption', caption);
                }
            },
            retry: {
                get() {
                    return this.$store.state.retry;
                },
                set(retry) {
                    this.$store.commit('updateRetry', retry);
                }
            },
            ...mapState(['scheduleMode', 'nextRunDates', 'fetchingNextRunDates', 'channel', 'action', 'submitting', 'schedule'])
        },
        mounted() {
            if (this.scheduleId) {
                this.$http.get('schedules/' + this.scheduleId, {params:  {include: 'channel'}}).then(({body}) => {
                    this.loadScheduleToEdit(body);
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
            ScheduleFormStartEndDate,
            ButtonLoading,
            LoadingDots,
            Switches
        },
        methods: mapActions(['submit', 'loadScheduleToEdit'])
    };
</script>
