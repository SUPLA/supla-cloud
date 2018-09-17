<template>
    <div>
        <div class="container"
            v-if="availableTabs.length">
            <div class="form-group">
                <ul class="nav nav-tabs">
                    <li :class="currentTab == tabDefinition.id ? 'active' : ''"
                        v-for="tabDefinition in availableTabs">
                        <a @click="currentTab = tabDefinition.id">{{ $t(tabDefinition.header) }}</a>
                    </li>
                </ul>
            </div>
        </div>
        <div v-if="currentTab == 'schedules'">
            <schedule-list-page :subject-id="channelGroup.id"
                subject-type="channelGroup"></schedule-list-page>
        </div>
    </div>
</template>

<script>
    import ScheduleListPage from "../schedules/schedule-list/schedule-list-page";

    export default {
        props: ['channelGroup'],
        components: {ScheduleListPage},
        data() {
            return {
                currentTab: '',
                availableTabs: []
            };
        },
        mounted() {
            this.availableTabs.push({id: 'schedules', header: 'Schedules'});
            if (this.availableTabs.length) {
                this.currentTab = this.availableTabs[0].id;
            }
        },
    };
</script>
