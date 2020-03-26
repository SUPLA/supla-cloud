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
        <apexchart width="100%"
            type="line"
            :options="chartOptions"
            :series="series"></apexchart>
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

    Vue.use(VueApexCharts);

    export default {
        components: {apexchart: VueApexCharts},
        props: ['channel'],
        data: function () {
            return {
                deleteConfirm: false,
                chartOptions: {
                    chart: {
                        id: 'vuechart-example'
                    },
                    xaxis: {
                        categories: [1991, 1992, 1993, 1994, 1995, 1996, 1997, 1998]
                    }
                },
                series: [{
                    name: 'series-1',
                    data: [30, 40, 35, 50, 49, 60, 70, 91]
                }]
            };
        },
        methods: {
            deleteMeasurements() {
                this.deleteConfirm = false;
                this.$http.delete('channels/' + this.channel.id + '/measurement-logs')
                    .then(() => successNotification(this.$t('Success'), this.$t('The measurement history has been deleted.')));
            },
        }
    };
</script>
