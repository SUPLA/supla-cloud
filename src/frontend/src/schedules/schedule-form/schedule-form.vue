<template>
  <page-container :error="error" class="container">
    <BreadcrumbList v-if="schedule" :current="$t('Edit')">
      <RouterLink :to="{name: 'schedules'}">{{ $t('Schedules') }}</RouterLink>
      <RouterLink :to="{name: 'schedule', params: {id: schedule.id}}" v-if="schedule.id">{{ $t('Schedule') + ' ID' + id }}</RouterLink>
    </BreadcrumbList>
    <loading-cover :loading="!schedule || submitting">
      <pending-changes-page v-if="schedule" :header="schedule.id ? $t('Schedule') + ' ID' + id : $t('Create New Schedule')">
        <template #buttons>
          <div class="btn-toolbar">
            <router-link v-if="schedule.id" :to="{name: 'schedule', params: {id: schedule.id}}" class="btn btn-grey">
              <i class="pe-7s-back"></i>
              {{ $t('Cancel') }}
            </router-link>
            <div v-tooltip="!nextRunDates.length ? $t('Cannot calculate when to run the schedule - incorrect configuration?') : ''" class="btn-group">
              <button class="btn btn-white" type="submit" :disabled="!nextRunDates.length || nextRunDates.fetching" @click="submit()">
                <i class="pe-7s-diskette"></i>
                {{ $t('Save') }}
              </button>
            </div>
            <button
              v-if="schedule.id && schedule.enabled === false"
              class="btn btn-green"
              type="submit"
              :disabled="!nextRunDates.length || nextRunDates.fetching"
              @click="submit(true)"
            >
              <i class="pe-7s-diskette"></i>
              {{ $t('Save and enable') }}
            </button>
          </div>
        </template>
        <div class="row">
          <div class="col-sm-12">
            <div class="form-group">
              <label>{{ $t('Name') }}</label>
              <input v-model="schedule.caption" type="text" class="form-control" />
            </div>
            <div class="form-group">
              <label>{{ $t('Schedule mode') }}</label>
              <div class="clearfix"></div>
              <schedule-mode-chooser v-model="schedule.mode" @input="modeChanged($event)"></schedule-mode-chooser>
            </div>
          </div>
        </div>
        <div v-show="schedule.mode" class="row">
          <div class="col-md-6">
            <div class="well">
              <div>
                <div class="form-group">
                  <subject-dropdown
                    v-model="schedule.subject"
                    disable-schedules
                    disable-notifications
                    channels-dropdown-params="io=output&hasFunction=1"
                    :filter="filterOutNotSchedulableSubjects"
                  ></subject-dropdown>
                </div>
              </div>
              <div v-if="schedule.mode !== 'once'">
                <date-range-picker v-model="startEndDate"></date-range-picker>
              </div>
              <next-run-dates-preview v-if="schedule.subject" v-model="nextRunDates" :schedule="schedule"></next-run-dates-preview>
              <toggler v-if="canSetRetry" v-model="schedule.retry" :label="$t('Retry on failure')"></toggler>
            </div>
          </div>
          <div class="col-md-6">
            <div class="well">
              <div v-if="!schedule.subject">
                {{ $t('Please choose the schedule subject first.') }}
              </div>
              <div v-else-if="schedule.mode != 'daily' && schedule.mode != 'crontab'">
                <div class="form-group">
                  <schedule-form-mode-once v-if="schedule.mode == 'once'" v-model="schedule.config[0].crontab"></schedule-form-mode-once>
                  <schedule-form-mode-minutely v-if="schedule.mode == 'minutely'" v-model="schedule.config[0].crontab"></schedule-form-mode-minutely>
                </div>
                <channel-action-chooser
                  v-model="schedule.config[0].action"
                  :subject="schedule.subject"
                  :possible-action-filter="possibleActionFilter"
                ></channel-action-chooser>
              </div>
              <div v-else>
                <schedule-form-mode-daily v-if="schedule.mode === 'daily'" v-model="schedule.config" :subject="schedule.subject"></schedule-form-mode-daily>
                <schedule-form-mode-crontab
                  v-if="schedule.mode === 'crontab'"
                  v-model="schedule.config"
                  :subject="schedule.subject"
                ></schedule-form-mode-crontab>
              </div>
            </div>
          </div>
        </div>
      </pending-changes-page>
    </loading-cover>
  </page-container>
</template>

<script>
  import ScheduleModeChooser from './schedule-mode-chooser.vue';
  import ScheduleFormModeOnce from './modes/schedule-form-mode-once.vue';
  import ScheduleFormModeMinutely from './modes/schedule-form-mode-minutely.vue';
  import ScheduleFormModeDaily from './modes/schedule-form-mode-daily.vue';
  import ScheduleFormModeCrontab from './modes/schedule-form-mode-crontab.vue';
  import NextRunDatesPreview from './next-run-dates-preview.vue';
  import Toggler from '../../common/gui/toggler.vue';
  import PageContainer from '../../common/pages/page-container.vue';
  import PendingChangesPage from '../../common/pages/pending-changes-page.vue';
  import AppState from '../../router/app-state';
  import SubjectDropdown from '../../devices/subject-dropdown.vue';
  import ChannelActionChooser from '../../channels/action/channel-action-chooser.vue';
  import {cloneDeep} from 'lodash';
  import ActionableSubjectType from '../../common/enums/actionable-subject-type';
  import ChannelFunctionAction from '../../common/enums/channel-function-action';
  import DateRangePicker from '@/activity/date-range-picker.vue';
  import {DateTime} from 'luxon';
  import ChannelFunction from '@/common/enums/channel-function';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {api} from '@/api/api.js';
  import BreadcrumbList from '@/common/gui/breadcrumb/BreadcrumbList.vue';

  export default {
    components: {
      BreadcrumbList,
      LoadingCover,
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
    props: ['id'],
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
        },
      },
      canSetRetry() {
        return (
          this.schedule.subject &&
          this.schedule.subject.ownSubjectType === ActionableSubjectType.CHANNEL &&
          [20, 30].indexOf(this.schedule.subject.functionId) === -1
        );
      },
    },
    mounted() {
      if (this.id) {
        this.error = false;
        api
          .get(`schedules/${this.id}?include=subject`, {skipErrorHandler: [403, 404]})
          .then(({body}) => {
            this.schedule = body;
            this.configs[body.mode] = body.config;
            this.schedule.config = this.configs[body.mode];
          })
          .catch((response) => (this.error = response.status));
      } else {
        this.schedule = {
          mode: 'daily',
          dateStart: DateTime.now().startOf('second').toISO({suppressMilliseconds: true}),
          retry: true,
          config: this.configs.daily,
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
        const toSend = {...this.schedule};
        toSend.subjectId = toSend.subject.id;
        toSend.subjectType = toSend.subject.ownSubjectType;
        delete toSend.subject;
        if (this.schedule.id) {
          promise = api.put(`schedules/${this.schedule.id}` + (enableIfDisabled ? '?enable=true' : ''), toSend);
        } else {
          promise = api.post('schedules?include=subject,closestExecutions', toSend);
        }
        promise
          .then(({body: schedule}) => this.$emit('update', schedule) && schedule)
          .then((schedule) => this.$router.push({name: 'schedule', params: {id: schedule.id}}))
          .finally(() => (this.submitting = false));
      },
      filterOutNotSchedulableSubjects(subject) {
        if (subject.possibleActions.length === 0) {
          return false;
        }
        if (subject.ownSubjectType === 'channelGroup' && ['CONTROLLINGTHEGATE', 'CONTROLLINGTHEGARAGEDOOR'].indexOf(subject.function.name) !== -1) {
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
      modeChanged(targetMode) {
        this.configs[this.schedule.mode] = this.schedule.config;
        if (targetMode === 'crontab' && !this.configs.crontab.length) {
          this.configs.crontab = cloneDeep(this.schedule.config);
        }
        this.schedule.mode = targetMode;
        this.schedule.config = this.configs[targetMode];
      },
    },
  };
</script>

<style lang="scss">
  @use '../../styles/variables' as *;

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
