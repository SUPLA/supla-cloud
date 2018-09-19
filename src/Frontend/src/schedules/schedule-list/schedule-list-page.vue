<template>
    <div>
        <div class="container">
            <div class="clearfix left-right-header">
                <div>
                    <h1 v-if="!subjectType"
                        v-title>{{ $t('Schedules') }}</h1>
                </div>
                <div :class="subjectType ? 'no-margin-top' : ''">
                    <router-link :to="{name: 'schedule.new', query: {subjectId: subjectId, subjectType: subjectType}}"
                        class="btn btn-green btn-lg"
                        v-if="schedules">
                        <i class="pe-7s-plus"></i>
                        {{ $t('Create New Schedule') }}
                    </router-link>
                </div>
            </div>
        </div>
        <loading-cover :loading="!schedules">
            <div class="container"
                v-show="schedules && schedules.length">
                <schedule-filters @filter-function="filterFunction = $event"
                    @compare-function="compareFunction = $event"
                    @filter="filter()"></schedule-filters>
            </div>
            <div v-if="schedules && schedules.length">
                <square-links-grid v-if="filteredSchedules.length"
                    :count="filteredSchedules.length"
                    class="square-links-height-250">
                    <div v-for="schedule in filteredSchedules"
                        :key="schedule.id">
                        <schedule-tile :model="schedule"></schedule-tile>
                    </div>
                </square-links-grid>
                <empty-list-placeholder v-else></empty-list-placeholder>
            </div>
            <empty-list-placeholder v-else-if="schedules"></empty-list-placeholder>
        </loading-cover>
    </div>
</template>

<script type="text/babel">
    import ScheduleTile from "./schedule-tile";
    import ScheduleFilters from "./schedule-filters";

    export default {
        components: {ScheduleFilters, ScheduleTile},
        props: ['subjectId', 'subjectType'],
        data() {
            return {
                schedules: undefined,
                totalSchedules: undefined,
                filteredSchedules: undefined,
                filterFunction: () => true,
                compareFunction: () => -1,
            };
        },
        mounted() {
            let endpoint = 'schedules?include=subject,closestExecutions';
            if (this.subjectId) {
                if (this.subjectType == 'channel') {
                    endpoint = `channels/${this.subjectId}/${endpoint}`;
                }
                else if (this.subjectType == 'channelGroup') {
                    endpoint = `channel-groups/${this.subjectId}/${endpoint}`;
                }
            }
            this.$http.get(endpoint).then(response => {
                this.schedules = response.body;
                this.totalSchedules = +response.headers.get('SUPLA-Total-Schedules');
                this.filter();
            });
        },
        methods: {
            filter() {
                this.filteredSchedules = this.schedules ? this.schedules.filter(this.filterFunction) : this.schedules;
                if (this.filteredSchedules) {
                    this.filteredSchedules = this.filteredSchedules.sort(this.compareFunction);
                }
            },
        }
    };
</script>
