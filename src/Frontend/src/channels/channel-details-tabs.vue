<template>
    <div>
        <div class="container"
            v-if="availableTabs.length">
            <div class="form-group">
                <ul class="nav nav-tabs">
                    <li :class="currentTab == tabDefinition.id ? 'active' : ''"
                        v-for="tabDefinition in availableTabs">
                        <a @click="changeTab(tabDefinition.id)">
                            {{ $t(tabDefinition.header) }}
                            <span v-if="tabDefinition.count !== undefined">({{ tabDefinition.count }})</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div v-if="currentTab == 'schedules'">
            <schedule-list-page :subject-id="channel.id"
                subject-type="channel"></schedule-list-page>
        </div>
        <div v-if="currentTab == 'directLinks'">
            <direct-links-list :subject="channel"></direct-links-list>
        </div>
        <div v-if="currentTab == 'measurementsHistory'"
            class="text-center">

            <div class="button-container ">
                <a :href="`/api/channels/${channel.id}/measurement-logs-csv?` | withDownloadAccessToken"
                    class="btn btn-default">{{ $t('Download the history of measurement') }}</a>

                <button @click="deleteConfirm = true"
                    type="button"
                    class="btn btn-red">
                    <i class="pe-7s-trash"></i>
                    {{ $t('Delete measurement history') }}
                </button>
            </div>

            <modal-confirm v-if="deleteConfirm"
                class="modal-warning"
                @confirm="deleteMeasurements()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure you want to delete the entire measurement history saved for this channel?')">
            </modal-confirm>
        </div>
    </div>
</template>

<script>
    import ScheduleListPage from "../schedules/schedule-list/schedule-list-page";
    import {successNotification} from "../common/notifier";
    import DirectLinksList from "../direct-links/direct-links-list";

    export default {
        props: ['channel'],
        components: {DirectLinksList, ScheduleListPage},
        data() {
            return {
                currentTab: '',
                deleteConfirm: false,
                availableTabs: []
            };
        },
        methods: {
            deleteMeasurements() {
                this.deleteConfirm = false;
                this.$http.delete('channels/' + this.channel.id + '/measurement-logs')
                    .then(() => successNotification(this.$t('Success'), this.$t('The measurement history has been deleted.')));
            },
            changeTab(id) {
                this.currentTab = id;
                this.$router.push({query: {tab: id}});
            }
        },
        mounted() {
            if (this.channel.function.possibleActions.length) {
                this.availableTabs.push({
                    id: 'schedules',
                    header: 'Schedules', // i18n
                    count: this.channel.relationsCount.schedules,
                });
            }
            this.availableTabs.push({
                id: 'directLinks',
                header: 'Direct links', // i18n
                count: this.channel.relationsCount.directLinks,
            });
            var supporterFunctions = ['THERMOMETER',
                'HUMIDITY',
                'HUMIDITYANDTEMPERATURE',
                'ELECTRICITYMETER',
                'GASMETER',
                'WATERMETER',
                'THERMOSTAT',
                'THERMOSTATHEATPOLHOMEPLUS'];

            if (supporterFunctions.indexOf(this.channel.function.name) >= 0) {
                this.availableTabs.push({
                    id: 'measurementsHistory',
                    header: 'History of measurements', // i18n
                });
            }
            const currentTab = this.availableTabs.filter(tab => tab.id == this.$route.query.tab)[0];
            this.currentTab = currentTab ? currentTab.id : this.availableTabs[0].id;
        },
    };
</script>
