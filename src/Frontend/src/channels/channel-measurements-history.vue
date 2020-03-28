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
                :options="chartOptions"
                ref="bigChart"
                :series="series"></apexchart>
            <apexchart width="100%"
                height="130"
                type="area"
                :options="chartOptions2"
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

    Vue.use(VueApexCharts);

    export default {
        components: {apexchart: VueApexCharts},
        props: ['channel'],
        data: function () {

            var options = {
                chart: {
                    id: 'chart2vue',
                    type: 'line',
                    // height: 230,
                    toolbar: {
                        autoSelected: 'pan',
                        show: false
                    },
                    animations: {
                        enabled: true,
                    }
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
                    type: 'datetime'
                }
            };

            // var chart = new ApexCharts(document.querySelector("#chart-line2"), options);
            // chart.render();

            var optionsLine = {
                chart: {
                    id: 'chart1vue',
                    height: 130,
                    type: 'area',
                    brush: {
                        target: 'chart2vue',
                        enabled: true,
                        autoScaleYaxis: false,
                    },
                    // selection: {
                    //     enabled: true,
                    // xaxis: {
                    //     min: 1484505000000,
                    //     max: 1484764200000
                    // }
                    // },
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
                    tooltip: {
                        enabled: false
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
                chartOptions: options,
                chartOptions2: optionsLine,
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
                    this.series = [{data: series}];
                    // this.series2 = [{data: series}];
                    this.chartOptions2 = {
                        ...this.chartOptions2,
                        chart: {
                            ...this.chartOptions2.chart,
                            selection: {
                                enabled: true,
                                xaxis: {
                                    max: series[series.length - 1][0],
                                    min: series[Math.max(0, series.length - 30)][0]
                                },
                            }
                        },
                    };
                    this.chartOptions = {
                        ...this.chartOptions,
                        chart: {
                            ...this.chartOptions.chart,
                            events: {
                                updated: debounce((chartContext, {config}) => {
                                    if (config.xaxis.min && config.xaxis.max) {
                                        this.fetchPreciseLogs(config.xaxis.min, config.xaxis.max);
                                    }
                                }, 500),
                            }
                        },
                    };
                });
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
                    const newItems = logItems.map((item) => [+item.date_timestamp * 1000, +item.temperature]);
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

                    this.$refs.bigChart.updateSeries([{data: newSeries}], true);
                    this.$refs.bigChart.updateOptions({
                        xaxis: {
                            min: this.currentMinTimestamp,
                            max: this.currentMaxTimestamp
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
        }
    };
</script>
