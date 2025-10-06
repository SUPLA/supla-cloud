<template>
    <list-page header-i18n="Schedules"
        tile="schedule-tile"
        filters="schedule-filters"
        endpoint="schedules?include=subject,closestExecutions"
        create-new-label-i18n="Create New Schedule"
        :limit="userData.limits.schedule"
        details-route="schedule"
        :subject="subject"></list-page>
</template>

<script>
    import ScheduleTile from "./schedule-tile";
    import ScheduleFilters from "./schedule-filters";
    import ListPage from "../../common/pages/list-page";
    import Vue from "vue";
    import {mapState} from "pinia";
    import {useCurrentUserStore} from "@/stores/current-user-store";

    Vue.component('ScheduleTile', ScheduleTile);
    Vue.component('ScheduleFilters', ScheduleFilters);

    export default {
        components: {ListPage},
        props: ['subject'],
        computed: {
            ...mapState(useCurrentUserStore, ['userData']),
        },
    };
</script>
