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
    </div>
</template>

<script>
    import SchedulesList from "../schedules/schedule-list/schedules-list";
    import DirectLinksList from "../direct-links/direct-links-list";
    import ChannelGroupsList from "../channel-groups/channel-groups-list";
    import ScenesList from "../scenes/scenes-list";
    import ChannelMeasurementsHistory from "./channel-measurements-history";
    import ChannelActionTriggers from "@/channels/action-trigger/channel-action-triggers";

    export default {
        props: ['channel'],
        components: {
            ChannelActionTriggers,
            ChannelMeasurementsHistory, ScenesList, ChannelGroupsList, DirectLinksList, SchedulesList
        },
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
                /*this.availableTabs.push({
                    id: 'scenes',
                    header: 'Scenes', // i18n
                    count: this.channel.relationsCount.scenes,
                });*/
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
            const currentTab = this.availableTabs.filter(tab => tab.id == this.$route.query.tab)[0];
            this.currentTab = currentTab ? currentTab.id : (this.availableTabs[0] ? this.availableTabs[0].id : undefined);
        },
    };
</script>
