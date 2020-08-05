<template>
    <loading-cover :loading="hasLogs === undefined || fetchingDenseLogs">
        <div class="container">
            <div :class="'form-group text-' + (sparseLogs.length ? 'right' : 'center')"
                v-if="hasLogs">
                <div class="button-container">
                    <a :href="`/api/channels/${channel.id}/measurement-logs-csv?` | withDownloadAccessToken"
                        class="btn btn-default">{{ $t('Download the history of measurement') }}</a>
                    <button @click="deleteConfirm = true"
                        type="button"
                        class="btn btn-red">
                        <i class="pe-7s-trash"></i>
                        {{ $t('Delete measurement history') }}
                    </button>
                </div>
            </div>

            <div v-if="supportsChart">
                <div ref="bigChart"></div>
                <div ref="smallChart"></div>
            </div>

            <modal-confirm v-if="deleteConfirm"
                class="modal-warning"
                @confirm="deleteMeasurements()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure you want to delete the entire measurement history saved for this channel?')">
            </modal-confirm>

            <empty-list-placeholder v-if="hasLogs === false"></empty-list-placeholder>
        </div>
    </loading-cover>
</template>

<script>
    import {successNotification} from "../common/notifier";
    import {debounce} from "lodash";
    import {channelTitle} from "../common/filters";
    import ApexCharts from "apexcharts";

    window.ApexCharts = ApexCharts;

    const locales = [
        require("apexcharts/dist/locales/en.json"),
        require("apexcharts/dist/locales/pl.json"),
        // require("apexcharts/dist/locales/cs.json"),
        require("apexcharts/dist/locales/de.json"),
        require("apexcharts/dist/locales/es.json"),
        require("apexcharts/dist/locales/el.json"),
        require("apexcharts/dist/locales/fr.json"),
        require("apexcharts/dist/locales/it.json"),
        // require("apexcharts/dist/locales/lt.json"),
        require("apexcharts/dist/locales/nl.json"),
        // require("apexcharts/dist/locales/nb.json"),
        // require("apexcharts/dist/locales/pt.json"),
        require("apexcharts/dist/locales/ru.json"),
        // require("apexcharts/dist/locales/sk.json"),
        // require("apexcharts/dist/locales/sl.json"),
    ];

    const CHART_TYPES = {
        THERMOMETER: {
            chartType: 'line',
            series: function (allLogs) {
                const temperatureSeries = allLogs.map((item) => [item.date_timestamp * 1000, item.temperature]);
                return [{name: `${channelTitle(this.channel, this)} (${this.$t('Temperature')})`, data: temperatureSeries}];
            },
            fixLog: (log) => {
                if (log.temperature !== undefined && log.temperature !== null) {
                    log.temperature = log.temperature >= -273 ? +log.temperature : null;
                }
                return log;
            },
            adjustLogs: (logs) => logs,
            interpolateGaps: (logs) => logs,
            yaxes: function (logs) {
                const temperatures = logs.map(log => log.temperature).filter(t => t !== null);
                return [
                    {
                        seriesName: `${channelTitle(this.channel, this)} (${this.$t('Temperature')})`,
                        title: {text: this.$t("Temperature")},
                        labels: {formatter: (v) => `${(+v).toFixed(2)}°C`},
                        min: Math.floor(Math.min.apply(this, temperatures)),
                        max: Math.ceil(Math.max.apply(this, temperatures)),
                    }
                ];
            },
            emptyLog: () => ({date_timestamp: null, temperature: null}),
        },
        HUMIDITYANDTEMPERATURE: {
            chartType: 'line',
            series: function (allLogs) {
                const temperatureSeries = allLogs.map((item) => [item.date_timestamp * 1000, item.temperature]);
                const humiditySeries = allLogs.map((item) => [item.date_timestamp * 1000, item.humidity]);
                return [
                    {name: `${channelTitle(this.channel, this)} (${this.$t('Temperature')})`, data: temperatureSeries},
                    {name: `${channelTitle(this.channel, this)} (${this.$t('Humidity')})`, data: humiditySeries},
                ];
            },
            fixLog: (log) => {
                if (log.temperature !== undefined && log.temperature !== null) {
                    log.temperature = log.temperature >= -273 ? +log.temperature : null;
                }
                if (log.humidity !== undefined && log.humidity !== null) {
                    log.humidity = log.humidity >= 0 ? +log.humidity : null;
                }
                return log;
            },
            adjustLogs: (logs) => logs,
            interpolateGaps: (logs) => logs,
            yaxes: function (logs) {
                const temperatures = logs.map(log => log.temperature).filter(t => t !== null);
                const humidities = logs.map(log => log.humidity).filter(h => h !== null);
                return [
                    {
                        seriesName: `${channelTitle(this.channel, this)} (${this.$t('Temperature')})`,
                        title: {text: this.$t("Temperature")},
                        labels: {formatter: (v) => `${(+v).toFixed(2)}°C`},
                        min: Math.floor(Math.min.apply(this, temperatures)),
                        max: Math.ceil(Math.max.apply(this, temperatures)),
                    },
                    {
                        seriesName: `${channelTitle(this.channel, this)} (${this.$t('Humidity')})`,
                        opposite: true,
                        title: {text: this.$t('Humidity')},
                        labels: {formatter: (v) => `${(+v).toFixed(1)}%`},
                        min: Math.floor(Math.max(0, Math.min.apply(this, humidities))),
                        max: Math.ceil(Math.min(100, Math.max.apply(this, humidities) + 1)),
                    }
                ];
            },
            emptyLog: () => ({date_timestamp: null, temperature: null, humidity: null}),
        },
        GASMETER: {
            chartType: 'bar',
            series: function (allLogs) {
                const calculatedValues = allLogs.map((item) => [item.date_timestamp * 1000, item.calculated_value]);
                return [{name: `${channelTitle(this.channel, this)} (${this.$t('Calculated value')})`, data: calculatedValues}];
            },
            fixLog: (log) => {
                if (log.calculated_value !== undefined && log.calculated_value !== null) {
                    log.calculated_value = +log.calculated_value;
                }
                return log;
            },
            adjustLogs: (logs) => {
                let previousLog = logs[0];
                const adjustedLogs = [];
                for (let i = 1; i < logs.length; i++) {
                    const log = {...logs[i]};
                    log.calculated_value -= previousLog.calculated_value;
                    log.counter -= previousLog.counter;
                    adjustedLogs.push(log);
                    previousLog = logs[i];
                }
                return adjustedLogs;
            },
            interpolateGaps: (logs) => {
                let firstNullLog = undefined;
                for (let lastNonNullLog = 0; lastNonNullLog < logs.length; lastNonNullLog++) {
                    if (logs[lastNonNullLog].calculated_value === null && firstNullLog === undefined) {
                        firstNullLog = lastNonNullLog;
                    } else if (logs[lastNonNullLog].calculated_value !== null && firstNullLog !== undefined) {
                        const logsToFill = lastNonNullLog - firstNullLog + 1;
                        const normalizedValue = logs[lastNonNullLog].calculated_value / logsToFill;
                        for (let i = firstNullLog; i <= lastNonNullLog; i++) {
                            logs[i].calculated_value = normalizedValue;
                            // logs[i].interpolated = true; may be useful
                        }
                        firstNullLog = undefined;
                    }
                }
                return logs;
            },
            yaxes: function (logs) {
                const values = logs.map(log => log.calculated_value).filter(t => t !== null);
                return [
                    {
                        seriesName: `${channelTitle(this.channel, this)} (${this.$t('Calculated value')})`,
                        title: {text: this.$t("Calculated value")},
                        labels: {formatter: (v) => `${(+v).toFixed(2)} m³`},
                        min: Math.floor(Math.min.apply(this, values)),
                        max: Math.ceil(Math.max.apply(this, values)),
                    }
                ];
            },
            emptyLog: () => ({date_timestamp: null, counter: null, calculated_value: null}),
        },
    };

    export default {
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
            };
        },
        mounted() {
            if (this.supportsChart) {
                this.chartStrategy = CHART_TYPES[this.channel.function.name];
                this.fetchSparseLogs().then((logs) => {
                    logs = this.adjustLogs(this.fixLogs(logs));
                    this.hasLogs = logs.length > 0;
                    if (logs.length > 1) {
                        const minTimestamp = logs[0].date_timestamp * 1000;
                        const maxTimestamp = logs[logs.length - 1].date_timestamp * 1000;
                        const expectedInterval = Math.max(600000, Math.ceil((maxTimestamp - minTimestamp) / 500));
                        this.sparseLogs = this.fillGaps(logs, expectedInterval);
                        this.renderCharts();
                    }
                });
            } else {
                this.hasLogs = true;
            }
        },
        methods: {
            getChartSeries() {
                const series = [];
                if (this.sparseLogs.length) {
                    const allLogs = this.mergeLogs(this.sparseLogs, this.denseLogs);
                    return this.chartStrategy.series.call(this, allLogs);
                }
                return series;
            },
            fetchSparseLogs() {
                return this.$http.get(`channels/${this.channel.id}/measurement-logs?sparse=500&order=ASC`)
                    .then(({body: logItems, headers}) => {
                        if (logItems.length > 0) {
                            const maxTimestamp = headers.get('X-Max-Timestamp');
                            if (maxTimestamp && maxTimestamp > logItems[logItems.length - 1].date_timestamp) {
                                logItems.push({...logItems[logItems.length - 1], date_timestamp: maxTimestamp});
                            }
                            const minTimestamp = headers.get('X-Min-Timestamp');
                            if (minTimestamp && minTimestamp < logItems[0].date_timestamp) {
                                logItems.unshift({...logItems[0], date_timestamp: minTimestamp});
                            }
                        }
                        return logItems;
                    });
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
                    this.fetchDenseLogs().then(() => this.rerenderCharts());
                }, 500);

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
                        animations: {enabled: true},
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
                    colors: ['#00d150', '#008ffb'],
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
                        x: {
                            formatter: (value) => moment.unix(value / 1000).format('LT D MMM'),
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
                        colors: ['#00d150', '#008ffb'],
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
                    legend: {show: false},
                    xaxis: {
                        type: 'datetime',
                        tooltip: {enabled: false},
                        labels: {datetimeUTC: false}
                    },
                    yaxis: {labels: {show: false}}
                };

                const series = this.getChartSeries();
                this.bigChart = new ApexCharts(this.$refs.bigChart, {...bigChartOptions, series});
                this.smallChart = new ApexCharts(this.$refs.smallChart, {...smallChartOptions, series});
                this.bigChart.render();
                this.smallChart.render();
                this.updateChartLocale();
            },
            fixLogs(logs) {
                if (!logs || !logs.length) {
                    return logs;
                }
                return logs.map((log) => {
                    log.date_timestamp = +log.date_timestamp;
                    return this.chartStrategy.fixLog(log);
                });
            },
            adjustLogs(logs) {
                if (!logs || !logs.length) {
                    return logs;
                }
                return this.chartStrategy.adjustLogs(logs);
            },
            fillGaps(logs, expectedInterval) {
                if (logs.length < 2) {
                    return logs;
                }
                const defaultLog = this.chartStrategy.emptyLog();
                expectedInterval /= 1000;
                let lastTimestamp = 0;
                const filledLogs = [];
                for (const log of logs) {
                    const currentTimestamp = log.date_timestamp;
                    if (lastTimestamp && (currentTimestamp - lastTimestamp) > expectedInterval * 1.5) {
                        for (let missingTimestamp = lastTimestamp + expectedInterval; missingTimestamp < currentTimestamp; missingTimestamp += expectedInterval) {
                            filledLogs.push({...defaultLog, date_timestamp: missingTimestamp});
                        }
                    }
                    filledLogs.push(log);
                    lastTimestamp = currentTimestamp;
                }
                return this.chartStrategy.interpolateGaps(filledLogs);
            },
            mergeLogs(sparseLogs, denseLogs) {
                let sparseLogsIndex = 0;
                let denseLogsIndex = 0;
                const mergedLogs = [];
                while (denseLogsIndex < denseLogs.length || sparseLogsIndex < sparseLogs.length) {
                    if (denseLogsIndex >= denseLogs.length) {
                        mergedLogs.push(sparseLogs[sparseLogsIndex++]);
                    } else if (sparseLogsIndex >= sparseLogs.length) {
                        mergedLogs.push(denseLogs[denseLogsIndex++]);
                    } else {
                        const sparseLogsNextTimestamp = sparseLogs[sparseLogsIndex].date_timestamp;
                        const denseLogsNextTimestamp = denseLogs[denseLogsIndex].date_timestamp;
                        if (sparseLogsNextTimestamp < denseLogsNextTimestamp) {
                            mergedLogs.push(sparseLogs[sparseLogsIndex++]);
                        } else if (sparseLogsNextTimestamp > denseLogsNextTimestamp) {
                            mergedLogs.push(denseLogs[denseLogsIndex++]);
                        } else {
                            mergedLogs.push(denseLogs[denseLogsIndex]);
                            ++denseLogsIndex;
                            ++sparseLogsIndex;
                        }
                    }
                }
                return mergedLogs;
            },
            fetchDenseLogs() {
                this.fetchingDenseLogs = true;
                return this.$http.get(`channels/${this.channel.id}/measurement-logs?` + $.param({
                    sparse: 200,
                    afterTimestamp: Math.floor(this.currentMinTimestamp / 1000) - 1,
                    beforeTimestamp: Math.ceil(this.currentMaxTimestamp / 1000) + 1,
                    order: 'ASC',
                }))
                    .then(({body: logItems}) => {
                        const expectedInterval = Math.max(600000, Math.ceil(this.visibleRange / 200));
                        return this.denseLogs = this.fillGaps(this.adjustLogs(this.fixLogs(logItems)), expectedInterval);
                    })
                    .finally(() => this.fetchingDenseLogs = false);
            },
            updateChartLocale() {
                const availableLocales = locales.map((l) => l.name);
                const locale = availableLocales.includes(this.$i18n.locale) ? this.$i18n.locale : 'en';
                this.smallChart.setLocale(locale);
                this.bigChart.setLocale(locale);
                this.bigChart.updateOptions({
                    title: {text: channelTitle(this.channel, this)},
                });
                this.rerenderCharts();
            },
            rerenderCharts() {
                const series = this.getChartSeries();
                this.bigChart.updateSeries(series, true);
                this.bigChart.updateOptions({
                    xaxis: {
                        min: this.currentMinTimestamp,
                        max: this.currentMaxTimestamp,
                    },
                    yaxis: this.chartStrategy.yaxes.call(this, this.denseLogs),
                }, false, false);
            },
            deleteMeasurements() {
                this.deleteConfirm = false;
                this.$http.delete('channels/' + this.channel.id + '/measurement-logs')
                    .then(() => successNotification(this.$t('Success'), this.$t('The measurement history has been deleted.')));
            },
        },
        computed: {
            visibleRange() {
                return this.currentMaxTimestamp && (this.currentMaxTimestamp - this.currentMinTimestamp);
            },
            supportsChart() {
                return this.channel && CHART_TYPES[this.channel.function.name];
            },
        },
        watch: {
            '$i18n.locale'() {
                this.updateChartLocale();
            }
        }
    };
</script>
