<template>
  <page-container :error="error">
    <loading-cover v-if="id !== 'new'" :loading="!schedule || loading">
      <div v-if="schedule" class="container">
        <SubjectBreadcrumb :entity="schedule" :current="scheduleCaption">
          <RouterLink :to="{name: 'schedules'}">&laquo; {{ $t('Schedules') }}</RouterLink>
        </SubjectBreadcrumb>
        <pending-changes-page :header="scheduleCaption" :is-pending="hasPendingChanges" @cancel="cancelChanges()" @save="saveChanges()">
          <template #buttons>
            <div class="btn-toolbar">
              <router-link :to="{name: 'schedule.edit', params: {id: schedule.id}}" class="btn btn-default">
                {{ $t('Edit') }}
              </router-link>
              <a class="btn btn-danger" @click="deleteConfirm = true">
                {{ $t('Delete') }}
              </a>
            </div>
          </template>
          <div class="row">
            <div class="col-md-4 col-sm-6">
              <div class="details-page-block">
                <h3 class="text-center">{{ $t('Details') }}</h3>
                <div class="hover-editable text-left">
                  <dl>
                    <dd>{{ $t('Caption') }}</dd>
                    <dt>
                      <input v-model="schedule.caption" type="text" class="form-control text-center" @keydown="updateSchedule()" />
                    </dt>
                    <dd>{{ $t('Enabled') }}</dd>
                    <dt>
                      <toggler v-model="schedule.enabled" @update:model-value="updateSchedule()"></toggler>
                    </dt>
                  </dl>
                  <dl v-if="!retryOptionDisabled">
                    <dd>{{ $t('Retry on failure') }}</dd>
                    <dt>
                      <toggler v-model="schedule.retry" @update:model-value="updateSchedule()"></toggler>
                    </dt>
                  </dl>
                  <dl>
                    <dd>{{ $t('Mode') }}</dd>
                    <dt>{{ $t(`scheduleMode_${schedule.mode}`) }}</dt>
                    <dd>{{ $t('Start date') }}</dd>
                    <dt>{{ formatDateTimeLong(schedule.dateStart) }}</dt>
                  </dl>
                  <dl v-if="schedule.dateEnd">
                    <dd>{{ $t('End date') }}</dd>
                    <dt>{{ formatDateTimeLong(schedule.dateEnd) }}</dt>
                  </dl>
                </div>
              </div>
            </div>
            <div class="col-md-4 col-sm-6">
              <div class="details-page-block">
                <h3 class="text-center">{{ $t('actionableSubjectType_' + schedule.subjectType) }}</h3>
                <channel-tile v-if="schedule.subjectType == 'channel'" :model="schedule.subject"></channel-tile>
                <channel-group-tile v-if="schedule.subjectType == 'channelGroup'" :model="schedule.subject"></channel-group-tile>
                <scene-tile v-if="schedule.subjectType == 'scene'" :model="schedule.subject"></scene-tile>
                <div v-if="scheduleActionWarning" class="mt-3">
                  <div class="alert alert-warning text-center">
                    {{ scheduleActionWarning }}
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-4 col-sm-12">
              <div class="details-page-block">
                <h3 class="text-center">{{ $t('Executions') }}</h3>
                <schedule-executions-display v-if="!loading" :schedule="schedule"></schedule-executions-display>
                <h4 v-if="!schedule.enabled" class="text-center">{{ $t('No future executions - schedule disabled') }}</h4>
              </div>
            </div>
          </div>
        </pending-changes-page>
      </div>
      <modal-confirm
        v-if="deleteConfirm"
        class="modal-warning"
        :header="$t('Are you sure you want to delete this schedule?')"
        :loading="loading"
        @confirm="deleteSchedule()"
        @cancel="deleteConfirm = false"
      >
      </modal-confirm>
    </loading-cover>
    <schedule-form v-else @update="$emit('add', $event)"></schedule-form>
  </page-container>
</template>

<script>
  import ChannelTile from '../../channels/channel-tile.vue';
  import ChannelGroupTile from '../../channel-groups/channel-group-tile.vue';
  import SceneTile from '../../scenes/scene-tile.vue';
  import Toggler from '../../common/gui/toggler.vue';
  import PendingChangesPage from '../../common/pages/pending-changes-page.vue';
  import ScheduleExecutionsDisplay from './schedule-executions-display.vue';
  import PageContainer from '../../common/pages/page-container.vue';
  import ScheduleForm from '../schedule-form/schedule-form.vue';
  import ActionableSubjectType from '../../common/enums/actionable-subject-type';
  import {extendObject} from '@/common/utils';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import ModalConfirm from '@/common/modal-confirm.vue';
  import {api} from '@/api/api.js';
  import {formatDateTimeLong} from '@/common/filters-date.js';
  import SubjectBreadcrumb from '@/channels/subject-breadcrumb.vue';

  export default {
    components: {
      SubjectBreadcrumb,
      ModalConfirm,
      LoadingCover,
      ScheduleForm,
      PageContainer,
      ScheduleExecutionsDisplay,
      PendingChangesPage,
      Toggler,
      ChannelTile,
      SceneTile,
      ChannelGroupTile,
    },
    props: ['id'],
    data() {
      return {
        schedule: undefined,
        error: false,
        hasPendingChanges: false,
        loading: false,
        deleteConfirm: false,
      };
    },
    computed: {
      scheduleActionWarning() {
        if (this.schedule) {
          if (['CONTROLLINGTHEGARAGEDOOR', 'CONTROLLINGTHEGATE'].includes(this.schedule.subject.function.name)) {
            return this.$t('The gate sensor must function properly in order to execute the scheduled action.');
          } else if (['VALVEOPENCLOSE'].includes(this.schedule.subject.function.name)) {
            return this.$t('The valve will not be opened if it was closed manually or remotely, which might could been caused by flooding.');
          }
        }
        return undefined;
      },
      retryOptionDisabled() {
        return this.scheduleActionWarning || this.schedule.subjectType !== ActionableSubjectType.CHANNEL;
      },
      scheduleCaption() {
        return this.schedule.caption || `${this.$t('Schedule')} ${this.$t('ID')}${this.schedule.id}`;
      },
    },
    watch: {
      id() {
        this.fetch();
      },
    },
    mounted() {
      this.fetch();
    },
    methods: {
      formatDateTimeLong,
      fetch() {
        if (this.id !== 'new') {
          this.loading = true;
          this.error = false;
          api
            .get(`schedules/${this.id}?include=subject`, {skipErrorHandler: [403, 404]})
            .then(({body}) => {
              this.schedule = body;
              this.hasPendingChanges = this.loading = false;
            })
            .catch((response) => (this.error = response.status));
        }
      },
      cancelChanges() {
        this.fetch();
      },
      updateSchedule() {
        this.hasPendingChanges = true;
      },
      saveChanges() {
        this.loading = true;
        api
          .put(`schedules/${this.id}`, this.schedule)
          .then((response) => extendObject(this.schedule, response.body))
          .then(() => (this.hasPendingChanges = false))
          .finally(() => (this.loading = false));
      },
      deleteSchedule() {
        this.loading = true;
        api.delete_(`schedules/${this.id}`).then(() => this.$router.push({name: 'schedules'}));
      },
    },
  };
</script>
