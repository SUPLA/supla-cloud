<template>
    <loading-cover :loading="hasLogs === undefined || fetchingDenseLogs">
        <div class="container">
            <div :class="'download-buttons form-group text-' + (sparseLogs.length ? 'right' : 'center')" v-if="hasLogs">
                <ChannelMeasurementsDownload :channel="channel" @delete="onMeasurementsDelete()" :storage="storage"/>
            </div>

            <div v-if="supportsChart && storage && hasStorageSupport">
                <div class="text-center my-3" v-if="sparseLogs && sparseLogs.length > 1">
                    <ChannelMeasurementsPredefinedTimeRanges :storage="storage" @choose="setTimeRange($event)"/>
                    <div class="d-inline-block dropdown mx-2 my-2">
                        <button class="btn btn-default dropdown-toggle btn-wrapped" type="button" data-toggle="dropdown">
                            {{ $t('Logs aggregation:') }}
                            {{ $t(`logs_aggregation_${aggregationMethod}`) }}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li v-for="mode in ['minute', 'hour', 'day', 'month']"
                                :key="mode"
                                :class="{disabled: !availableAggregationStrategies.includes(mode), active: mode === aggregationMethod}">
                                <a @click="changeAggregationMethod(mode)">
                                    {{ $t(`logs_aggregation_${mode}`) }}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="d-inline-block dropdown" v-if="supportedChartModes.length > 1">
                        <button class="btn btn-default dropdown-toggle btn-wrapped" type="button" data-toggle="dropdown">
                            {{ $t('Chart mode:') }}
                            {{ $t(chartModeLabels[chartMode]) }}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li v-for="mode in supportedChartModes" :key="mode"
                                :class="{active: mode === chartMode}">
                                <a @click="changeChartMode(mode)">
                                    {{ $t(chartModeLabels[mode]) }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <DateRangePicker v-model="dateRange" :min="null"/>

                <transition-expand>
                    <div v-if="fetchingLogsProgress" class="mb-5">
                        <div v-if="fetchingLogsProgress === true" class="text-center">
                            <label>{{ $t('All logs has been downloaded. Reload the chart to see them.') }}</label>
                            <div>
                                <a @click="$emit('rerender')" class="btn btn-default">{{ $t('Reload the chart') }}</a>
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

                <h3 class="text-center my-5" v-if="sparseLogs.length === 0">{{ $t('Your chart is being drawn...') }}</h3>
                <div>
                    <div ref="bigChart"></div>
                    <div ref="smallChart"></div>
                </div>
            </div>

            <empty-list-placeholder v-if="hasLogs === false"></empty-list-placeholder>

            <div class="alert alert-warning my-5" v-if="!hasStorageSupport">
                {{ $t('Your browser does not support client-side database mechanism (IndexedDB). It is required to render the charts and enable advanced export modes. You may need to upgrade your browser or exit private browsing mode to use these features.') }}
            </div>
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
    import ChannelMeasurementsPredefinedTimeRanges from "@/channels/history/channel-measurements-predefined-time-ranges.vue";
    import DateRangePicker from "@/direct-links/date-range-picker.vue";

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

    export default {
        components: {DateRangePicker, ChannelMeasurementsPredefinedTimeRanges, TransitionExpand, ChannelMeasurementsDownload},
        props: ['channel'],
        data: function () {
            return {
                deleteConfirm: false,
                currentMinTimestamp: undefined,
                currentMaxTimestamp: undefined,
                hasLogs: undefined,
                sparseLogs: [],
                sparseLogsAggregation: 'minute',
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
                    fae_rae: 'Arithmetic balance', // i18n
                },
                aggregationMethod: 'day',
                storage: undefined,
                fetchingLogsProgress: false,
                hasStorageSupport: true,
            };
        },
        mounted() {
            if (this.supportsChart) {
                this.chartStrategy = CHART_TYPES[this.channel.function.name];
                this.storage = new IndexedDbMeasurementLogsStorage(this.channel);
                this.storage.checkSupport().then(hasSupport => {
                    this.hasStorageSupport = hasSupport;
                    this.storage.init(this).then((firstPageOfLogs) => {
                        this.hasLogs = firstPageOfLogs.length > 0;
                        if (this.hasLogs && hasSupport) {
                            this.storage.getSparseLogsAggregationStrategy().then(strategy => {
                                this.sparseLogsAggregation = strategy;
                                this.storage.fetchSparseLogs().then((logs) => {
                                    this.sparseLogs = logs;
                                    this.renderCharts();
                                    this.fetchAllLogs();
                                });
                            })
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
        created() {
            this.setTimeRange = debounce(({afterTimestampMs, beforeTimestampMs}) => {
                this.currentMinTimestamp = afterTimestampMs;
                this.currentMaxTimestamp = beforeTimestampMs;
                this.fetchingDenseLogs = true;
                this.updateSmallChart();
                this.fetchDenseLogs().then(() => this.rerenderBigChart());
            }, 50);
        },
        methods: {
            fetchAllLogs() {
                this.storage.fetchOlderLogs(this, (progress) => this.fetchingLogsProgress = progress)
                    .then((downloaded) => this.fetchingLogsProgress = downloaded);
            },
            getSmallChartSeries() {
                const series = [];
                if (this.sparseLogs.length) {
                    // const allLogs = this.adjustLogs(this.sparseLogs);
                    return this.chartStrategy.series.call(this, this.sparseLogs);
                }
                return series;
            },
            getBigChartSeries() {
                const series = [];
                if (this.denseLogs.length) {
                    // const allLogs = this.denseLogs;
                    return this.chartStrategy.series.call(this, this.denseLogs);
                }
                return series;
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
                                pan: false,
                                reset: false,
                                customIcons: [{
                                    icon: '<span class="pe-7s-refresh" style="font-weight: bold"></span>',
                                    index: 2,
                                    title: this.$t('reset chart view'),
                                    click: () => this.$emit('rerender'),
                                }]
                            },
                        },
                        animations: {enabled: false},
                        locales,
                        events: {
                            zoomed: (chartContext, {xaxis}) => {
                                this.setTimeRange({afterTimestampMs: xaxis.min, beforeTimestampMs: xaxis.max});
                            },
                            scrolled: (chartContext, {xaxis}) => {
                                this.setTimeRange({afterTimestampMs: xaxis.min, beforeTimestampMs: xaxis.max});
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
                            formatter: (value, info) => {
                                let nextPointTimestamp = undefined;
                                if (info?.series && info.seriesIndex !== undefined) {
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
                                min: Math.max(
                                    this.sparseLogs[Math.max(0, this.sparseLogs.length - 30)].date_timestamp * 1000,
                                    this.sparseLogs[this.sparseLogs.length - 1].date_timestamp * 1000 - 86400_000 * 6,
                                )
                            },
                        },
                        events: {
                            selection: (chartContext, {xaxis}) => {
                                if (xaxis.min !== this.currentMinTimestamp || xaxis.max !== this.currentMaxTimestamp) {
                                    this.setTimeRange({
                                        afterTimestampMs: DateTime.fromMillis(xaxis.min).startOf(this.sparseLogsAggregation).toMillis(),
                                        beforeTimestampMs: DateTime.fromMillis(xaxis.max).endOf(this.sparseLogsAggregation).toMillis(),
                                    });
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
            updateSmallChart() {
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
            },
            fetchDenseLogs() {
                const afterTimestamp = Math.floor(this.currentMinTimestamp / 1000);
                const beforeTimestamp = Math.ceil(this.currentMaxTimestamp / 1000);
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
                    const newChartOptions = {
                        // xaxis: {
                        //     min: this.denseLogs[0].date_timestamp,
                        //     max: this.currentMaxTimestamp,
                        // },
                        yaxis: this.chartStrategy.yaxes.call(this, this.denseLogs),
                        annotations: {
                            xaxis: this.chartStrategy.getAnnotations?.call(this, this.denseLogs) || [],
                        },
                    };
                    this.bigChart.updateOptions(merge(newChartOptions, this.chartStrategy.chartOptions.call(this)), false, false);
                }
                this.fetchingDenseLogs = false;
            },
            rerenderSmallChart() {
                if (this.sparseLogs && this.sparseLogs.length) {
                    const series = this.getSmallChartSeries();
                    this.smallChart.updateSeries(series, true);
                    this.smallChart.updateOptions(this.chartStrategy.chartOptions.call(this), false, false);
                }
            },
            changeChartMode(newMode) {
                this.chartMode = newMode;
                this.rerenderBigChart();
                this.rerenderSmallChart();
            },
            changeAggregationMethod(newMethod) {
                if (this.availableAggregationStrategies.includes(newMethod)) {
                    this.aggregationMethod = newMethod;
                    this.fetchingDenseLogs = true;
                    this.fetchDenseLogs().then(() => this.rerenderBigChart());
                }
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
            dateRange: {
                get() {
                    console.log('get');
                    return {
                        dateStart: this.currentMinTimestamp ? DateTime.fromMillis(this.currentMinTimestamp).toISO() : undefined,
                        dateEnd: this.currentMaxTimestamp ? DateTime.fromMillis(this.currentMaxTimestamp).toISO() : undefined,
                    };
                },
                set(dateRange) {
                    this.setTimeRange({
                        afterTimestampMs: DateTime.fromISO(dateRange.dateStart).toMillis(),
                        beforeTimestampMs: DateTime.fromISO(dateRange.dateEnd).toMillis(),
                    });
                }
            },
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
                    const modes = (this.channel.config.countersAvailable || defaultModes)
                        .filter(mode => modesMap[mode]).map(mode => modesMap[mode]);
                    if (modes.includes('fae') && modes.includes('rae')) {
                        modes.push('fae_rae');
                    }
                    return modes;
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
    @import "../../styles/mixins";

    ::v-deep .apexcharts-menu-item.exportCSV {
        display: none;
    }

    .download-buttons {
        @include on-xs-and-down {
            text-align: center;
        }
    }
</style>
