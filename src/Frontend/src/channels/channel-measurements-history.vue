<template>
    <div class="container">
        <div class="text-right form-group">
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

        <div ref="bigChart"></div>
        <div ref="smallChart"></div>

        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteMeasurements()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete the entire measurement history saved for this channel?')">
        </modal-confirm>
    </div>
</template>

<script>
    import {successNotification} from "../common/notifier";
    import {debounce} from "lodash";
    import {channelTitle} from "../common/filters";
    import ApexCharts from "apexcharts";

    window.ApexCharts = ApexCharts;

    const pl = require("apexcharts/dist/locales/pl.json");
    const en = require("apexcharts/dist/locales/en.json");

    export default {
        props: ['channel'],
        data: function () {
            return {
                deleteConfirm: false,
                currentMinTimestamp: undefined,
                currentMaxTimestamp: undefined,
                sparseLogs: undefined,
                bigChart: undefined,
                smallChart: undefined,
            };
        },
        mounted() {
            this.fetchAllLogs();
        },
        methods: {
            fetchAllLogs() {
                this.$http.get(`channels/${this.channel.id}/measurement-logs?sparse=500&order=ASC`).then(({body: logItems}) => {
                    this.sparseLogs = logItems;

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
                        this.fetchPreciseLogs();
                    }, 500);

                    const temperatureSeries = this.sparseLogs.map((item) => [+item.date_timestamp * 1000, +item.temperature]);
                    const humiditySeries = this.sparseLogs.map((item) => [+item.date_timestamp * 1000, +item.humidity]);

                    var bigChartOptions = {
                        chart: {
                            id: 'chart2vue',
                            type: 'line',
                            height: 230,
                            toolbar: {
                                // autoSelected: 'pan',
                                show: true,
                                tools: {
                                    download: true,
                                    selection: false,
                                    zoom: true,
                                    zoomin: false,
                                    zoomout: false,
                                    pan: true,
                                    reset: false,
                                    customIcons: [{
                                        icon: '<span class="pe-7s-refresh" style="font-weight: bold"></span>',
                                        index: 2,
                                        title: 'show default view',
                                        click: () => {
                                            this.currentMaxTimestamp = temperatureSeries[temperatureSeries.length - 1][0];
                                            this.currentMinTimestamp = temperatureSeries[Math.max(0, temperatureSeries.length - 30)][0];
                                            updateSmallChart();
                                        }
                                    }]
                                },
                            },

                            animations: {
                                enabled: true,
                            },
                            locales: [pl, en],
                            defaultLocale: 'pl',
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
                                // scrolled: updateSmallChart,
                                // click: (event, chartContext, config) => {
                                //     console.log(event, chartContext, config);
                                // },
                            },
                        },
                        title: {
                            text: channelTitle(this.channel, this),
                            style: {
                                fontSize: '20px',
                                fontWeight: 'normal',
                                fontFamily: 'Quicksand',
                            },
                        },
                        legend: {
                            show: true,
                            showForSingleSeries: true,
                            position: 'top',
                        },
                        // colors: ['#546e7a'],
                        stroke: {
                            width: 3
                        },
                        dataLabels: {
                            enabled: false
                        },
                        fill: {
                            opacity: 1,
                        },
                        markers: {
                            size: 0
                        },
                        xaxis: {
                            type: 'datetime',
                            // type: 'numeric',
                            // tickAmount: 8,
                            labels: {
                                datetimeUTC: false,
                                // formatter: (value, timestamp) => moment.unix(value / 1000).format('LT L'),
                            }
                        },
                        tooltip: {
                            x: {
                                formatter: (value, timestamp) => moment.unix(value / 1000).format('LT D MMM'),
                            }
                        },
                        yaxis: [
                            {
                                seriesName: channelTitle(this.channel, this) + ' (temperatura)',
                                title: {
                                    text: "Temperatura"
                                },
                                labels: {
                                    formatter: (v) => `${v}°C`
                                }
                            },
                            {
                                seriesName: channelTitle(this.channel, this) + ' (wilgotność)',
                                opposite: true,
                                title: {
                                    text: "Wilgotność"
                                },
                                labels: {
                                    formatter: (v) => `${v}%`
                                }
                            }
                        ],
                    };

                    var smallChartOptions = {
                        chart: {
                            id: 'chart1vue',
                            height: 130,
                            type: 'line',
                            brush: {
                                target: 'chart2vue',
                                enabled: true,
                                autoScaleYaxis: false,
                            },
                            locales: [pl, en],
                            defaultLocale: 'pl',
                            selection: {
                                enabled: true,
                                xaxis: {
                                    max: temperatureSeries[temperatureSeries.length - 1][0],
                                    min: temperatureSeries[Math.max(0, temperatureSeries.length - 30)][0]
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
                            // selection: {
                            //     enabled: true,
                            // xaxis: {
                            //     min: 1484505000000,
                            //     max: 1484764200000
                            // }
                            // },
                        },
                        // colors: ['#008ffb'],
                        // fill: {
                        //     type: 'gradient',
                        //     gradient: {
                        //         opacityFrom: 0.91,
                        //         opacityTo: 0.1,
                        //     }
                        // },
                        legend: {
                            show: false,
                        },
                        xaxis: {
                            type: 'datetime',
                            tooltip: {
                                enabled: false
                            },
                            labels: {
                                datetimeUTC: false,
                            }
                        },
                        yaxis: {
                            labels: {show: false}
                        }
                    };

                    // const series = logItems.map((item) => [moment.unix(+item.date_timestamp).format(), +item.temperature]);
                    const series = [
                        {name: channelTitle(this.channel, this) + ' (temperatura)', data: temperatureSeries},
                        {name: channelTitle(this.channel, this) + ' (wilgotność)', data: humiditySeries},
                    ];

                    this.bigChart = new ApexCharts(this.$refs.bigChart, {...bigChartOptions, series});
                    this.smallChart = new ApexCharts(this.$refs.smallChart, {...smallChartOptions, series});
                    this.bigChart.render();
                    this.smallChart.render();

                });
            },
            formatTimestamp(timestamp) {
                return moment.unix(timestamp / 1000).format('LT D MMM');

            },
            fillGaps(logs, expectedInterval) {
                const defaultLog = {temperature: null};
                if (logs[0].humidity !== undefined) {
                    defaultLog.humidity = null;
                }
                let lastTimestamp = 0;
                const filledLogs = [];
                for (const log of logs) {
                    const currentTimestamp = +log.date_timestamp;
                    if (lastTimestamp && (currentTimestamp - lastTimestamp) > expectedInterval * 1.5) {
                        for (let missingTimestamp = lastTimestamp + expectedInterval; missingTimestamp < currentTimestamp; missingTimestamp += expectedInterval) {
                            filledLogs.push({...defaultLog, date_timestamp: missingTimestamp});
                        }
                    }
                    filledLogs.push(log);
                    lastTimestamp = currentTimestamp;
                }
                return filledLogs;
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
                        const sparseLogsNextTimestamp = +sparseLogs[sparseLogsIndex].date_timestamp;
                        const denseLogsNextTimestamp = +denseLogs[denseLogsIndex].date_timestamp;
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
            fetchPreciseLogs() {
                this.$http.get(`channels/${this.channel.id}/measurement-logs?` + $.param({
                    sparse: 200,
                    afterTimestamp: Math.floor(this.currentMinTimestamp / 1000),
                    beforeTimestamp: Math.ceil(this.currentMaxTimestamp / 1000),
                    order: 'ASC',
                })).then(({body: logItems}) => {
                    const expectedInterval = Math.max(600000, Math.ceil(this.visibleRange / 200));
                    logItems = this.fillGaps(logItems, expectedInterval);
                    const allLogs = this.mergeLogs(this.sparseLogs, logItems);
                    const temperatureSeries = allLogs.map((item) => [+item.date_timestamp * 1000, +item.temperature]);
                    const humiditySeries = allLogs.map((item) => [+item.date_timestamp * 1000, +item.humidity]);
                    this.bigChart.updateSeries([
                        {name: channelTitle(this.channel, this) + ' (temperatura)', data: temperatureSeries},
                        {name: channelTitle(this.channel, this) + ' (wilgotność)', data: humiditySeries},
                    ], true);
                    this.bigChart.updateOptions({
                        xaxis: {
                            min: this.currentMinTimestamp,
                            max: this.currentMaxTimestamp,
                        },
                    }, false, false);
                });
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
        },
    };
</script>
