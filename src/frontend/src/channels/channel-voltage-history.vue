<template>
    <div>
        <loading-cover :loading="!logs">
            <div class="container"
                v-if="logs">
                WYKRES
                <div class="voltage-logs-list">
                    <div v-for="log in logs"
                        :key="log.date_timestamp"
                        class="voltage-log panel panel-default">
                        <div class="panel-body">
                            <h4>{{ formatTimestamp(log.date_timestamp) }}</h4>
                            <div class="row">
                                <div class="col-md-4">
                                    <h4>{{ $t('Violation info') }}</h4>
                                    <dl class="dl-grid">
                                        <dt>{{ $t('Phase number') }}</dt>
                                        <dd>{{ log.phaseNo }}</dd>
                                        <dt>{{ $t('Measurement window') }}</dt>
                                        <dd>{{ log.measurementTimeSec }} {{ $t('sec.') }}</dd>
                                        <dt>{{ $t('Average voltage') }}</dt>
                                        <dd>{{ log.avgVoltage }} V</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <h4>
                                        <span v-if="log.countBelow > 0"
                                            class="violation-indicator text-danger">&bull;</span>
                                        {{ $t('Below threshold') }}
                                    </h4>
                                    <dl class="dl-grid">
                                        <dt>{{ $t('Violations count') }}</dt>
                                        <dd>{{ log.countBelow }}</dd>
                                        <dt>{{ $t('Total time') }}</dt>
                                        <dd>{{ log.secBelow }} {{ $t('sec.') }}</dd>
                                        <dt>{{ $t('Longest violation') }}</dt>
                                        <dd>{{ log.maxSecBelow }} {{ $t('sec.') }}</dd>
                                        <dt>{{ $t('Minimum voltage') }}</dt>
                                        <dd :class="{'text-danger text-bold': log.countBelow > 0}">{{ log.minVoltage }} V</dd>
                                    </dl>
                                </div>
                                <div class="col-md-4 col-sm-6">
                                    <h4>
                                        <span v-if="log.countAbove > 0"
                                            class="violation-indicator text-danger">&bull;</span>
                                        {{ $t('Above threshold') }}
                                    </h4>
                                    <dl class="dl-grid">
                                        <dt>{{ $t('Violations count') }}</dt>
                                        <dd>{{ log.countAbove }}</dd>
                                        <dt>{{ $t('Total time') }}</dt>
                                        <dd>{{ log.secAbove }} {{ $t('sec.') }}</dd>
                                        <dt>{{ $t('Longest violation') }}</dt>
                                        <dd>{{ log.maxSecAbove }} {{ $t('sec.') }}</dd>
                                        <dt>{{ $t('Maximum voltage') }}</dt>
                                        <dd :class="{'text-danger text-bold': log.countAbove > 0}">{{ log.maxVoltage }} V</dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <empty-list-placeholder v-if="logs && logs.length === 0"></empty-list-placeholder>
        </loading-cover>
    </div>
</template>

<script>
    import moment from "moment";

    export default {
        props: {
            channel: Object,
        },
        data() {
            return {
                logs: undefined,
            };
        },
        beforeMount() {
            this.$http.get(`channels/${this.channel.id}/measurement-logs?logsType=voltage&limit=100`)
                .then(({body: logItems, headers}) => {
                    this.logs = logItems;
                });
        },
        methods: {
            formatTimestamp(timestamp) {
                return moment.unix(timestamp).format('LLLL');
            },
        }
    };
</script>

<style lang="scss">
    @import '../styles/variables';

    .dl-grid {
        width: 100%;
        display: grid;
        grid-template-columns: max-content auto;
        dt, dd {
            display: inline-block;
            border-bottom: 1px solid $supla-grey-light;
            padding: 2px 0;
            &:last-of-type {
                border-bottom: 0;
            }
        }
        dt {
            padding-right: 10px;
            //text-align: right;
            &:after {
                content: ':';
            }
        }
    }

    .violation-indicator {
        font-size: 2em;
        line-height: 0.5em;
        vertical-align: middle;
    }
</style>
