<template>
    <div>
        <div class="container"
            v-if="availableTabs.length">
            <div class="form-group">
                <ul class="nav nav-tabs">
                    <li :class="currentTab == tabDefinition.id ? 'active' : ''"
                        :key="tabDefinition.id"
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
            <schedules-list :subject="scene"></schedules-list>
        </div>
        <div v-if="currentTab == 'scenes'">
            <scenes-list :subject="scene"></scenes-list>
        </div>
        <div v-if="currentTab == 'directLinks'">
            <direct-links-list :subject="scene"></direct-links-list>
        </div>
    </div>
</template>

<script>
    import SchedulesList from "../schedules/schedule-list/schedules-list";
    import DirectLinksList from "../direct-links/direct-links-list";
    import ScenesList from "../scenes/scenes-list";

    export default {
        props: ['scene'],
        components: {ScenesList, DirectLinksList, SchedulesList},
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
            this.availableTabs.push({id: 'schedules', header: 'Schedules', count: this.scene.relationsCount.schedules});
            this.availableTabs.push({id: 'scenes', header: 'Scenes', count: this.scene.relationsCount.scenes});
            this.availableTabs.push({id: 'directLinks', header: 'Direct links', count: this.scene.relationsCount.directLinks});
            const currentTab = this.availableTabs.filter(tab => tab.id == this.$route.query.tab)[0];
            this.currentTab = currentTab ? currentTab.id : this.availableTabs[0].id;
        },
    };
</script>
