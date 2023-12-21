<template>
    <loading-cover :loading="hasLogs === undefined || fetchingDenseLogs">
        <div class="container">
            <div :class="'download-buttons form-group text-' + (hasLogs ? 'right' : 'center')" v-if="hasLogs">
                <ChannelMeasurementsDownload :channel="channel" @delete="onMeasurementsDelete()" :storage="storage"
                    :date-range="dateRange"/>
            </div>

            <div v-if="supportsChart && storage && hasStorageSupport">
                <div class="text-center my-3" v-if="hasLogs">
                    <ChannelMeasurementsPredefinedTimeRanges :storage="storage" @choose="setTimeRange($event)"/>
                    <div class="d-inline-block dropdown" v-if="supportedChartModes.length > 1">
                        <button class="btn btn-default dropdown-toggle btn-wrapped" type="button" data-toggle="dropdown">
                            {{ $t('Chart mode') }}:
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
                    <div class="d-inline-block dropdown mx-2 my-2">
                        <button class="btn btn-default dropdown-toggle btn-wrapped" type="button" data-toggle="dropdown">
                            {{ $t('Aggregation') }}:
                            {{ $t(`logs_aggregation_${aggregationMethod}`) }}
                            <span class="caret"></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <!-- i18n:['logs_aggregation_minute','logs_aggregation_hour','logs_aggregation_day','logs_aggregation_month'] -->
                            <li v-for="mode in ['minute', 'hour', 'day', 'month']"
                                :key="mode"
                                :class="{disabled: !availableAggregationStrategies.includes(mode), active: mode === aggregationMethod}">
                                <a @click="changeAggregationMethod(mode)">
                                    {{ $t(`logs_aggregation_${mode}`) }}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="history-date-range-picker d-flex my-3" v-if="oldestLog && newestLog">
                    <div class="d-flex flex-column form-group mr-3 justify-content-end">
                        <button type="button" class="btn btn-default" @click="panTime(-1)"
                            :disabled="oldestLog.date_timestamp * 1000 + 1000 >= currentMinTimestamp">
                            <fa icon="chevron-left"/>
                        </button>
                    </div>
                    <DateRangePicker v-model="dateRange" :min="minDate" class="flex-grow-1"
                        :label-date-start="$t('From')" :label-date-end="$t('To')"/>
                    <div class="d-flex flex-column justify-content-end form-group ml-3">
                        <div class="d-flex">
                            <button class="btn btn-default" type="button" @click="panTime(1)"
                                :disabled="newestLog.date_timestamp * 1000 <= currentMaxTimestamp">
                                <fa icon="chevron-right"/>
                            </button>
                            <button class="btn btn-default ml-3" type="button">{{ $t('OK') }}</button>
                        </div>
                    </div>
                </div>

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

                <h3 class="text-center my-5" v-if="hasLogs && denseLogs.length === 0">{{ $t('Your chart is being drawn...') }}</h3>
                <div ref="bigChart"></div>
                <div v-if="channel.functionId === ChannelFunction.ELECTRICITYMETER && denseLogs.length > 0">
                    <h3>{{ $t('Summary for selected time range') }}</h3>
                    <ChannelMeasurementsHistorySummaryTableElectricityMeter :channel="channel" :logs="denseLogs"/>
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
    import ChannelMeasurementsHistorySummaryTableElectricityMeter
        from "@/channels/history/channel-measurements-history-summary-table-electricity-meter.vue";
    import ChannelFunction from "@/common/enums/channel-function";

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
        components: {
            ChannelMeasurementsHistorySummaryTableElectricityMeter,
            DateRangePicker, ChannelMeasurementsPredefinedTimeRanges, TransitionExpand, ChannelMeasurementsDownload
        },
        props: ['channel'],
        data: function () {
            return {
                deleteConfirm: false,
                currentMinTimestamp: undefined,
                currentMaxTimestamp: undefined,
                hasLogs: undefined,
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
                    fae_rae_vector: 'Vector balance', // i18n
                },
                aggregationMethod: 'day',
                storage: undefined,
                fetchingLogsProgress: false,
                hasStorageSupport: true,
                oldestLog: undefined,
                newestLog: undefined,
                ChannelFunction,
            };
        },
        async mounted() {
            if (this.supportsChart) {
                this.chartStrategy = CHART_TYPES[this.channel.function.name];
                this.storage = new IndexedDbMeasurementLogsStorage(this.channel);
                await this.storage.connect();
                this.hasStorageSupport = await this.storage.checkSupport();
                this.hasLogs = (await this.storage.init(this)).length > 1;
                if (this.hasLogs && this.hasStorageSupport) {
                    this.newestLog = await this.storage.getNewestLog();
                    this.oldestLog = await this.storage.getOldestLog();
                    this.fetchAllLogs();
                    this.renderCharts();
                    this.setTimeRange({
                        afterTimestampMs: Math.max(this.oldestLog.date_timestamp * 1000, this.newestLog.date_timestamp * 1000 - 86400000 * 7),
                        beforeTimestampMs: this.newestLog.date_timestamp * 1000,
                    })
                }
            } else {
                this.hasLogs = true;
            }
            if (this.supportedChartModes.length > 0) {
                this.chartMode = this.supportedChartModes[0];
            }
        },
        created() {
            this.setTimeRange = debounce(({afterTimestampMs, beforeTimestampMs}) => {
                let minTimestamp = DateTime.fromMillis(afterTimestampMs);
                minTimestamp = minTimestamp.set({seconds: 0, minutes: Math.floor(minTimestamp.get('minute') / 10) * 10});
                let maxTimestamp = DateTime.fromMillis(beforeTimestampMs);
                maxTimestamp = maxTimestamp.set({seconds: 0, minutes: Math.ceil(minTimestamp.get('minute') / 10) * 10});
                const diff = maxTimestamp.toSeconds() - minTimestamp.toSeconds();
                if (diff < 600) {
                    minTimestamp = minTimestamp.minus({minutes: 10});
                }
                const availableAggregationStrategies = this.storage.getAvailableAggregationStrategies(maxTimestamp.toSeconds() - minTimestamp.toSeconds());
                if (!availableAggregationStrategies.includes(this.aggregationMethod)) {
                    this.aggregationMethod = availableAggregationStrategies[availableAggregationStrategies.length - 1];
                }
                if (this.aggregationMethod !== 'minute') {
                    minTimestamp = minTimestamp.startOf(this.aggregationMethod);
                    maxTimestamp = maxTimestamp.endOf(this.aggregationMethod);
                }
                this.currentMinTimestamp = minTimestamp.toMillis();//) - Math.floor(Math.random() * 100);
                this.currentMaxTimestamp = maxTimestamp.toMillis();
                if (this.currentMinTimestamp < this.oldestLog.date_timestamp * 1000) {
                    this.currentMinTimestamp = this.oldestLog.date_timestamp * 1000;
                }
                if (this.currentMaxTimestamp <= this.currentMinTimestamp || this.currentMaxTimestamp > this.newestLog.date_timestamp * 1000) {
                    this.currentMaxTimestamp = this.newestLog.date_timestamp * 1000;
                }
                // the random below forces date range picker to rerender even if the same 10-minute slot was set
                this.currentMinTimestamp += Math.floor(Math.random() * 100);
                this.fetchingDenseLogs = true;
                this.fetchDenseLogs().then(() => this.rerenderBigChart());
            }, 50);
        },
        methods: {
            fetchAllLogs() {
                this.storage.fetchOlderLogs(this, (progress) => this.fetchingLogsProgress = progress)
                    .then((downloaded) => this.fetchingLogsProgress = downloaded);
            },
            getBigChartSeries() {
                if (this.denseLogs.length) {
                    return this.chartStrategy.series.call(this, this.denseLogs);
                }
                return [];
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
                                pan: false, // disabled because of https://github.com/apexcharts/apexcharts.js/issues/3757#issuecomment-1517485503
                                reset: false,
                                customIcons: [
                                    {
                                        icon: '<span class="pe-7s-refresh" style="font-weight: bold"></span>',
                                        index: 2,
                                        title: this.$t('reset chart view'),
                                        click: () => this.$emit('rerender'),
                                    }
                                ]
                            },
                        },
                        animations: {enabled: false},
                        locales,
                        events: {
                            zoomed: (chartContext, {xaxis}) => {
                                this.setTimeRange({afterTimestampMs: xaxis.min, beforeTimestampMs: xaxis.max});
                            },
                            scrolled: debounce((chartContext, {xaxis}) => {
                                this.setTimeRange({afterTimestampMs: xaxis.min, beforeTimestampMs: xaxis.max});
                            }, 200),
                            // click: (event, chartContext, config) => {
                            //     console.log(event, chartContext, config);
                            // },
                        },
                    },
                    title: {style: {fontSize: '20px', fontWeight: 'normal', fontFamily: 'Quicksand'}},
                    legend: {show: true, showForSingleSeries: true, position: 'top', onItemClick: {toggleDataSeries: false}},
                    stroke: {width: 3,/* curve: 'smooth'*/},
                    colors: ['#00d150', '#008ffb', '#ff851b'],
                    dataLabels: {enabled: false},
                    fill: {opacity: 1},
                    markers: {size: 0},
                    xaxis: {
                        type: 'datetime',
                        crosshairs: {show: false},
                        labels: {
                            datetimeUTC: false,
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        followCursor: true,
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

                const chartOptions = this.chartStrategy.chartOptions.call(this);
                const series = this.getBigChartSeries();
                merge(bigChartOptions, chartOptions);
                this.bigChart = new ApexCharts(this.$refs.bigChart, {...bigChartOptions, series});
                this.bigChart.render();
                this.updateChartLocale();
            },
            fetchDenseLogs() {
                const afterTimestamp = DateTime.fromMillis(this.currentMinTimestamp).toSeconds();
                const beforeTimestamp = DateTime.fromMillis(this.currentMaxTimestamp).toSeconds();
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
                        xaxis: {
                            min: this.currentMinTimestamp,
                            max: this.currentMaxTimestamp,
                        },
                        yaxis: this.chartStrategy.yaxes.call(this, this.denseLogs),
                        annotations: {
                            xaxis: this.chartStrategy.getAnnotations?.call(this, this.denseLogs) || [],
                        },
                    };
                    this.bigChart.updateOptions(merge(newChartOptions, this.chartStrategy.chartOptions.call(this)), false, false);
                }
                this.fetchingDenseLogs = false;
            },
            changeChartMode(newMode) {
                this.chartMode = newMode;
                this.rerenderBigChart();
            },
            changeAggregationMethod(newMethod) {
                if (this.availableAggregationStrategies.includes(newMethod)) {
                    this.aggregationMethod = newMethod;
                    this.setTimeRange({
                        afterTimestampMs: this.currentMinTimestamp,
                        beforeTimestampMs: this.currentMaxTimestamp,
                    });
                }
            },
            onMeasurementsDelete() {
                this.hasLogs = false;
                this.bigChart?.destroy();
                this.bigChart = undefined;
            },
            panTime(direction) {
                // const panWindow = {minute: 600_000, hour: 3600_000, day: 86_400_000, month: 28 * 86_400_00,}[this.aggregationMethod];
                const duration = {[`${this.aggregationMethod}s`]: this.aggregationMethod === 'minute' ? 10 : 1};
                const method = direction > 0 ? 'plus' : 'minus';
                this.setTimeRange({
                    afterTimestampMs: DateTime.fromMillis(this.currentMinTimestamp)[method](duration).toMillis(),// + panWindow * direction,
                    beforeTimestampMs: DateTime.fromMillis(this.currentMaxTimestamp)[method](duration).toMillis(),
                });
            },
        },
        computed: {
            dateRange: {
                get() {
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
            minDate() {
                return this.oldestLog ? DateTime.fromSeconds(this.oldestLog.date_timestamp).toJSDate() : null;
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
                    let availableModes = this.channel.config.countersAvailable || defaultModes;
                    const modes = availableModes.filter(mode => modesMap[mode]).map(mode => modesMap[mode]);
                    if (modes.includes('fae') && modes.includes('rae')) {
                        modes.push('fae_rae');
                    }
                    if (availableModes.includes('forwardActiveEnergyBalanced') && availableModes.includes('reverseActiveEnergyBalanced')) {
                        modes.push('fae_rae_vector');
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

    ::v-deep .apexcharts-tooltip {
        background: #f3f3f3;
        border: 1px solid #e3e3e3;
        .apexcharts-tooltip-text-y-label {
            display: none;
        }
    }

    .download-buttons {
        @include on-xs-and-down {
            text-align: center;
        }
    }

    @include on-sm-and-down {
        .history-date-range-picker {
            .justify-content-end {
                justify-content: center;
            }
        }
    }
</style>
