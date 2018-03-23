<template>
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <h1 v-if="id">
                    <span v-title>{{ $t('Edit schedule') }} ID{{ id }}</span>
                    <span class="small"
                        v-if="schedule.caption">{{ schedule.caption }}</span>
                </h1>
                <h1 v-else
                    v-title>{{ $t('Create New Schedule') }}</h1>
            </div>
        </div>
        <loading-dots v-if="id && !schedule.id"></loading-dots>
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
                v-show="mode">
                <div class="col-md-6">
                    <div class="well">
                        <h3 class="no-margin-top">{{ $t('When') }}?</h3>
                        <div class="form-group">
                            <schedule-form-mode-once v-if="mode == 'once'"></schedule-form-mode-once>
                            <schedule-form-mode-minutely v-if="mode == 'minutely'"></schedule-form-mode-minutely>
                            <schedule-form-mode-hourly v-if="mode == 'hourly'"></schedule-form-mode-hourly>
                            <schedule-form-mode-daily v-if="mode == 'daily'"></schedule-form-mode-daily>
                        </div>
                        <div v-if="mode != 'once'">
                            <schedule-form-start-end-date></schedule-form-start-end-date>
                        </div>
                        <next-run-dates-preview></next-run-dates-preview>
                        <toggler v-model="retry"
                            v-if="canSetRetry"
                            label="Retry when fail"></toggler>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="well">
                        <h3 class="no-margin-top">{{ $t('Action') }}</h3>
                        <schedule-form-action-chooser @channel-change="canSetRetry = !$event || [20, 30].indexOf($event.function) < 0"></schedule-form-action-chooser>
                        <div class="text-right"
                            v-if="!submitting">
                            <router-link :to="{name: 'schedule', params: schedule}"
                                class="btn btn-white"
                                v-if="schedule.id">
                                <i class="pe-7s-back-2"></i>
                                {{ $t('Cancel') }}
                            </router-link>
                            <button class="btn btn-white btn-lg"
                                v-if="schedule.enabled === false"
                                :disabled="actionId == undefined || !nextRunDates.length || fetchingNextRunDates"
                                @click="submit()">
                                <i class="pe-7s-diskette"></i>
                                {{ $t('Save') }}
                            </button>
                            <button class="btn btn-green btn-lg"
                                :disabled="actionId == undefined || !nextRunDates.length || fetchingNextRunDates"
                                @click="submit(true)">
                                <i :class="id ? 'pe-7s-diskette' : 'pe-7s-plus'"></i>
                                {{ $t(id ? (schedule.enabled ? 'Save' : 'Save and enable') : 'Add') }}
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
    import Toggler from "../../common/gui/toggler";

    export default {
        name: 'schedule-form',
        props: ['id'],
        store: new Vuex.Store({
            state: {
                caption: '',
                mode: 'once',
                timeExpression: '',
                dateStart: moment().format(),
                dateEnd: '',
                fetchingNextRunDates: false,
                nextRunDates: [],
                retry: true,
                channelId: undefined,
                actionId: undefined,
                actionParam: undefined,
                submitting: false,
                schedule: {},
            },
            mutations: mutations,
            actions: actions,
            strict: process.env.NODE_ENV !== 'production'
        }),
        data() {
            return {
                canSetRetry: false,
            };
        },
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
            ...mapState(['mode', 'nextRunDates', 'fetchingNextRunDates', 'channelId', 'actionId', 'submitting', 'schedule'])
        },
        mounted() {
            this.resetState();
            if (this.id) {
                this.$http.get('schedules/' + this.id, {params: {include: 'channel'}}).then(({body}) => {
                    this.loadScheduleToEdit(body);
                });
            }
            else if (this.$route.query.channelId) {
                this.$store.commit('updateChannel', this.$route.query.channelId);
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
            Toggler,
        },
        methods: {
            resetState() {
                this.$store.replaceState({
                    caption: '',
                    mode: 'once',
                    timeExpression: '',
                    dateStart: moment().format(),
                    dateEnd: '',
                    fetchingNextRunDates: false,
                    nextRunDates: [],
                    retry: true,
                    channelId: undefined,
                    actionId: undefined,
                    actionParam: undefined,
                    submitting: false,
                    schedule: {},
                });
            },
            ...mapActions(['submit', 'loadScheduleToEdit'])
        },
        watch: {
            '$route'() {
                this.resetState();
            }
        }
    };
</script>
