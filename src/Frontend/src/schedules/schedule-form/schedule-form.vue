<template>
    <page-container :error="error"
        class="container">
        <loading-cover :loading="!schedule || submitting">
            <pending-changes-page :header="schedule.id ? $t('Schedule') + ' ID' + id : $t('Create New Schedule')"
                v-if="schedule">
                <div slot="buttons">
                    <router-link :to="{name: 'schedule', params: {id: schedule.id}}"
                        class="btn btn-grey"
                        v-if="schedule.id">
                        <i class="pe-7s-back"></i>
                        {{ $t('Cancel') }}
                    </router-link>
                    <button class="btn btn-white"
                        :disabled="schedule.actionId == undefined || !nextRunDates.length || nextRunDates.fetching"
                        @click="submit()">
                        <i class="pe-7s-diskette"></i>
                        {{ $t('Save') }}
                    </button>
                    <button class="btn btn-green"
                        v-if="schedule.id && schedule.enabled === false"
                        :disabled="schedule.actionId == undefined || !nextRunDates.length || nextRunDates.fetching"
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
                                v-model="schedule.caption">
                        </div>
                        <div class="form-group">
                            <label>{{ $t("Schedule mode") }}</label>
                            <div class="clearfix"></div>
                            <schedule-mode-chooser v-model="schedule.mode"></schedule-mode-chooser>
                        </div>
                    </div>
                </div>
                <div class="row"
                    v-show="schedule.mode">
                    <div class="col-md-6">
                        <div class="well">
                            <h3 class="no-margin-top">{{ $t('When') }}?</h3>
                            <div class="form-group">
                                <schedule-form-mode-once v-if="schedule.mode == 'once'"
                                    v-model="schedule.timeExpression"></schedule-form-mode-once>
                                <schedule-form-mode-minutely v-if="schedule.mode == 'minutely'"
                                    v-model="schedule.timeExpression"></schedule-form-mode-minutely>
                                <schedule-form-mode-hourly v-if="schedule.mode == 'hourly'"
                                    v-model="schedule.timeExpression"></schedule-form-mode-hourly>
                                <schedule-form-mode-daily v-if="schedule.mode == 'daily'"
                                    v-model="schedule.timeExpression"></schedule-form-mode-daily>
                            </div>
                            <div v-if="schedule.mode !== 'once'">
                                <schedule-form-start-end-date v-model="startEndDate"></schedule-form-start-end-date>
                            </div>
                            <next-run-dates-preview v-model="nextRunDates"
                                :schedule="schedule"></next-run-dates-preview>
                            <toggler v-model="schedule.retry"
                                v-if="canSetRetry"
                                label="Retry on failure"></toggler>
                        </div>
                    </div>
                    {{ schedule }}
                    <div class="col-md-6">
                        <div class="well">
                            <h3 class="no-margin-top">{{ $t('Action') }}</h3>
                            <div class="form-group">
                                <label>{{ $t('Subject') }}</label>
                                <subject-dropdown v-model="schedule.subject"
                                    channels-dropdown-params="io=output&hasFunction=1"
                                    :filter="filterOutNotSchedulableSubjects"></subject-dropdown>
                            </div>
                            <div v-if="schedule.subject">
                                <channel-action-chooser :subject="schedule.subject"
                                    v-model="scheduleAction"
                                    :possible-action-filter="possibleActionFilter"></channel-action-chooser>
                            </div>
                        </div>
                    </div>
                </div>
            </pending-changes-page>
        </loading-cover>
    </page-container>
</template>

<script>
    import ScheduleModeChooser from "./schedule-mode-chooser.vue";
    import ScheduleFormModeOnce from "./modes/schedule-form-mode-once.vue";
    import ScheduleFormModeMinutely from "./modes/schedule-form-mode-minutely.vue";
    import ScheduleFormModeHourly from "./modes/schedule-form-mode-hourly.vue";
    import ScheduleFormModeDaily from "./modes/schedule-form-mode-daily.vue";
    import NextRunDatesPreview from "./next-run-dates-preview.vue";
    import ScheduleFormStartEndDate from "./schedule-form-start-end-date.vue";
    import ButtonLoading from "../../common/gui/loaders/button-loading.vue";
    import LoadingDots from "../../common/gui/loaders/loader-dots.vue";
    import 'imports-loader?define=>false,exports=>false!eonasdan-bootstrap-datetimepicker';
    import 'eonasdan-bootstrap-datetimepicker/build/css/bootstrap-datetimepicker.css';
    import Toggler from "../../common/gui/toggler";
    import PageContainer from "../../common/pages/page-container";
    import PendingChangesPage from "../../common/pages/pending-changes-page";
    import AppState from "../../router/app-state";
    import SubjectDropdown from "../../devices/subject-dropdown";
    import ChannelActionChooser from "../../channels/action/channel-action-chooser";
    import Vue from "vue";

    export default {
        props: ['id'],
        components: {
            ChannelActionChooser,
            SubjectDropdown,
            PendingChangesPage,
            PageContainer,
            ScheduleModeChooser,
            ScheduleFormModeOnce,
            ScheduleFormModeMinutely,
            ScheduleFormModeHourly,
            ScheduleFormModeDaily,
            NextRunDatesPreview,
            ScheduleFormStartEndDate,
            ButtonLoading,
            LoadingDots,
            Toggler,
        },
        data() {
            return {
                error: false,
                schedule: undefined,
                nextRunDates: [],
                submitting: false,
            };
        },
        computed: {
            startEndDate: {
                get() {
                    return {dateStart: this.schedule.dateStart, dateEnd: this.schedule.dateEnd};
                },
                set(dates) {
                    this.$set(this.schedule, 'dateStart', dates.dateStart);
                    this.$set(this.schedule, 'dateEnd', dates.dateEnd);
                }
            },
            scheduleAction: {
                get() {
                    return {id: this.schedule.actionId, param: this.schedule.actionParam};
                },
                set(action) {
                    this.$set(this.schedule, 'actionId', action.id);
                    this.$set(this.schedule, 'actionParam', action.param);
                }
            },
            canSetRetry() {
                return this.schedule.subject
                    && this.schedule.subject.subjectType == 'channel'
                    && [20, 30].indexOf(this.schedule.subject.functionId) === -1;
            },
        },
        mounted() {
            if (this.id) {
                this.error = false;
                this.$http.get('schedules/' + this.id, {params: {include: 'subject'}, skipErrorHandler: [403, 404]})
                    .then(({body}) => this.schedule = body)
                    .catch(response => this.error = response.status);
            } else {
                this.schedule = {
                    mode: 'once',
                    dateStart: moment().format(),
                    retry: true,
                };
                const subjectForNewSchedule = AppState.shiftTask('scheduleCreate');
                if (subjectForNewSchedule) {
                    this.schedule.subject = subjectForNewSchedule;
                }
            }
        },
        methods: {
            submit(enableIfDisabled) {
                this.submitting = true;
                let promise;
                if (this.schedule.id) {
                    promise = Vue.http.put(`schedules/${this.schedule.id}` + (enableIfDisabled ? '?enable=true' : ''), this.schedule);
                } else {
                    promise = Vue.http.post('schedules?include=subject,closestExecutions', this.schedule);
                }
                promise
                    .then(({body: schedule}) => this.$emit('update', schedule) && schedule)
                    .then(schedule => this.$router.push({name: 'schedule', params: {id: schedule.id}}))
                    .finally(() => this.submitting = false);
            },
            filterOutNotSchedulableSubjects(subject) {
                if (subject.function.possibleActions.length === 0) {
                    return false;
                }
                if (subject.subjectType === 'channelGroup'
                    && ['CONTROLLINGTHEGATE', 'CONTROLLINGTHEGARAGEDOOR'].indexOf(subject.function.name) !== -1) {
                    return false;
                }
                return true;
            },
            possibleActionFilter(possibleAction) {
                return possibleAction.name != 'OPEN_CLOSE' && possibleAction.name != 'TOGGLE';
            },
        }
    };
</script>
