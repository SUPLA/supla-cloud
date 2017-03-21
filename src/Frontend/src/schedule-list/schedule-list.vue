<template>
    <div class="row">
        <div class="col-xs-12">
            <div :class="['schedule-list-wrapper', {loading: loading}]">
                <div class="loader">
                    <div class="progress">
                        <div class="progress-bar progress-bar-success progress-bar-striped active"
                            style="width: 100%"></div>
                    </div>
                </div>
                <vuetable
                    api-url="schedule/"
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
    import Vue from 'vue';
    export default {
        name: 'schedule-list',
        data() {
            return {
                loading: false,
                columns: [
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
                        name: 'schedule.dateStart',
                        title: this.$t('Start date'),
                        callback: 'formatDate',
                        sortField: 's.dateStart'
                    }
                ],
                bootstrapStyles: {
                    tableClass: 'table table-striped table-hover',
                    ascendingIcon: 'glyphicon glyphicon-chevron-up',
                    descendingIcon: 'glyphicon glyphicon-chevron-down',
                }
            }
        },
        methods: {
            formatDate(date) {
                return moment(date).format('LLL');
            },
            enabledDisabled(enabled) {
                if (enabled) {
                    return `<span class="label label-success">${this.$t('ENABLED')}</span>`;
                } else {
                    return `<span class="label label-danger">${this.$t('DISABLED')}</span>`;
                }
            },
            onRowClicked(row) {
                window.location.href = `${Vue.http.options.root}/schedule/${row.schedule.id}`;
            }
        }
    };
</script>

<style lang="scss"
    rel="stylesheet/scss">
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
