<template>
    <div>
        <div class="container"
            v-if="availableTabs.length">
            <div class="form-group">
                <ul class="nav nav-tabs">
                    <li :class="currentTab == tabDefinition.id ? 'active' : ''"
                        v-for="tabDefinition in availableTabs">
                        <a @click="changeTab(tabDefinition.id)">
                            {{ $t(tabDefinition.header) }}
                            <span v-if="tabDefinition.count !== undefined">({{ tabDefinition.count }})</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
        <div v-if="currentTab == 'schedules'">
            <schedule-list-page :subject-id="channelGroup.id"
                subject-type="channelGroup"></schedule-list-page>
        </div>
        <div v-if="currentTab == 'directLinks'">
            <direct-links-list :subject="channelGroup"></direct-links-list>
        </div>
    </div>
</template>

<script>
    import ScheduleListPage from "../schedules/schedule-list/schedule-list-page";
    import DirectLinksList from "../direct-links/direct-links-list";

    export default {
        props: ['channelGroup'],
        components: {DirectLinksList, ScheduleListPage},
        data() {
            return {
                currentTab: '',
                availableTabs: []
            };
        },
        methods: {
            changeTab(id) {
                this.currentTab = id;
                this.$router.push({query: {tab: id}});
            }
        },
        mounted() {
            this.availableTabs.push({id: 'schedules', header: 'Schedules', count: this.channelGroup.relationsCount.schedules});
            this.availableTabs.push({id: 'directLinks', header: 'Direct links', count: this.channelGroup.relationsCount.directLinks});
            const currentTab = this.availableTabs.filter(tab => tab.id == this.$route.query.tab)[0];
            this.currentTab = currentTab ? currentTab.id : this.availableTabs[0].id;
        },
    };
</script>
