<template>
    <div class="container"
        v-if="availableTabs.length">
        <div class="row">
            <div class="col-xs-12">
                <div class="form-group">
                    <ul class="nav nav-tabs">
                        <li :class="currentTab == tabDefinition.id ? 'active' : ''"
                            v-for="tabDefinition in availableTabs">
                            <a @click="currentTab = tabDefinition.id">{{ $t(tabDefinition.header) }}</a>
                        </li>
                    </ul>
                </div>
                <schedule-list v-if="currentTab == 'schedules'"
                    :channel-id="channel.id"></schedule-list>
                <div v-if="currentTab == 'measurementsHistory'"
                    class="text-center">
                    <a :href="'/channels/' + channel.id + '/csv' | withBaseUrl"
                        class="btn btn-default">{{ $t('Download the history of measurement') }}</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ScheduleList from "../schedules/schedule-list/schedule-list";

    export default {
        props: ['channel'],
        components: {
            ScheduleList,
        },
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
