<template>
    <div class="channel-details-tabs details-tabs" id="channel-details-tabs">
        <div class="container"
            v-if="availableTabs.length">
            <div class="form-group">
                <ul class="nav nav-tabs">
                    <li v-for="tabDefinition in availableTabs" :key="tabDefinition.id"
                        :class="{active: $route.name === tabDefinition.route}">
                        <router-link :to="{name: tabDefinition.route, params: {id: channel.id}}">
                            {{ $t(tabDefinition.header) }}
                            <span v-if="tabDefinition.count !== undefined">({{ tabDefinition.count() }})</span>
                        </router-link>
                    </li>
                </ul>
            </div>
        </div>

        <RouterView :subject="channel" :channel="channel" v-if="tabVisible"/>
    </div>
</template>

<script>
    import ChannelFunction from "../common/enums/channel-function";
    import {ChannelFunctionTriggers} from "@/channels/reactions/channel-function-triggers";
    import EventBus from "@/common/event-bus";
    import {mapStores} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";

    export default {
        props: {
            channelId: Number,
        },
        data() {
            return {
                tabVisible: true,
                availableTabs: [],
                channelUpdatedListener: undefined,
            };
        },
        methods: {
            detectAvailableTabs() {
                this.availableTabs = [];
                const hasActions = this.channel.possibleActions?.length > 0;
                const isActionTrigger = this.channel.functionId === ChannelFunction.ACTION_TRIGGER;
                if (this.channel.typeId === 6100 && this.channel.config?.weeklySchedule) {
                    this.availableTabs.push({
                        route: 'channel.thermostatPrograms',
                        header: 'Week', // i18n
                    });
                }
                if (ChannelFunctionTriggers[this.channel.functionId]) {
                    this.availableTabs.push({
                        route: 'channel.reactions',
                        header: 'Reactions', // i18n
                        count: () => this.channel.relationsCount.ownReactions,
                    });
                }
                if (this.channel.relationsCount.managedNotifications > 0) {
                    this.availableTabs.push({
                        route: 'channel.notifications',
                        header: 'Notifications', // i18n
                    });
                }
                if (this.channel.relationsCount.actionTriggers > 0 || isActionTrigger) {
                    this.availableTabs.push({
                        route: 'channel.actionTriggers',
                        header: 'Action triggers', // i18n
                        count: () => this.channel.relationsCount.actionTriggers + (isActionTrigger ? 1 : 0),
                    });
                }
                if (hasActions) {
                    const nonScheduleFunctions = [
                        ChannelFunction.HVAC_THERMOSTAT,
                        ChannelFunction.HVAC_DOMESTIC_HOT_WATER,
                        ChannelFunction.HVAC_THERMOSTAT_DIFFERENTIAL,
                        ChannelFunction.HVAC_THERMOSTAT_HEAT_COOL,
                    ];
                    if (!nonScheduleFunctions.includes(this.channel.functionId)) {
                        this.availableTabs.push({
                            route: 'channel.schedules',
                            header: 'Schedules', // i18n
                            count: () => this.channel.relationsCount.schedules,
                        });
                    }
                    this.availableTabs.push({
                        route: 'channel.channelGroups',
                        header: 'Channel groups', // i18n
                        count: () => this.channel.relationsCount.channelGroups,
                    });
                    this.availableTabs.push({
                        route: 'channel.scenes',
                        header: 'Scenes', // i18n
                        count: () => this.channel.relationsCount.scenes,
                    });
                }
                if (this.channel.function.id > 0 && !isActionTrigger) {
                    this.availableTabs.push({
                        route: 'channel.directLinks',
                        header: 'Direct links', // i18n
                        count: () => this.channel.relationsCount.directLinks,
                    });
                }
                const measurementsHistoryFunctions = [
                    ChannelFunction.THERMOMETER,
                    ChannelFunction.HUMIDITY,
                    ChannelFunction.HUMIDITYANDTEMPERATURE,
                    ChannelFunction.ELECTRICITYMETER,
                    ChannelFunction.IC_ELECTRICITYMETER,
                    ChannelFunction.IC_GASMETER,
                    ChannelFunction.IC_WATERMETER,
                    ChannelFunction.IC_HEATMETER,
                    ChannelFunction.THERMOSTAT,
                    ChannelFunction.THERMOSTATHEATPOLHOMEPLUS,
                    ChannelFunction.GENERAL_PURPOSE_METER,
                    ChannelFunction.GENERAL_PURPOSE_MEASUREMENT,
                ];
                if (measurementsHistoryFunctions.includes(this.channel.functionId)) {
                    this.availableTabs.push({
                        route: 'channel.measurementsHistory',
                        header: 'History of measurements', // i18n
                    });
                }
                if (this.channel.function.id === ChannelFunction.ELECTRICITYMETER) {
                    this.availableTabs.push({
                        route: 'channel.voltageAberrations',
                        header: 'Voltage aberrations', // i18n
                    });
                }
                if (this.channel.config?.ocr) {
                    this.availableTabs.push({
                        route: 'channel.ocrSettings',
                        header: 'OCR settings', // i18n
                    });
                }
            },
        },
        mounted() {
            this.detectAvailableTabs();
            if (this.availableTabs.length) {
                const isDefaultChannelRoute = this.$router.currentRoute.name === 'channel';
                const isDefaultTab = this.$router.currentRoute.name === this.availableTabs[0].route;
                const isAvailableTabRequested = this.availableTabs
                    .map(t => t.route)
                    .filter(r => this.$router.currentRoute.name.startsWith(r))
                    .length > 0;
                if (isDefaultChannelRoute || !isAvailableTabRequested) {
                    this.$router.replace({name: this.availableTabs[0].route, params: {id: this.channel.id}});
                } else if (!isDefaultTab) {
                    setTimeout(() => document.getElementById('channel-details-tabs').scrollIntoView({behavior: 'smooth'}));
                }
            }
            this.channelUpdatedListener = () => this.detectAvailableTabs();
            EventBus.$on('channel-updated', this.channelUpdatedListener);
        },
        computed: {
            channel() {
                return this.channelsStore.all[this.channelId];
            },
            ...mapStores(useChannelsStore),
        },
        beforeDestroy() {
            EventBus.$off('channel-updated', this.channelUpdatedListener);
        },
    };
</script>
