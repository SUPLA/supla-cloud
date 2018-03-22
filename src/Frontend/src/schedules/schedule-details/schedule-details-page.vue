<template>
    <div>
        <loading-cover :loading="!schedule">
            <div class="container"
                v-if="schedule">
                <pending-changes-page :header="$t('Schedule') + ' ID' + schedule.id"
                    @cancel="cancelChanges()"
                    @save="saveChanges()">
                    <div class="btn-toolbar"
                        slot="buttons">
                        <a class="btn btn-danger"
                            @click="$emit('delete')">
                            {{ $t('Delete') }}
                        </a>
                        <a class="btn btn-default"
                            @click="$emit('delete')">
                            {{ $t('Edit') }}
                        </a>
                    </div>
                    <div class="row hidden-xs">
                        <div class="col-xs-12">
                            <dots-route></dots-route>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-4">
                            <h3 class="text-center">{{ $t('Details') }}</h3>
                            <div class="hover-editable text-left">
                                <dl>
                                    <dd>{{ $t('Caption') }}</dd>
                                    <dt>
                                        <input type="text"
                                            class="form-control text-center"
                                            @change="updateChannel()"
                                            v-model="schedule.caption">
                                    </dt>
                                    <dd>{{ $t('Enabled') }}</dd>
                                    <dt>
                                        <toggler v-model="schedule.enabled"
                                            @input="updateChannel()"></toggler>
                                    </dt>
                                    <dd>{{ $t('Retry when fail') }}</dd>
                                    <dt>
                                        <toggler v-model="schedule.retry"
                                            @input="updateChannel()"></toggler>
                                    </dt>
                                    <dd>{{ $t('Action') }}</dd>
                                    <dt>{{ $t(schedule.action.caption) }}</dt>
                                    <dd>{{ $t('Mode') }}</dd>
                                    <dt>{{ $t(schedule.mode.value) }}</dt>
                                    <dd>{{ $t('Start date') }}</dd>
                                    <dt>{{ schedule.dateStart | moment('LLL') }}</dt>
                                </dl>
                                <dl v-if="schedule.dateEnd">
                                    <dd>{{ $t('End date') }}</dd>
                                    <dt>{{ schedule.dateEnd | moment('LLL') }}</dt>
                                </dl>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <h3 class="text-center">{{ $t('Channel') }}</h3>
                            <channel-tile :model="schedule.channel"></channel-tile>
                        </div>
                        <div class="col-sm-4">
                            <h3 class="text-center">{{ $t('Executions') }}</h3>
                            <schedule-executions-display :schedule="schedule"></schedule-executions-display>
                        </div>
                    </div>
                </pending-changes-page>
            </div>
        </loading-cover>
    </div>
</template>

<script type="text/babel">
    import DotsRoute from "../../common/gui/dots-route";
    import ChannelTile from "../../channels/channel-tile";
    import Toggler from "../../common/gui/toggler";
    import PendingChangesPage from "../../common/pages/pending-changes-page";
    import ScheduleExecutionsDisplay from "./schedule-executions-display";

    export default {
        components: {
            ScheduleExecutionsDisplay,
            PendingChangesPage,
            Toggler,
            ChannelTile,
            DotsRoute
        },
        props: ['scheduleId'],
        data() {
            return {
                schedule: undefined
            };
        },
        mounted() {
            let endpoint = `schedules/${this.scheduleId}?include=channel,iodevice,location`;
            this.$http.get(endpoint).then(({body}) => {
                this.schedule = body;
            });
        },
        methods: {}
    };
</script>
