<template>
    <page-container :error="error"
        class="container">
        <loading-cover :loading="submitting || (id && !schedule.id)">
            <pending-changes-page :header="id ? $t('Schedule') + ' ID' + id : $t('Create New Schedule')"
                v-if="schedule">
                <div slot="buttons">
                    <router-link :to="{name: 'schedule', params: {id: schedule.id}}"
                        class="btn btn-grey"
                        v-if="schedule.id">
                        <i class="pe-7s-back"></i>
                        {{ $t('Cancel') }}
                    </router-link>
                    <button class="btn btn-white"
                        :disabled="actionId == undefined || !nextRunDates.length || fetchingNextRunDates"
                        @click="submit()">
                        <i class="pe-7s-diskette"></i>
                        {{ $t('Save') }}
                    </button>
                    <button class="btn btn-green"
                        v-if="schedule.id && schedule.enabled === false"
                        :disabled="actionId == undefined || !nextRunDates.length || fetchingNextRunDates"
                        @click="submit(true)">
                        <i class="pe-7s-diskette"></i>
                        {{ $t('Save and enable') }}
                    </button>
                </div>
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
                                label="Retry on failure"></toggler>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="well">
                            <h3 class="no-margin-top">{{ $t('Action') }}</h3>
                            <schedule-form-action-chooser @subject-change="canSetRetry = !$event || [20, 30].indexOf($event.function) < 0"></schedule-form-action-chooser>
                        </div>
                    </div>
                </div>
            </pending-changes-page>
        </loading-cover>
    </page-container>
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
    import PageContainer from "../../common/pages/page-container";
    import PendingChangesPage from "../../common/pages/pending-changes-page";

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
                subjectId: undefined,
                subject: undefined,
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
                error: false,
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
            ...mapState(['mode', 'nextRunDates', 'fetchingNextRunDates', 'subjectId', 'actionId', 'submitting', 'schedule', 'timeExpression'])
        },
        mounted() {
            this.resetState();
            if (this.id) {
                this.error = false;
                this.$http.get('schedules/' + this.id, {params: {include: 'subject'}, skipErrorHandler: [403, 404]})
                    .then(({body}) => this.loadScheduleToEdit(body))
                    .catch(response => this.error = response.status);
            }
            else if (this.$route.query.subjectId) {
                // this.$store.commit('updateSubject', this.$route.query.subject);
            }
        },
        components: {
            PendingChangesPage,
            PageContainer,
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
                    timeExpression: this.timeExpression,
                    dateStart: moment().format(),
                    dateEnd: '',
                    fetchingNextRunDates: false,
                    nextRunDates: [],
                    retry: true,
                    subjectId: undefined,
                    subject: undefined,
                    actionId: undefined,
                    actionParam: undefined,
                    submitting: false,
                    schedule: {},
                });
            },
            submit(enableIfDisabled) {
                this.$store.dispatch('submit', enableIfDisabled)
                    .then(({body: schedule}) => this.$router.push({name: 'schedule', params: {id: schedule.id}}));
            },
            ...mapActions(['loadScheduleToEdit'])
        },
        watch: {
            '$route'() {
                this.resetState();
            }
        }
    };
</script>
