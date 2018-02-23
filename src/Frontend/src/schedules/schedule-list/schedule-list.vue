<template>
    <div class="row">
        <div class="col-xs-12">
            <loading-cover :loading="loading"
                class="schedule-list-wrapper">
                <vuetable
                    :api-url="'web-api' + (channelId ? '/channels/' + channelId : '') + '/schedules?include=channel,function,type,iodevice,location,closestExecutions'"
                    data-path=""
                    pagination-path=""
                    :no-data-template="$t('Empty!')"
                    :css="bootstrapStyles"
                    :fields="columns"
                    @vuetable:loading="loading = true"
                    @vuetable:loaded="loading = false"
                    @vuetable:row-clicked="onRowClicked"></vuetable>
            </loading-cover>
        </div>
    </div>
</template>

<script type="text/babel">
    import {channelTitle, withBaseUrl} from "../../common/filters";
    import Vuetable from "vuetable-2/src/components/Vuetable.vue";

    export default {
        name: 'schedule-list',
        components: {Vuetable},
        props: ['channelId'],
        data() {
            return {
                loading: false,
                columns: [
                    {
                        name: 'id',
                        title: 'ID',
                        sortField: 'id'
                    },
                    {
                        name: 'caption',
                        title: this.$t('Name'),
                        sortField: 'caption'
                    },
                    {
                        name: 'channel',
                        title: this.$t('Channel'),
                        callback: this.channelCaption
                    },
                    {
                        name: 'enabled',
                        title: this.$t('Status'),
                        callback: this.showState
                    },
                    {
                        name: 'mode.value',
                        title: this.$t('Schedule mode'),
                        callback: '$t',
                    },
                    {
                        name: 'action.caption',
                        title: this.$t('Action'),
                        callback: '$t',
                    },
                    {
                        name: 'retry',
                        title: this.$t('Retry when fail'),
                        callback: this.showState
                    },
                    {
                        name: 'closestExecutions.future.0.plannedTimestamp',
                        title: this.$t('Next run date'),
                        callback: this.formatDate
                    },
                    {
                        name: 'closestExecutions.past',
                        title: this.$t('The latest execution'),
                        callback: (executions) => this.latestExecution(executions)
                    }
                ].filter(c => !this.channelId || c.name !== 'channel'), // hides "Channel" column when table displayed in context of one
                bootstrapStyles: {
                    tableClass: 'table table-striped table-hover',
                    ascendingIcon: 'glyphicon glyphicon-chevron-up',
                    descendingIcon: 'glyphicon glyphicon-chevron-down',
                }
            };
        },
        methods: {
            formatDate(date) {
                return date ? moment(date).format('LLL') : '-';
            },
            showState(state) {
                if (state) {
                    return `<span class="label label-success">${this.$t('ENABLED')}</span>`;
                } else {
                    return `<span class="label label-warning">${this.$t('DISABLED')}</span>`;
                }
            },
            channelCaption(channel) {
                return channelTitle(channel, this, true);
            },
            latestExecution(executions) {
                if (executions.length) {
                    const execution = executions[executions.length - 1];
                    return `<span class="${execution.failed ? 'text-danger' : 'text-success'}" title="${this.$t(execution.result.caption)}">
                                ${this.formatDate(execution.resultTimestamp)}
                            </span>`;
                } else {
                    return '-';
                }
            },
            onRowClicked(schedule) {
                window.location.href = withBaseUrl(`/schedules/${schedule.id}`);
            }
        }
    };
</script>

<style lang="scss">
    .schedule-list-wrapper {
        tbody td {
            cursor: pointer;
        }
    }
</style>
