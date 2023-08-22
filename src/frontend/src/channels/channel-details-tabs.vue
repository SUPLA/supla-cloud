<template>
    <div class="channel-details-tabs">
        <div class="container"
            v-if="availableTabs.length">
            <div class="form-group">
                <ul class="nav nav-tabs">
                    <router-link :to="{name: tabDefinition.route, params: {id: channel.id}}" tag="li"
                        v-for="tabDefinition in availableTabs" :key="tabDefinition.id">
                        <a>
                            {{ $t(tabDefinition.header) }}
                            <span v-if="tabDefinition.count !== undefined">({{ tabDefinition.count() }})</span>
                        </a>
                    </router-link>
                </ul>
            </div>
        </div>

        <RouterView :subject="channel" :channel="channel" @rerender="rerender()" v-if="tabVisible"/>
    </div>
</template>

<script>
    import ChannelFunction from "../common/enums/channel-function";
    import {ChannelFunctionTriggers} from "@/channels/reactions/channel-function-triggers";

    export default {
        props: ['channel'],
        data() {
            return {
                tabVisible: true,
                availableTabs: []
            };
        },
        methods: {
            rerender() {
                this.tabVisible = false;
                this.$nextTick(() => this.tabVisible = true);
            },
        },
        mounted() {
            const hasActions = this.channel.possibleActions?.length > 0;
            const isActionTrigger = this.channel.functionId === ChannelFunction.ACTION_TRIGGER;
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
            if ((hasActions && this.channel.actionTriggersIds?.length) || isActionTrigger) {
                this.availableTabs.push({
                    route: 'channel.actionTriggers',
                    header: 'Action triggers', // i18n
                    count: () => this.channel.actionTriggersIds?.length + (isActionTrigger ? 1 : 0),
                });
            }
            if (hasActions) {
                this.availableTabs.push({
                    route: 'channel.schedules',
                    header: 'Schedules', // i18n
                    count: () => this.channel.relationsCount.schedules,
                });
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
            if (this.$router.currentRoute.name === 'channel' && this.availableTabs.length) {
                this.$router.replace({name: this.availableTabs[0].route, params: {id: this.channel.id}});
            }
        },
    };
</script>
