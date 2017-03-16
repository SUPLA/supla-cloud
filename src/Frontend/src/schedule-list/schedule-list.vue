<template>
    <div class="row">
        <div class="col-xs-12">
            <div :class="'schedule-list-wrapper ' + (loading ? 'loading' : '')"><!--Wrapper Element -->
                <div class="loader">
                    <div class="progress">
                        <div class="progress-bar progress-bar-success progress-bar-striped active"
                            style="width: 100%"></div>
                    </div>
                </div>
                <vuetable
                    api-url="schedule/"
                    @vuetable:loading="loading = true"
                    @vuetable:loaded="loading = false"
                    :css="bootstrapStyles"
                    :fields="columns"
                    :item-actions="itemActions"
                ></vuetable>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
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
                        name: 'schedule.mode',
                        title: this.$t('Schedule mode'),
                        callback: '$t',
                    },
                    {
                        name: 'schedule.action.caption',
                        title: this.$t('Action'),
                        callback: '$t',
                    },
                ],
                bootstrapStyles: {
                    tableClass: 'table table-striped table-hover',
                    ascendingIcon: 'glyphicon glyphicon-chevron-up',
                    descendingIcon: 'glyphicon glyphicon-chevron-down',
                },
                itemActions: [
//                    {name: 'view-item', label: '', icon: 'zoom icon', class: 'ui teal button'},
//                    {name: 'edit-item', label: '', icon: 'edit icon', class: 'ui orange button'},
//                    {name: 'delete-item', label: '', icon: 'delete icon', class: 'ui red button'}
                ]
            }
        }
    };
</script>

<style lang="scss"
    rel="stylesheet/scss"
    scoped>
    // source: https://github.com/ratiw/vue-table/wiki/Loading-Animation
    .schedule-list-wrapper {
        position: relative;
        opacity: 1;
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
