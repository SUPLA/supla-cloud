<template>
    <loading-cover :loading="hasLogs === undefined || fetchingDenseLogs">
        <div class="container">
            <div :class="'form-group text-' + (sparseLogs.length ? 'right' : 'center')"
                v-if="hasLogs">
                <ChannelMeasurementsDownload :channel="channel" @delete="onMeasurementsDelete()"/>
            </div>

            <div v-if="supportsChart && this.storage">
                <div class="form-group text-center"
                    v-if="sparseLogs && sparseLogs.length > 1">
                    <div class="btn-group"
                        v-if="supportedChartModes.length > 1">
                        <a :class="'btn btn-' + (chartMode === mode ? 'green' : 'default')"
                            :key="mode"
                            @click="changeChartMode(mode)"
                            v-for="mode in supportedChartModes">
                            {{ $t(chartModeLabels[mode]) }}
                        </a>
                    </div>
                </div>

                <div class="form-group text-center"
                    v-if="sparseLogs && sparseLogs.length > 1">
                    <div class="btn-group">
                        <button :class="'btn btn-' + (aggregationMethod === mode ? 'green' : 'default')"
                            type="button"
                            :key="mode"
                            :disabled="!availableAggregationStrategies.includes(mode)"
                            @click="changeAggregationMethod(mode)"
                            v-for="mode in ['all', 'hour', 'day', 'month']">
                            {{ mode }}
                        </button>
                    </div>
                </div>

                <transition-expand>
                    <div v-if="fetchingLogsProgress" class="mb-5">
                        <div v-if="fetchingLogsProgress === true" class="text-center">
                            <label>{{ $t('All logs has been downloaded. Refresh the page to see them.') }}</label>
                            <div>
                                <a href="" class="btn btn-default">{{ $t('Refresh the page') }}</a>
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

                <div :class="sparseLogs && sparseLogs.length > 4 ? '' : 'invisible'">
                    <div ref="bigChart"></div>
                    <div ref="smallChart"></div>
                </div>
            </div>

            <empty-list-placeholder v-if="hasLogs === false"></empty-list-placeholder>
        </div>
    </loading-cover>
</template>

<script>
    import {debounce, merge} from "lodash";
    import {channelTitle} from "../../common/filters";
    import ApexCharts from "apexcharts";
    import {DateTime} from "luxon";
    import {CHART_TYPES} from "./channel-measurements-history-chart-strategies";
    import ChannelMeasurementsDownload from "@/channels/history/channel-measurements-download.vue";
    import {IndexedDbMeasurementLogsStorage} from "@/channels/history/channel-measurements-storage";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    window.ApexCharts = ApexCharts;

    const locales = [
        require("apexcharts/dist/locales/en.json"),
        require("apexcharts/dist/locales/pl.json"),
        require("apexcharts/dist/locales/cs.json"),
        require("apexcharts/dist/locales/de.json"),
        require("apexcharts/dist/locales/es.json"),
        require("apexcharts/dist/locales/el.json"),
        require("apexcharts/dist/locales/fr.json"),
        require("apexcharts/dist/locales/it.json"),
        require("apexcharts/dist/locales/lt.json"),
        require("apexcharts/dist/locales/nl.json"),
        require("apexcharts/dist/locales/nb.json"),
        require("apexcharts/dist/locales/pt.json"),
        require("apexcharts/dist/locales/ru.json"),
        require("apexcharts/dist/locales/sk.json"),
        require("apexcharts/dist/locales/sl.json"),
    ];

    const SPARSE_LOGS_COUNT = 300;
    const DENSE_LOGS_COUNT = 150;

    export default {
        components: {TransitionExpand, ChannelMeasurementsDownload},
        props: ['channel'],
        data: function () {
            return {
                deleteConfirm: false,
                currentMinTimestamp: undefined,
                currentMaxTimestamp: undefined,
                hasLogs: undefined,
                sparseLogs: [],
                denseLogs: [],
                bigChart: undefined,
                smallChart: undefined,
                fetchingDenseLogs: false,
                chartStrategy: undefined,
                chartMode: 'fae',
                chartModeLabels: {
                    fae: 'Forward active energy', // i18n
                    rae: 'Reverse active energy', // i18n
                    fre: 'Forward reactive energy', // i18n
                    rre: 'Reverse reactive energy', // i18n
                },
                aggregationMethod: 'day',
                storage: undefined,
                fetchingLogsProgress: false,
            };
        },
        mounted() {
            if (this.supportsChart) {
                this.chartStrategy = CHART_TYPES[this.channel.function.name];
                this.storage = new IndexedDbMeasurementLogsStorage(this.channel);
                this.storage.init(this).then(() => {
                    this.fetchSparseLogs().then((logs) => {
                        this.hasLogs = logs.length > 0;
                        if (logs.length > 1) {
                            // const minTimestamp = logs[0].date_timestamp * 1000;
                            // const maxTimestamp = logs[logs.length - 1].date_timestamp * 1000;
                            // const expectedInterval = Math.max(600000, Math.ceil((maxTimestamp - minTimestamp) / SPARSE_LOGS_COUNT));
                            this.sparseLogs = logs;//this.fillGaps(logs, expectedInterval);
                            this.renderCharts();
                            this.fetchAllLogs();
                        }
                    });
                });
            } else {
                this.hasLogs = true;
            }
            if (this.supportedChartModes.length > 0) {
                this.chartMode = this.supportedChartModes[0];
            }
        },
        methods: {
            fetchAllLogs() {
                this.storage.fetchOlderLogs(this, (progress) => this.fetchingLogsProgress = progress)
                    .then((downloaded) => this.fetchingLogsProgress = downloaded);
            },
            getSmallChartSeries() {
                const series = [];
                if (this.sparseLogs.length) {
                    const allLogs = this.adjustLogs(this.sparseLogs);
                    return this.chartStrategy.series.call(this, allLogs);
                }
                return series;
            },
            getBigChartSeries() {
                const series = [];
                if (this.denseLogs.length) {
                    const allLogs = this.denseLogs;
                    return this.chartStrategy.series.call(this, this.adjustLogs(allLogs));
                }
                return series;
            },
            fetchSparseLogs() {
                return this.storage.fetchSparseLogs(SPARSE_LOGS_COUNT).then(logItems => {
                    // return this.$http.get(`channels/${this.channel.id}/measurement-logs?sparse=${SPARSE_LOGS_COUNT}&order=ASC`)
                    //     .then(({body: logItems, headers}) => {
                    if (logItems.length > 0) {
                        // const maxTimestamp = headers.get('X-Max-Timestamp');
                        // if (maxTimestamp && maxTimestamp > logItems[logItems.length - 1].date_timestamp) {
                        //     logItems.push({...logItems[logItems.length - 1], date_timestamp: maxTimestamp});
                        // }
                        // const minTimestamp = headers.get('X-Min-Timestamp');
                        // if (minTimestamp && minTimestamp < logItems[0].date_timestamp) {
                        //     logItems.unshift({...logItems[0], date_timestamp: minTimestamp});
                        // }
                    }
                    return logItems;
                });
            },
            formatPointLabel(pointTimestampMs, nextPointTimestampMs) {
                if (this.aggregationMethod === 'hour') {
                    let datetimeLabel = DateTime.fromSeconds(pointTimestampMs / 1000).startOf('hour').toLocaleString(DateTime.DATETIME_MED);
                    if (nextPointTimestampMs) {
                        datetimeLabel += ' - ' + DateTime.fromSeconds(pointTimestampMs / 1000).endOf('hour').toLocaleString(DateTime.DATETIME_MED);
                    }
                    return datetimeLabel;
                } else if (this.aggregationMethod === 'day') {
                    return DateTime.fromSeconds(pointTimestampMs / 1000).toLocaleString(DateTime.DATE_MED);
                } else if (this.aggregationMethod === 'month') {
                    return DateTime.fromSeconds(pointTimestampMs / 1000).toLocaleString({year: 'numeric', month: 'long'});
                } else {
                    let datetimeLabel = DateTime.fromSeconds(pointTimestampMs / 1000).toLocaleString(DateTime.DATETIME_MED);
                    if (nextPointTimestampMs) {
                        datetimeLabel += ' - ' + DateTime.fromSeconds(nextPointTimestampMs / 1000).toLocaleString(DateTime.DATETIME_MED);
                    }
                    return datetimeLabel;
                }
            },
            renderCharts() {
                const updateSmallChart = () => {
                    this.smallChart.updateOptions(
                        {
                            chart: {
                                selection: {
                                    xaxis: {
                                        min: this.currentMinTimestamp,
                                        max: this.currentMaxTimestamp,
                                    }
                                }
                            }
                        },
                        false,
                        false
                    );
                    fetchPreciseLogs();
                };

                const fetchPreciseLogs = debounce(() => {
                    this.fetchingDenseLogs = true;
                    this.fetchDenseLogs().then(() => this.rerenderBigChart());
                }, 50);

                let chartId = `channel${this.channel.id}`;
                const bigChartOptions = {
                    chart: {
                        id: chartId,
                        type: this.chartStrategy.chartType,
                        height: 400,
                        toolbar: {
                            show: true,
                            tools: {
                                download: true,
                                selection: false,
                                zoom: true,
                                zoomin: true,
                                zoomout: true,
                                pan: true,
                                reset: false,
                                customIcons: [{
                                    icon: '<span class="pe-7s-refresh" style="font-weight: bold"></span>',
                                    index: 2,
                                    title: this.$t('reset chart view'),
                                    click: () => {
                                        this.currentMaxTimestamp = this.sparseLogs[this.sparseLogs.length - 1].date_timestamp * 1000;
                                        this.currentMinTimestamp = this.sparseLogs[Math.max(0, this.sparseLogs.length - 30)].date_timestamp * 1000;
                                        updateSmallChart();
                                    }
                                }]
                            },
                        },
                        animations: {enabled: false},
                        locales,
                        events: {
                            zoomed: (chartContext, {xaxis}) => {
                                this.currentMaxTimestamp = xaxis.max;
                                this.currentMinTimestamp = xaxis.min;
                                updateSmallChart();
                            },
                            scrolled: (chartContext, {xaxis}) => {
                                this.currentMaxTimestamp = xaxis.max;
                                this.currentMinTimestamp = xaxis.min;
                                updateSmallChart();
                            },
                            // click: (event, chartContext, config) => {
                            //     console.log(event, chartContext, config);
                            // },
                        },
                    },
                    title: {style: {fontSize: '20px', fontWeight: 'normal', fontFamily: 'Quicksand'}},
                    legend: {show: true, showForSingleSeries: true, position: 'top'},
                    stroke: {width: 3,/* curve: 'smooth'*/},
                    colors: ['#00d150', '#008ffb', '#ff851b'],
                    dataLabels: {enabled: false},
                    fill: {opacity: 1},
                    markers: {size: 0},
                    xaxis: {
                        type: 'datetime',
                        labels: {
                            datetimeUTC: false,
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        x: {
                            formatter: (value, info) => {//, {series, seriesIndex, dataPointIndex, w}) => {
                                // console.log(value, s, e);
                                let nextPointTimestamp = undefined;
                                if (info?.series && info.seriesIndex !== undefined) {
                                    // debugger;
                                    const nextPoint = info.w.config.series[info.seriesIndex].data[info.dataPointIndex + 1];
                                    if (nextPoint) {
                                        nextPointTimestamp = nextPoint.x;
                                    }
                                }
                                if (value && info?.series) {
                                    return this.formatPointLabel(value, nextPointTimestamp);
                                } else {
                                    return this.$t('Value');
                                }
                                // debugger;
                                // const label = value && info?.series ? this.formatPointLabel(value, nextPointTimestamp) : undefined;
                                // console.log(label);
                                // return label;
                                // return undefined;
                            }
                        }
                    },
                };

                const smallChartOptions = {
                    chart: {
                        id: 'smallChart',
                        height: 130,
                        type: this.chartStrategy.chartType,
                        brush: {target: chartId, enabled: true, autoScaleYaxis: false},
                        locales,
                        animations: {enabled: false},
                        selection: {
                            enabled: true,
                            xaxis: {
                                max: this.sparseLogs[this.sparseLogs.length - 1].date_timestamp * 1000,
                                min: this.sparseLogs[Math.max(0, this.sparseLogs.length - 30)].date_timestamp * 1000,
                            },
                        },
                        events: {
                            selection: (chartContext, {xaxis}) => {
                                if (xaxis.min !== this.currentMinTimestamp || xaxis.max !== this.currentMaxTimestamp) {
                                    this.bigChart.updateOptions({
                                        xaxis: {
                                            min: xaxis.min,
                                            max: xaxis.max,
                                        },
                                    }, false, false, false, false, false);
                                    this.currentMinTimestamp = xaxis.min;
                                    this.currentMaxTimestamp = xaxis.max;
                                    fetchPreciseLogs();
                                }
                            },
                        },
                    },
                    colors: ['#00d150', '#008ffb', '#ff851b'],
                    legend: {show: false},
                    xaxis: {
                        type: 'datetime',
                        tooltip: {enabled: false},
                        labels: {datetimeUTC: false}
                    },
                    yaxis: {labels: {show: false}}
                };

                const chartOptions = this.chartStrategy.chartOptions.call(this);
                const series = this.getSmallChartSeries();
                merge(bigChartOptions, chartOptions);
                merge(smallChartOptions, chartOptions);
                this.bigChart = new ApexCharts(this.$refs.bigChart, {...bigChartOptions, series});
                this.smallChart = new ApexCharts(this.$refs.smallChart, {...smallChartOptions, series});
                this.bigChart.render();
                this.smallChart.render();
                this.updateChartLocale();
            },
            adjustLogs(logs) {
                if (!logs || !logs.length) {
                    return logs;
                }
                return this.chartStrategy.adjustLogs(logs);
            },
            fetchDenseLogs() {
                const afterTimestamp = Math.floor(this.currentMinTimestamp / 1000) - 1;
                const beforeTimestamp = Math.ceil(this.currentMaxTimestamp / 1000) + 1;
                if (!this.availableAggregationStrategies.includes(this.aggregationMethod)) {
                    this.aggregationMethod = this.availableAggregationStrategies[this.availableAggregationStrategies.length - 1];
                }
                return this.storage.fetchDenseLogs(afterTimestamp, beforeTimestamp, this.aggregationMethod).then(logItems => {
                    return this.denseLogs = logItems;
                });
            },
            updateChartLocale() {
                const availableLocales = locales.map((l) => l.name);
                const locale = availableLocales.includes(this.$i18n.locale) ? this.$i18n.locale : 'en';
                this.smallChart.setLocale(locale);
                this.bigChart.setLocale(locale);
                this.bigChart.updateOptions({
                    title: {text: channelTitle(this.channel, this)},
                });
                this.rerenderBigChart();
            },
            rerenderBigChart() {
                if (this.denseLogs && this.denseLogs.length) {
                    const series = this.getBigChartSeries();
                    this.bigChart.updateSeries(series, true);
                    this.bigChart.updateOptions({
                        xaxis: {
                            min: this.currentMinTimestamp,
                            max: this.currentMaxTimestamp,
                        },
                        yaxis: this.chartStrategy.yaxes.call(this, this.denseLogs),
                    }, false, false);
                }
                this.fetchingDenseLogs = false;
            },
            rerenderSmallChart() {
                if (this.sparseLogs && this.sparseLogs.length) {
                    const series = this.getSmallChartSeries();
                    this.smallChart.updateSeries(series, true);
                }
            },
            changeChartMode(newMode) {
                this.chartMode = newMode;
                this.rerenderBigChart();
                this.rerenderSmallChart();
            },
            changeAggregationMethod(newMethod) {
                this.aggregationMethod = newMethod;
                this.fetchingDenseLogs = true;
                this.fetchDenseLogs().then(() => this.rerenderBigChart());
            },
            onMeasurementsDelete() {
                this.hasLogs = false;
                this.sparseLogs = undefined;
                this.bigChart?.destroy();
                this.smallChart?.destroy();
                this.bigChart = undefined;
                this.smallChart = undefined;
            },
        },
        computed: {
            visibleRange() {
                return this.currentMaxTimestamp && (this.currentMaxTimestamp - this.currentMinTimestamp);
            },
            availableAggregationStrategies() {
                return this.storage.getAvailableAggregationStrategies(this.visibleRange / 1000);
            },
            supportsChart() {
                return this.channel && CHART_TYPES[this.channel.function.name];
            },
            supportedChartModes() {
                if (this.channel.function.name === 'ELECTRICITYMETER') {
                    const modesMap = {
                        forwardActiveEnergy: 'fae',
                        reverseActiveEnergy: 'rae',
                        forwardReactiveEnergy: 'fre',
                        reverseReactiveEnergy: 'rre'
                    };
                    const defaultModes = ['forwardActiveEnergy', 'reverseActiveEnergy', 'forwardReactiveEnergy', 'reverseReactiveEnergy'];
                    const modes = this.channel.config.countersAvailable || defaultModes;
                    return modes.filter(mode => modesMap[mode]).map(mode => modesMap[mode]);
                }
                return [];
            },
        },
        watch: {
            '$i18n.locale'() {
                this.updateChartLocale();
            }
        }
    };
</script>

<style lang="scss" scoped>
    ::v-deep .apexcharts-menu-item.exportCSV {
        display: none;
    }
</style>
