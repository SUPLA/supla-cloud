<template>
    <div>
        <div class="container"
            v-if="availableTabs.length">
            <div class="form-group">
                <ul class="nav nav-tabs">
                    <li :class="currentTab == tabDefinition.id ? 'active' : ''"
                        v-for="tabDefinition in availableTabs">
                        <a @click="currentTab = tabDefinition.id">{{ $t(tabDefinition.header) }}</a>
                    </li>
                </ul>
            </div>
        </div>
        <div v-if="currentTab == 'schedules'">
            <schedule-list-page :channel-id="channel.id"></schedule-list-page>
        </div>
        <div v-if="currentTab == 'measurementsHistory'"
            class="text-center">
            <a :href="'/web-api/channels/' + channel.id + '/measurement-logs-csv' | withBaseUrl"
                class="btn btn-default">{{ $t('Download the history of measurement') }}</a>
        </div>
    </div>
</template>

<script>
    import ScheduleListPage from "../schedules/schedule-list/schedule-list-page";

    export default {
        props: ['channel'],
        components: {ScheduleListPage},
        data() {
            return {
                currentTab: '',
                availableTabs: []
            };
        },
        mounted() {
            if (this.channel.function.possibleActions.length) {
                this.availableTabs.push({id: 'schedules', header: 'Schedules'});
            }
            if (['THERMOMETER', 'HUMIDITY', 'HUMIDITYANDTEMPERATURE'].indexOf(this.channel.function.name) >= 0) {
                this.availableTabs.push({id: 'measurementsHistory', header: 'History of measurements'});
            }
            if (this.availableTabs.length) {
                this.currentTab = this.availableTabs[0].id;
            }
        },
    };
</script>
