<template>
    <carousel-page header-i18n="Schedules"
        tile="schedule-tile"
        endpoint="schedules?include=subject,closestExecutions"
        create-new-label-i18n="Create New Schedule"
        :limit="userData.limits.schedule"
        filters="schedule-filters"
        details-route="schedule"></carousel-page>
</template>

<script>
    import CarouselPage from "../../common/pages/carousel-page";
    import ScheduleTile from "./schedule-tile";
    import ScheduleFilters from "./schedule-filters";
    import Vue from "vue";
    import {mapState} from "pinia";
    import {useCurrentUserStore} from "@/stores/current-user-store";

    Vue.component('ScheduleTile', ScheduleTile);
    Vue.component('ScheduleFilters', ScheduleFilters);

    export default {
        components: {CarouselPage},
        computed: {
            ...mapState(useCurrentUserStore, ['userData']),
        },
    };
</script>
