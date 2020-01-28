<template>
    <page-container :error="error"
        class="container">
        <loading-cover :loading="!scheduleX || submitting">
            <pending-changes-page :header="scheduleX.id ? $t('Schedule') + ' ID' + id : $t('Create New Schedule')"
                v-if="scheduleX">
                <div slot="buttons">
                    <router-link :to="{name: 'schedule', params: {id: scheduleX.id}}"
                        class="btn btn-grey"
                        v-if="scheduleX.id">
                        <i class="pe-7s-back"></i>
                        {{ $t('Cancel') }}
                    </router-link>
                    <button class="btn btn-white"
                        :disabled="scheduleX.actionId == undefined || !nextRunDatesX.length || nextRunDatesX.fetching"
                        @click="submit()">
                        <i class="pe-7s-diskette"></i>
                        {{ $t('Save') }}
                    </button>
                    <button class="btn btn-green"
                        v-if="scheduleX.id && scheduleX.enabled === false"
                        :disabled="scheduleX.actionId == undefined || !nextRunDatesX.length || nextRunDatesX.fetching"
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
                                v-model="scheduleX.caption">
                            {{ scheduleX }}
                        </div>
                        <div class="form-group">
                            <label>{{ $t("Schedule mode") }}</label>
                            <div class="clearfix"></div>
                            <schedule-mode-chooser v-model="scheduleX.mode"></schedule-mode-chooser>
                        </div>
                    </div>
                </div>
                <div class="row"
                    v-show="scheduleX.mode">
                    <div class="col-md-6">
                        <div class="well">
                            <h3 class="no-margin-top">{{ $t('When') }}?</h3>
                            <div class="form-group">
                                <schedule-form-mode-once v-if="scheduleX.mode == 'once'"
                                    v-model="scheduleX.timeExpression"></schedule-form-mode-once>
                                <schedule-form-mode-minutely v-if="scheduleX.mode == 'minutely'"
                                    v-model="scheduleX.timeExpression"></schedule-form-mode-minutely>
                                <schedule-form-mode-hourly v-if="scheduleX.mode == 'hourly'"
                                    v-model="scheduleX.timeExpression"></schedule-form-mode-hourly>
                                <schedule-form-mode-daily v-if="scheduleX.mode == 'daily'"
                                    v-model="scheduleX.timeExpression"></schedule-form-mode-daily>
                            </div>
                            <div v-if="scheduleX.mode !== 'once'">
                                <schedule-form-start-end-date v-model="startEndDate"></schedule-form-start-end-date>
                            </div>
                            <next-run-dates-preview v-model="nextRunDatesX"
                                :schedule="scheduleX"></next-run-dates-preview>
                            <toggler v-model="scheduleX.retry"
                                v-if="canSetRetry"
                                label="Retry on failure"></toggler>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="well">
                            <h3 class="no-margin-top">{{ $t('Action') }}</h3>
                            <div class="form-group">
                                <label>{{ $t('Subject') }}</label>
                                <subject-dropdown v-model="scheduleX.subject"
                                    channels-dropdown-params="io=output&hasFunction=1"
                                    :filter="filterOutNotSchedulableSubjects"></subject-dropdown>
                            </div>
                            <div v-if="scheduleX.subject">
                                <channel-action-chooser :subject="scheduleX.subject"
                                    v-model="scheduleActionX"
                                    :possible-action-filter="possibleActionFilter"></channel-action-chooser>
                            </div>
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
                scheduleX: undefined,
                nextRunDatesX: [],
                submitting: false,
            };
        },
        computed: {
            startEndDate: {
                get() {
                    return {dateStart: this.scheduleX.startDate, dateEnd: this.scheduleX.endDate};
                },
                set(dates) {
                    this.$set(this.scheduleX, 'dateStart', dates.dateStart);
                    this.$set(this.scheduleX, 'dateEnd', dates.dateEnd);
                }
            },
            scheduleActionX: {
                get() {
                    return {id: this.scheduleX.actionId, param: this.scheduleX.actionParam};
                },
                set(action) {
                    this.$set(this.scheduleX, 'actionId', action.id);
                    this.$set(this.scheduleX, 'actionParam', action.param);
                }
            },
            canSetRetry() {
                return this.scheduleX.subject
                    && this.scheduleX.subject.subjectType == 'channel'
                    && [20, 30].indexOf(this.scheduleX.subject.functionId) === -1;
            },
        },
        mounted() {
            if (this.id) {
                this.error = false;
                this.$http.get('schedules/' + this.id, {params: {include: 'subject'}, skipErrorHandler: [403, 404]})
                    .then(({body}) => this.scheduleX = body)
                    .catch(response => this.error = response.status);
            } else {
                this.scheduleX = {
                    mode: 'once',
                    dateStart: moment().format(),
                    retry: true,
                };
                const subjectForNewSchedule = AppState.shiftTask('scheduleCreate');
                if (subjectForNewSchedule) {
                    this.scheduleX.subject = subjectForNewSchedule;
                }
            }
        },
        methods: {
            submit(enableIfDisabled) {
                let promise;
                if (this.scheduleX.id) {
                    promise = Vue.http.put(`schedules/${this.scheduleX.id}` + (enableIfDisabled ? '?enable=true' : ''), this.scheduleX);
                } else {
                    promise = Vue.http.post('schedules?include=subject,closestExecutions', this.scheduleX);
                }
                promise
                    .then(({body: schedule}) => this.$emit('update', schedule) && schedule)
                    .then(schedule => this.$router.push({name: 'schedule', params: {id: schedule.id}}));
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
