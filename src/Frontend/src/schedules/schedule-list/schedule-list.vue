<template>
    <div class="row">
        <div class="col-xs-12">
            <div :class="['schedule-list-wrapper', {loading: loading}]">
                <div class="loader">
                    <loading-dots></loading-dots>
                </div>
                <vuetable
                    :api-url="'web-api/schedules?channelId=' + (channelId || 0)"
                    data-path=""
                    pagination-path=""
                    :no-data-template="$t('Empty!')"
                    :css="bootstrapStyles"
                    :fields="columns"
                    @vuetable:loading="loading = true"
                    @vuetable:loaded="loading = false"
                    @vuetable:row-clicked="onRowClicked"
                ></vuetable>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import {withBaseUrl} from "../../common/filters";
    import LoadingDots from "../../common/gui/loaders/loader-dots.vue";
    import Vuetable from "vuetable-2/src/components/Vuetable.vue";

    export default {
        name: 'schedule-list',
        components: {LoadingDots, Vuetable},
        props: ['channelId'],
        data() {
            return {
                loading: false,
                columns: [
                    {
                        name: 'schedule.id',
                        title: this.$t('ID')
                    },
                    {
                        name: 'schedule.caption',
                        title: this.$t('Name'),
                        sortField: 's.caption'
                    },
                    {
                        name: 'channel_caption',
                        title: this.$t('Channel'),
                        sortField: 'channel_caption',
                    },
                    {
                        name: 'schedule.enabled',
                        title: this.$t('Status'),
                        callback: 'enabledDisabled'
                    },
                    {
                        name: 'device_name',
                        title: this.$t('Device'),
                        sortField: 'device_name',
                    },
                    {
                        name: 'location_caption',
                        title: this.$t('Location'),
                        sortField: 'location_caption',
                    },
                    {
                        name: 'schedule.mode.value',
                        title: this.$t('Schedule mode'),
                        callback: '$t',
                    },
                    {
                        name: 'schedule.action.caption',
                        title: this.$t('Action'),
                        callback: '$t',
                    },
                    {
                        name: 'futureExecution.plannedTimestamp',
                        title: this.$t('Next run date'),
                        callback: 'formatDate'
                    },
                    {
                        name: 'latestExecution',
                        title: this.$t('The latest execution'),
                        callback: 'latestExecution'
                    },
                    {
                        name: 'schedule.retry',
                        title: this.$t('Retry when fail'),
                        callback: 'showState'
                    }
                ],
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
            latestExecution(execution) {
                if (execution) {
                    return `<span class="${execution.failed ? 'text-danger' : 'text-success'}" title="${this.$t(execution.result.caption)}">
                                ${this.formatDate(execution.resultTimestamp)}
                            </span>`;
                } else {
                    return '-';
                }
            },
            enabledDisabled(enabled) {
                if (enabled) {
                    return `<span class="label label-success">${this.$t('ENABLED')}</span>`;
                } else {
                    return `<span class="label label-danger">${this.$t('DISABLED')}</span>`;
                }
            },
            onRowClicked(row) {
                window.location.href = withBaseUrl(`/schedules/${row.schedule.id}`);
            }
        }
    };
</script>

<style lang="scss">
    // source: https://github.com/ratiw/vue-table/wiki/Loading-Animation
    .schedule-list-wrapper {
        position: relative;
        opacity: 1;
        tbody td {
            cursor: pointer;
        }
        .loader {
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s linear;
            position: absolute;
            top: 45px;
            width: 100%;
        }
        &.loading {
            .loader {
                visibility: visible;
                opacity: 1;
                z-index: 100;
            }
            .vuetable {
                opacity: 0.3;
            }
        }
    }
</style>
