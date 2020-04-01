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
        <div v-if="series">
            <apexchart width="100%"
                height="330"
                type="line"
                :options="bigChartOptions"
                ref="bigChart"
                :series="series"></apexchart>
            <apexchart width="100%"
                height="130"
                type="area"
                ref="smallChart"
                :options="smallChartOptions"
                :series="series"></apexchart>
        </div>
        <modal-confirm v-if="deleteConfirm"
            class="modal-warning"
            @confirm="deleteMeasurements()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete the entire measurement history saved for this channel?')">
        </modal-confirm>
    </div>
</template>

<script>
    import Vue from "vue";
    import VueApexCharts from 'vue-apexcharts';
    import {successNotification} from "../common/notifier";
    import {debounce} from "lodash";
    import {channelTitle} from "../common/filters";

    Vue.use(VueApexCharts);
    const pl = require("apexcharts/dist/locales/pl.json")

    export default {
        components: {apexchart: VueApexCharts},
        props: ['channel'],
        data: function () {

            var bigChartOptions = {
                chart: {
                    id: 'chart2vue',
                    type: 'line',
                    // height: 230,
                    toolbar: {
                        // autoSelected: 'pan',
                        show: true,
                        tools: {
                            download: true,
                            selection: false,
                            zoom: true,
                            zoomin: true,
                            zoomout: true,
                            pan: false,
                            reset: true,
                            // customIcons: []
                        },
                    },

                    animations: {
                        enabled: true,
                    },
                    locales: [pl],
                    defaultLocale: 'pl',
                },
                title: {
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
                colors: ['#546e7a'],
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
                }
            };

            var smallChartOptions = {
                chart: {
                    id: 'chart1vue',
                    height: 130,
                    type: 'area',
                    brush: {
                        target: 'chart2vue',
                        enabled: true,
                        autoScaleYaxis: false,
                    },
                    selection: {
                        enabled: true,
                        // xaxis: {
                        //     min: 1484505000000,
                        //     max: 1484764200000
                        // }
                    },
                },
                colors: ['#008ffb'],
                fill: {
                    type: 'gradient',
                    gradient: {
                        opacityFrom: 0.91,
                        opacityTo: 0.1,
                    }
                },
                xaxis: {
                    type: 'datetime',
                    // type: 'numeric',
                    tooltip: {
                        enabled: false
                    },
                    // tickAmount: 12,
                    labels: {
                        datetimeUTC: false,
                        // hideOverlappingLabels: true,
                        // formatter: (value, timestamp) => moment.unix(value / 1000).format('LT D MMM'),
                    }
                },
                yaxis: {
                    tickAmount: 2
                }
            };

            // var chartLine = new ApexCharts(document.querySelector("#chart-line"), optionsLine);
            // chartLine.render();

            return {
                deleteConfirm: false,
                currentMinTimestamp: undefined,
                currentMaxTimestamp: undefined,
                bigChartOptions: bigChartOptions,
                smallChartOptions: smallChartOptions,
                series: undefined,
                // series2: undefined,
            };
        },
        mounted() {
            this.fetchAllLogs();
        },
        methods: {
            fetchAllLogs() {
                this.$http.get(`channels/${this.channel.id}/measurement-logs?sparse=500&order=ASC`).then(({body: logItems}) => {
                    const series = logItems.map((item) => [+item.date_timestamp * 1000, +item.temperature]);
                    // const series = logItems.map((item) => [moment.unix(+item.date_timestamp).format(), +item.temperature]);
                    this.series = [{name: channelTitle(this.channel, this) + ' (temperatura)', data: series}];
                    // this.series2 = [{data: series}];
                    this.smallChartOptions = {
                        ...this.smallChartOptions,
                        chart: {
                            ...this.smallChartOptions.chart,
                            selection: {
                                enabled: true,
                                xaxis: {
                                    max: series[series.length - 1][0],
                                    min: series[Math.max(0, series.length - 30)][0]
                                },
                            },
                        },
                    };
                    this.bigChartOptions = {
                        ...this.bigChartOptions,
                        chart: {
                            ...this.bigChartOptions.chart,
                            events: {
                                updated: debounce((chartContext, {config}) => {
                                    if (config.xaxis.min && config.xaxis.max) {
                                        this.fetchPreciseLogs(config.xaxis.min, config.xaxis.max);
                                    }
                                }, 500),
                                click: (event, chartContext, config) => {
                                    console.log(event, chartContext, config);
                                },
                            },
                        },
                        title: {
                            ...this.bigChartOptions.title,
                            text: channelTitle(this.channel, this),
                        }
                    };
                });
            },
            formatTimestamp(timestamp) {
                return moment.unix(timestamp / 1000).format('LT D MMM');

            },
            fetchPreciseLogs(afterTimestamp, beforeTimestamp) {
                if (afterTimestamp === this.currentMinTimestamp && beforeTimestamp === this.currentMaxTimestamp) {
                    return;
                }

                this.currentMinTimestamp = afterTimestamp;
                this.currentMaxTimestamp = beforeTimestamp;
                this.$http.get(`channels/${this.channel.id}/measurement-logs?` + $.param({
                    sparse: 200,
                    afterTimestamp: Math.floor(afterTimestamp / 1000),
                    beforeTimestamp: Math.ceil(beforeTimestamp / 1000),
                    order: 'ASC',
                })).then(({body: logItems}) => {
                    const series = this.series[0].data;
                    let newItems = logItems.map((item) => [+item.date_timestamp * 1000, +item.temperature]);
                    // const newItems = logItems.map((item) => [moment.unix(+item.date_timestamp).format(), +item.temperature]);

                    const expectedInterval = Math.max(600000, Math.ceil(this.visibleRange / 200));
                    const actualInterval = newItems[1][0] - newItems[0][0];
                    console.log(this.formatTimestamp(afterTimestamp), this.formatTimestamp(beforeTimestamp), this.visibleRange, expectedInterval, actualInterval);
                    const filledNewItems = [];
                    let lastTimestamp = 0;
                    for (const serie of newItems) {
                        const currentTimestamp = serie[0];
                        if (lastTimestamp && (currentTimestamp - lastTimestamp) > expectedInterval * 2) {
                            for (let missingTimestamp = lastTimestamp + expectedInterval; missingTimestamp < currentTimestamp; missingTimestamp += expectedInterval) {
                                filledNewItems.push([missingTimestamp, null]);
                            }
                        }
                        filledNewItems.push(serie);
                        lastTimestamp = currentTimestamp;
                    }
                    newItems = filledNewItems;

                    let seriesIndex = 0;
                    let newItemsIndex = 0;
                    const newSeries = [];
                    while (newItemsIndex < newItems.length || seriesIndex < series.length) {
                        if (newItemsIndex >= newItems.length) {
                            newSeries.push(series[seriesIndex++]);
                        } else if (seriesIndex >= series.length) {
                            newSeries.push(newItems[newItemsIndex++]);
                        } else {
                            const seriesTimestamp = series[seriesIndex][0];
                            const newItemsTimestamp = newItems[newItemsIndex][0];
                            if (seriesTimestamp < newItemsTimestamp) {
                                newSeries.push(series[seriesIndex++]);
                            } else if (seriesTimestamp > newItemsTimestamp) {
                                newSeries.push(newItems[newItemsIndex++]);
                            } else {
                                newSeries.push(newItems[newItemsIndex]);
                                ++newItemsIndex;
                                ++seriesIndex;
                            }
                        }
                    }

                    // console.log(newSeries);

                    // this.series2 = [{data: newSeries}];

                    this.$refs.bigChart.updateSeries([{name: channelTitle(this.channel, this) + ' (temperatura)', data: newSeries}], true);
                    this.$refs.bigChart.updateOptions({
                        xaxis: {
                            min: this.currentMinTimestamp,
                            max: this.currentMaxTimestamp,
                        }
                    }, false, false);

                    // setTimeout(() => {
                    //     this.chartOptions = {
                    //         ...this.chartOptions,
                    //         xaxis: {
                    //             ...this.chartOptions.xaxis,
                    //             min: this.currentMinTimestamp,
                    //             max: this.currentMaxTimestamp,
                    //         }
                    //     };
                    // });

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
