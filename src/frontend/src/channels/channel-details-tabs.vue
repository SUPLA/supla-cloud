<template>
    <div class="channel-details-tabs">
        <div class="container"
            v-if="availableTabs.length">
            <div class="form-group">
                <ul class="nav nav-tabs">
                    <li :class="currentTab == tabDefinition.id ? 'active' : ''"
                        v-for="tabDefinition in availableTabs"
                        :key="tabDefinition.id">
                        <a @click="changeTab(tabDefinition.id)">
                            {{ $t(tabDefinition.header) }}
                            <span v-if="tabDefinition.count !== undefined">({{ tabDefinition.count }})</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div v-if="currentTab == 'actions'">
            <div class="container">
                <channel-action-executor :subject="channel"></channel-action-executor>
            </div>
        </div>
        <div v-if="currentTab == 'schedules'">
            <schedule-list-page :subject="channel"></schedule-list-page>
        </div>
        <div v-if="currentTab == 'directLinks'">
            <direct-links-list :subject="channel"></direct-links-list>
        </div>
        <div v-if="currentTab == 'measurementsHistory'">
            <channel-measurements-history :channel="channel"></channel-measurements-history>
        </div>
    </div>
</template>

<script>
    import ScheduleListPage from "../schedules/schedule-list/schedule-list-page";
    import DirectLinksList from "../direct-links/direct-links-list";
    import ChannelActionExecutor from "./action/channel-action-executor";
    import ChannelMeasurementsHistory from "./channel-measurements-history";

    export default {
        props: ['channel'],
        components: {ChannelMeasurementsHistory, ChannelActionExecutor, DirectLinksList, ScheduleListPage},
        data() {
            return {
                currentTab: '',
                availableTabs: []
            };
        },
        methods: {
            changeTab(id) {
                this.currentTab = id;
                this.$router.push({query: {tab: id}});
            }
        },
        mounted() {
            if (this.channel.function.possibleActions && this.channel.function.possibleActions.length) {
                const noApiActionFunctions = ['VALVEPERCENTAGE'];
                if (!noApiActionFunctions.includes(this.channel.function.name)) {
                    this.availableTabs.push({
                        id: 'actions',
                        header: 'Actions', // i18n
                    });
                }
                this.availableTabs.push({
                    id: 'schedules',
                    header: 'Schedules', // i18n
                    count: this.channel.relationsCount.schedules,
                });
            }
            if (this.channel.function.id > 0) {
                this.availableTabs.push({
                    id: 'directLinks',
                    header: 'Direct links', // i18n
                    count: this.channel.relationsCount.directLinks,
                });
            }
            const measurementsHistoryFunctions = [
                'THERMOMETER',
                'HUMIDITY',
                'HUMIDITYANDTEMPERATURE',
                'ELECTRICITYMETER',
                'GASMETER',
                'WATERMETER',
                'HEATMETER',
                'THERMOSTAT',
                'THERMOSTATHEATPOLHOMEPLUS'
            ];
            if (measurementsHistoryFunctions.includes(this.channel.function.name)) {
                this.availableTabs.push({
                    id: 'measurementsHistory',
                    header: 'History of measurements', // i18n
                });
            }
            const currentTab = this.availableTabs.filter(tab => tab.id == this.$route.query.tab)[0];
            this.currentTab = currentTab ? currentTab.id : (this.availableTabs[0] ? this.availableTabs[0].id : undefined);
        },
    };
</script>
