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
        <div v-if="currentTab == 'actionTriggers'">
            <div class="container">
                <channel-action-triggers :channel="channel"></channel-action-triggers>
            </div>
        </div>
        <div v-if="currentTab == 'schedules'">
            <schedules-list :subject="channel"></schedules-list>
        </div>
        <div v-if="currentTab == 'channelGroups'">
            <channel-groups-list :channel="channel"></channel-groups-list>
        </div>
        <div v-if="currentTab == 'scenes'">
            <scenes-list :subject="channel"></scenes-list>
        </div>
        <div v-if="currentTab == 'directLinks'">
            <direct-links-list :subject="channel"></direct-links-list>
        </div>
        <div v-if="currentTab == 'measurementsHistory'">
            <channel-measurements-history :channel="channel"></channel-measurements-history>
        </div>
        <div v-if="currentTab == 'voltageHistory'">
            <channel-voltage-history :channel="channel"/>
        </div>
    </div>
</template>

<script>
    import SchedulesList from "../schedules/schedule-list/schedules-list";
    import DirectLinksList from "../direct-links/direct-links-list";
    import ChannelGroupsList from "../channel-groups/channel-groups-list";
    import ScenesList from "../scenes/scenes-list";
    import ChannelActionTriggers from "@/channels/action-trigger/channel-action-triggers";
    import ChannelFunction from "../common/enums/channel-function";
    import ChannelVoltageHistory from "./channel-voltage-history";

    export default {
        props: ['channel'],
        components: {
            ChannelMeasurementsHistory: () => import(/*webpackChunkName:"measurement-history"*/"./history/channel-measurements-history.vue"),
            ChannelVoltageHistory, ChannelActionTriggers, ScenesList, ChannelGroupsList, DirectLinksList, SchedulesList
        },
        data() {
            return {
                currentTab: '',
                availableTabs: []
            };
        },
        methods: {
            changeTab(id) {
                const currentTab = this.availableTabs.filter(tab => tab.id === id)[0];
                this.currentTab = currentTab ? currentTab.id : (this.availableTabs[0] ? this.availableTabs[0].id : undefined);
                if (this.$route.query.tab !== this.currentTab) {
                    this.$router.push({query: {tab: id}});
                }
            }
        },
        mounted() {
            if (this.channel.possibleActions?.length) {
                if (this.channel.actionTriggersIds?.length) {
                    this.availableTabs.push({
                        id: 'actionTriggers',
                        header: 'Action triggers', // i18n
                        count: this.channel.actionTriggersIds.length,
                    });
                }
                this.availableTabs.push({
                    id: 'schedules',
                    header: 'Schedules', // i18n
                    count: this.channel.relationsCount.schedules,
                });
                this.availableTabs.push({
                    id: 'channelGroups',
                    header: 'Channel groups', // i18n
                    count: this.channel.relationsCount.channelGroups,
                });
                this.availableTabs.push({
                    id: 'scenes',
                    header: 'Scenes', // i18n
                    count: this.channel.relationsCount.scenes,
                });
            }
            if (this.channel.function.id > 0 && !['ACTION_TRIGGER'].includes(this.channel.function.name)) {
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
                'IC_ELECTRICITYMETER',
                'IC_GASMETER',
                'IC_WATERMETER',
                'IC_HEATMETER',
                'THERMOSTAT',
                'THERMOSTATHEATPOLHOMEPLUS'
            ];
            if (measurementsHistoryFunctions.includes(this.channel.function.name)) {
                this.availableTabs.push({
                    id: 'measurementsHistory',
                    header: 'History of measurements', // i18n
                });
            }
            if (this.channel.function.id === ChannelFunction.ELECTRICITYMETER) {
                this.availableTabs.push({
                    id: 'voltageHistory',
                    header: 'Voltage aberrations', // i18n
                });
            }
            this.changeTab(this.$route.query.tab);
        },
        watch: {
            '$route.query.tab'() {
                this.changeTab(this.$route.query.tab);
            }
        },
    };
</script>
