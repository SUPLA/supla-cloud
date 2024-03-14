<template>
    <loading-cover :loading="hasLogs === undefined || fetchingDenseLogs">
        <div class="container">
            <div class="clearfix left-right-header reverse">
                <div class="mb-3">
                    <div class="dropdown d-inline-block" v-if="supportedChartModes.length > 1">
                        <button class="btn btn-default dropdown-toggle btn-wrapped" type="button" data-toggle="dropdown">
                            {{ $t('Show data') }}:
                            {{ $t(chartModeLabels[chartMode]) }}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu">
                            <li v-for="mode in supportedChartModes" :key="mode"
                                :class="{active: mode === chartMode}">
                                <a @click="changeChartMode(mode)">
                                    {{ $t(chartModeLabels[mode]) }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="mb-3">
                    <ChannelMeasurementsDownload :channel="channel" @delete="onMeasurementsDelete()" :storage="storage"
                        :chart-mode="chartMode"
                        :date-range="dateRange" v-if="hasLogs"/>
                </div>
            </div>

            <div v-if="supportsChart && storage && hasStorageSupport">
                <transition-expand>
                    <div v-if="fetchingLogsProgress" class="mb-5">
                        <div v-if="fetchingLogsProgress === true" class="text-center">
                            <label>{{ $t('All logs has been downloaded. Reload the chart to see them.') }}</label>
                            <div>
                                <a @click="rerender()" class="btn btn-default">{{ $t('Reload the chart') }}</a>
                            </div>
                        </div>
                        <div v-else>
                            <div class="form-group text-center">
                                <label>{{ $t('You can browse only the most recent logs now. Wait a while for the full history.') }}</label>
                            </div>
                            <div class="progress">
                                <div class="progress-bar progress-bar-success progress-bar-striped active"
                                    role="progressbar"
                                    aria-valuemin="0" :aria-valuenow="Math.min(100, fetchingLogsProgress)" aria-valuemax="100"
                                    :style="{width: Math.min(100, fetchingLogsProgress) + '%'}">
                                    <span v-if="fetchingLogsProgress < 100">{{ Math.min(100, Math.round(fetchingLogsProgress)) }}%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </transition-expand>

                <ChannelMeasurementsHistoryChart :channel="channel" :storage="storage" :chart-mode="chartMode"
                    v-if="hasLogs" @dateRangeChanged="(d) => dateRange = d"/>
            </div>

            <empty-list-placeholder v-if="hasLogs === false" class="my-5"></empty-list-placeholder>

            <div class="alert alert-warning my-5" v-if="!hasStorageSupport">
                {{ $t('Your browser does not support client-side database mechanism (IndexedDB). It is required to render the charts and enable advanced export modes. You may need to upgrade your browser or exit private browsing mode to use these features.') }}
            </div>
        </div>
    </loading-cover>
</template>

<script>
    import {CHART_TYPES} from "./channel-measurements-history-chart-strategies";
    import ChannelMeasurementsDownload from "@/channels/history/channel-measurements-download.vue";
    import {IndexedDbMeasurementLogsStorage} from "@/channels/history/channel-measurements-storage";
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import ChannelMeasurementsHistoryChart from "@/channels/history/channel-measurements-history-chart.vue";
    import ChannelFunction from "@/common/enums/channel-function";

    export default {
        components: {ChannelMeasurementsHistoryChart, TransitionExpand, ChannelMeasurementsDownload},
        props: ['channel'],
        data: function () {
            return {
                deleteConfirm: false,
                hasLogs: undefined,
                fetchingDenseLogs: false,
                chartMode: 'default',
                dateRange: {},
                chartModeLabels: {
                    fae: 'Forward active energy', // i18n
                    rae: 'Reverse active energy', // i18n
                    fre: 'Forward reactive energy', // i18n
                    rre: 'Reverse reactive energy', // i18n
                    fae_rae: 'Arithmetic balance', // i18n
                    fae_rae_vector: 'Vector balance', // i18n
                    voltageHistory: 'Voltage', // i18n
                    currentHistory: 'Current', // i18n
                    powerActiveHistory: 'Power active', // i18n
                },
                storage: undefined,
                fetchingLogsProgress: false,
                hasStorageSupport: true,
            };
        },
        async mounted() {
            if (this.supportsChart) {
                await this.initializeStorage();
            } else {
                this.hasLogs = true;
            }
            if (this.supportedChartModes.length > 0) {
                this.chartMode = this.supportedChartModes[0];
            }
        },
        methods: {
            async initializeStorage() {
                this.chartStrategy = CHART_TYPES.forChannel(this.channel, this.chartMode);
                this.storage = new IndexedDbMeasurementLogsStorage(this.channel, this.chartMode);
                await this.storage.connect();
                this.hasStorageSupport = await this.storage.checkSupport();
                this.hasLogs = (await this.storage.init(this)).length > 1;
                if (this.hasLogs && this.hasStorageSupport) {
                    this.fetchAllLogs();
                }
            },
            fetchAllLogs() {
                this.storage.fetchOlderLogs(this, (progress) => this.fetchingLogsProgress = progress)
                    .then((downloaded) => this.fetchingLogsProgress = downloaded);
            },
            changeChartMode(newMode) {
                this.hasLogs = undefined;
                this.chartMode = newMode;
                this.initializeStorage();
            },
            onMeasurementsDelete() {
                this.hasLogs = false;
            },
            async rerender() {
                this.hasLogs = undefined;
                this.fetchingLogsProgress = false;
                this.$nextTick(async () => this.hasLogs = (await this.storage.init(this)).length > 1);
            }
        },
        computed: {
            supportsChart() {
                return this.channel && CHART_TYPES[this.channel.function.name];
            },
            supportedChartModes() {
                if (this.channel.functionId === ChannelFunction.ELECTRICITYMETER) {
                    const modesMap = {
                        forwardActiveEnergy: 'fae',
                        reverseActiveEnergy: 'rae',
                        forwardReactiveEnergy: 'fre',
                        reverseReactiveEnergy: 'rre'
                    };
                    const defaultModes = ['forwardActiveEnergy', 'reverseActiveEnergy', 'forwardReactiveEnergy', 'reverseReactiveEnergy'];
                    let availableModes = this.channel.config.countersAvailable || defaultModes;
                    const modes = availableModes.filter(mode => modesMap[mode]).map(mode => modesMap[mode]);
                    if (modes.includes('fae') && modes.includes('rae')) {
                        modes.push('fae_rae');
                    }
                    if (availableModes.includes('forwardActiveEnergyBalanced') && availableModes.includes('reverseActiveEnergyBalanced')) {
                        modes.push('fae_rae_vector');
                    }
                    modes.push('voltageHistory');
                    modes.push('currentHistory');
                    modes.push('powerActiveHistory');
                    return modes;
                }
                return [];
            },
        },
    };
</script>
