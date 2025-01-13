<template>
    <page-container :error="error">
        <loading-cover :loading="!schedule || loading"
            v-if="id !== 'new'">
            <div class="container"
                v-if="schedule">
                <pending-changes-page
                    :header="scheduleCaption"
                    @cancel="cancelChanges()"
                    @save="saveChanges()"
                    :is-pending="hasPendingChanges">
                    <template #buttons>
                        <div class="btn-toolbar">
                            <router-link :to="{name: 'schedule.edit', params: {id: schedule.id}}"
                                class="btn btn-default">
                                {{ $t('Edit') }}
                            </router-link>
                            <a class="btn btn-danger"
                                @click="deleteConfirm = true">
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
                                            <input type="text"
                                                class="form-control text-center"
                                                @keydown="updateSchedule()"
                                                v-model="schedule.caption">
                                        </dt>
                                        <dd>{{ $t('Enabled') }}</dd>
                                        <dt>
                                            <toggler v-model="schedule.enabled"
                                                @input="updateSchedule()"></toggler>
                                        </dt>
                                    </dl>
                                    <dl v-if="!retryOptionDisabled">
                                        <dd>{{ $t('Retry on failure') }}</dd>
                                        <dt>
                                            <toggler v-model="schedule.retry"
                                                @input="updateSchedule()"></toggler>
                                        </dt>
                                    </dl>
                                    <dl>
                                        <dd>{{ $t('Mode') }}</dd>
                                        <dt>{{ $t(`scheduleMode_${schedule.mode}`) }}</dt>
                                        <dd>{{ $t('Start date') }}</dd>
                                        <dt>{{ schedule.dateStart | formatDateTimeLong }}</dt>
                                    </dl>
                                    <dl v-if="schedule.dateEnd">
                                        <dd>{{ $t('End date') }}</dd>
                                        <dt>{{ schedule.dateEnd | formatDateTimeLong }}</dt>
                                    </dl>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="details-page-block">
                                <h3 class="text-center">{{ $t('actionableSubjectType_' + schedule.subjectType) }}</h3>
                                <channel-tile :model="schedule.subject"
                                    v-if="schedule.subjectType == 'channel'"></channel-tile>
                                <channel-group-tile :model="schedule.subject"
                                    v-if="schedule.subjectType == 'channelGroup'"></channel-group-tile>
                                <scene-tile :model="schedule.subject"
                                    v-if="schedule.subjectType == 'scene'"></scene-tile>
                                <div class="mt-3"
                                    v-if="scheduleActionWarning">
                                    <div class="alert alert-warning text-center">
                                        {{ scheduleActionWarning }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="details-page-block">
                                <h3 class="text-center">{{ $t('Executions') }}</h3>
                                <schedule-executions-display :schedule="schedule"
                                    v-if="!loading"></schedule-executions-display>
                                <h4 class="text-center"
                                    v-if="!schedule.enabled">{{ $t('No future executions - schedule disabled') }}</h4>
                            </div>
                        </div>
                    </div>
                </pending-changes-page>
            </div>
            <modal-confirm v-if="deleteConfirm"
                class="modal-warning"
                @confirm="deleteSchedule()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure you want to delete this schedule?')"
                :loading="loading">
            </modal-confirm>
        </loading-cover>
        <schedule-form v-else
            @update="$emit('add', $event)"></schedule-form>
    </page-container>
</template>

<script>
    import ChannelTile from "../../channels/channel-tile";
    import ChannelGroupTile from "../../channel-groups/channel-group-tile";
    import SceneTile from "../../scenes/scene-tile";
    import Toggler from "../../common/gui/toggler";
    import PendingChangesPage from "../../common/pages/pending-changes-page";
    import ScheduleExecutionsDisplay from "./schedule-executions-display";
    import PageContainer from "../../common/pages/page-container";
    import ScheduleForm from "../schedule-form/schedule-form";
    import ActionableSubjectType from "../../common/enums/actionable-subject-type";
    import {extendObject} from "@/common/utils";

    export default {
        components: {
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
        mounted() {
            this.fetch();
        },
        methods: {
            fetch() {
                if (this.id !== 'new') {
                    this.loading = true;
                    this.error = false;
                    this.$http.get(`schedules/${this.id}?include=subject`, {skipErrorHandler: [403, 404]})
                        .then(({body}) => {
                            this.schedule = body;
                            this.hasPendingChanges = this.loading = false;
                        })
                        .catch(response => this.error = response.status);
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
                this.$http.put(`schedules/${this.id}`, this.schedule)
                    .then(response => extendObject(this.schedule, response.body))
                    .then(() => this.hasPendingChanges = false)
                    .finally(() => this.loading = false);
            },
            deleteSchedule() {
                this.loading = true;
                this.$http.delete(`schedules/${this.id}`).then(() => this.$router.push({name: 'schedules'}));
            }
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
            }
        }
    };
</script>
