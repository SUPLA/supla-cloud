<template>
    <page-container :error="error"
        class="container">
        <loading-cover :loading="!schedule || submitting">
            <pending-changes-page :header="schedule.id ? $t('Schedule') + ' ID' + id : $t('Create New Schedule')"
                v-if="schedule">
                <template #buttons>
                    <div class="btn-toolbar">
                        <router-link :to="{name: 'schedule', params: {id: schedule.id}}"
                            class="btn btn-grey"
                            v-if="schedule.id">
                            <i class="pe-7s-back"></i>
                            {{ $t('Cancel') }}
                        </router-link>
                        <div class="btn-group"
                            v-tooltip="!nextRunDates.length ? $t('Cannot calculate when to run the schedule - incorrect configuration?') : ''">
                            <button class="btn btn-white"
                                type="submit"
                                :disabled="!nextRunDates.length || nextRunDates.fetching"
                                @click="submit()">
                                <i class="pe-7s-diskette"></i>
                                {{ $t('Save') }}
                            </button>
                        </div>
                        <button class="btn btn-green"
                            type="submit"
                            v-if="schedule.id && schedule.enabled === false"
                            :disabled="!nextRunDates.length || nextRunDates.fetching"
                            @click="submit(true)">
                            <i class="pe-7s-diskette"></i>
                            {{ $t('Save and enable') }}
                        </button>
                    </div>
                </template>
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
                            <schedule-mode-chooser v-model="schedule.mode"
                                @beforeChange="beforeModeChange($event)"
                                @input="modeChanged()"></schedule-mode-chooser>
                        </div>
                    </div>
                </div>
                <div class="row"
                    v-show="schedule.mode">
                    <div class="col-md-6">
                        <div class="well">
                            <div>
                                <div class="form-group">
                                    <subject-dropdown v-model="schedule.subject"
                                        disable-schedules
                                        disable-notifications
                                        channels-dropdown-params="io=output&hasFunction=1"
                                        :filter="filterOutNotSchedulableSubjects"></subject-dropdown>
                                </div>
                            </div>
                            <div v-if="schedule.mode !== 'once'">
                                <date-range-picker v-model="startEndDate"></date-range-picker>
                            </div>
                            <next-run-dates-preview v-if="schedule.subject"
                                v-model="nextRunDates"
                                :schedule="schedule"></next-run-dates-preview>
                            <toggler v-model="schedule.retry"
                                v-if="canSetRetry"
                                label="Retry on failure"></toggler>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="well">
                            <div v-if="!schedule.subject">
                                {{ $t('Please choose the schedule subject first.') }}
                            </div>
                            <div v-else-if="schedule.mode != 'daily' && schedule.mode != 'crontab'">
                                <div class="form-group">
                                    <schedule-form-mode-once v-if="schedule.mode == 'once'"
                                        v-model="schedule.config[0].crontab"></schedule-form-mode-once>
                                    <schedule-form-mode-minutely v-if="schedule.mode == 'minutely'"
                                        v-model="schedule.config[0].crontab"></schedule-form-mode-minutely>
                                </div>
                                <channel-action-chooser :subject="schedule.subject"
                                    v-model="schedule.config[0].action"
                                    :possible-action-filter="possibleActionFilter"></channel-action-chooser>
                            </div>
                            <div v-else>
                                <schedule-form-mode-daily v-if="schedule.mode === 'daily'"
                                    v-model="schedule.config"
                                    :subject="schedule.subject"></schedule-form-mode-daily>
                                <schedule-form-mode-crontab v-if="schedule.mode === 'crontab'"
                                    v-model="schedule.config"
                                    :subject="schedule.subject"></schedule-form-mode-crontab>
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
    import ScheduleFormModeDaily from "./modes/schedule-form-mode-daily.vue";
    import ScheduleFormModeCrontab from "./modes/schedule-form-mode-crontab.vue";
    import NextRunDatesPreview from "./next-run-dates-preview.vue";
    import Toggler from "../../common/gui/toggler";
    import PageContainer from "../../common/pages/page-container";
    import PendingChangesPage from "../../common/pages/pending-changes-page";
    import AppState from "../../router/app-state";
    import SubjectDropdown from "../../devices/subject-dropdown";
    import ChannelActionChooser from "../../channels/action/channel-action-chooser";
    import Vue from "vue";
    import {cloneDeep} from "lodash";
    import ActionableSubjectType from "../../common/enums/actionable-subject-type";
    import ChannelFunctionAction from "../../common/enums/channel-function-action";
    import DateRangePicker from "../../direct-links/date-range-picker";
    import {DateTime} from "luxon";
    import ChannelFunction from "@/common/enums/channel-function";

    export default {
        props: ['id'],
        components: {
            DateRangePicker,
            ChannelActionChooser,
            SubjectDropdown,
            PendingChangesPage,
            PageContainer,
            ScheduleModeChooser,
            ScheduleFormModeOnce,
            ScheduleFormModeMinutely,
            ScheduleFormModeDaily,
            ScheduleFormModeCrontab,
            NextRunDatesPreview,
            Toggler,
        },
        data() {
            return {
                error: false,
                schedule: undefined,
                nextRunDates: [],
                submitting: false,
                configs: {
                    once: [{crontab: undefined, action: {}}],
                    minutely: [{crontab: undefined, action: {}}],
                    daily: [],
                    crontab: [],
                },
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
            canSetRetry() {
                return this.schedule.subject
                    && this.schedule.subject.ownSubjectType === ActionableSubjectType.CHANNEL
                    && [20, 30].indexOf(this.schedule.subject.functionId) === -1;
            },
        },
        mounted() {
            if (this.id) {
                this.error = false;
                this.$http.get('schedules/' + this.id, {params: {include: 'subject'}, skipErrorHandler: [403, 404]})
                    .then(({body}) => {
                        this.schedule = body;
                        this.configs[body.mode] = body.config;
                        this.schedule.config = this.configs[body.mode];
                    })
                    .catch(response => this.error = response.status);
            } else {
                this.schedule = {
                    mode: 'daily',
                    dateStart: DateTime.now().startOf('second').toISO({suppressMilliseconds: true}),
                    retry: true,
                    config: this.configs.daily,
                };
                const subjectForNewSchedule = AppState.shiftTask('scheduleCreate');
                if (subjectForNewSchedule) {
                    this.$set(this.schedule, 'subject', subjectForNewSchedule);
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
                if (subject.possibleActions.length === 0) {
                    return false;
                }
                if (subject.ownSubjectType === 'channelGroup'
                    && ['CONTROLLINGTHEGATE', 'CONTROLLINGTHEGARAGEDOOR'].indexOf(subject.function.name) !== -1) {
                    return false;
                }
                const nonScheduleFunctions = [
                    ChannelFunction.HVAC_THERMOSTAT,
                    ChannelFunction.HVAC_DOMESTIC_HOT_WATER,
                    ChannelFunction.HVAC_THERMOSTAT_DIFFERENTIAL,
                    ChannelFunction.HVAC_THERMOSTAT_HEAT_COOL,
                    ChannelFunction.THERMOSTATHEATPOLHOMEPLUS,
                ];
                return !nonScheduleFunctions.includes(subject.functionId);

            },
            possibleActionFilter(possibleAction) {
                return ChannelFunctionAction.availableInSchedules(possibleAction.id);
            },
            beforeModeChange(targetMode) {
                this.configs[this.schedule.mode] = this.schedule.config;
                if (targetMode === 'crontab' && !this.configs.crontab.length) {
                    this.configs.crontab = cloneDeep(this.schedule.config);
                }
            },
            modeChanged() {
                this.schedule.config = this.configs[this.schedule.mode];
            },
        }
    };
</script>

<style lang="scss">
    @import '../../styles/variables';

    .schedule-action {
        border-bottom: 1px solid $supla-green;
        padding-bottom: 1em;
        margin-bottom: 1.3em;
        .schedule-action-row {
            display: flex;
            gap: 1em;
            .schedule-action-time-chooser {
                width: 33%;
            }
            .schedule-action-actions {
                flex: 1;
            }
            .remove-item-button {
                font-weight: bold;
                font-size: 1.3em;
                color: $supla-red;
            }
        }
    }
</style>
