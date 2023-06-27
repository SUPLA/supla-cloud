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
        <div v-if="currentTab == 'reactions'">
            <div class="container">
                <ChannelReactionsConfig :subject="channel" @count="setCount('reactions', $event)"/>
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
            <channel-measurements-history :channel="channel" @rerender="rerenderMeasurementsHistory()"/>
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
    import ChannelReactionsConfig from "@/channels/reactions/channel-reactions-config.vue";
    import {ChannelReactionConditions} from "@/channels/reactions/channel-reaction-conditions";

    export default {
        props: ['channel'],
        components: {
            ChannelReactionsConfig,
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
                if ((this.$route.name === 'channel' || this.currentTab !== 'reactions') && this.$route.query.tab !== this.currentTab) {
                    this.$router.push({name: 'channel', params: {id: this.channel.id}, query: {tab: id}});
                }
            },
            rerenderMeasurementsHistory() {
                this.currentTab = undefined;
                this.$nextTick(() => this.currentTab = 'measurementsHistory');
            },
            setCount(tabId, count) {
                this.availableTabs.find(t => t.id = tabId).count = count;
            },
        },
        mounted() {
            if (ChannelReactionConditions[this.channel.functionId]) {
                this.availableTabs.push({
                    id: 'reactions',
                    header: 'Reactions', // i18n
                    count: this.channel.relationsCount.ownReactions,
                });
            }
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
                if (this.$route.name === 'channel') {
                    this.changeTab(this.$route.query.tab);
                }
            }
        },
    };
</script>
