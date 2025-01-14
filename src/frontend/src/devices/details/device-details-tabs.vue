<template>
    <div class="details-tabs">
        <div class="container">
            <div class="container" v-if="availableTabs.length > 1">
                <div class="form-group">
                    <ul class="nav nav-tabs">
                        <li v-for="tabDefinition in availableTabs" :key="tabDefinition.id"
                            :class="{active: $route.name === tabDefinition.route}">
                            <router-link :to="{name: tabDefinition.route, params: {id: device.id}}">
                                {{ $t(tabDefinition.header) }}
                                <span v-if="tabDefinition.count !== undefined">({{ tabDefinition.count() }})</span>
                            </router-link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <RouterView :device="device"/>
    </div>
</template>

<script>
    export default {
        props: {
            device: Object,
        },
        data() {
            return {
                tabVisible: true,
                availableTabs: [],
                channelUpdatedListener: undefined,
            };
        },
        methods: {
            rerender() {
                this.tabVisible = false;
                this.$nextTick(() => this.tabVisible = true);
            },
            detectAvailableTabs() {
                this.availableTabs = [];
                if (this.device.locked) {
                    this.availableTabs.push({
                        route: 'device.unlock',
                        header: 'Unlock the device', // i18n
                    });
                } else {
                    this.availableTabs.push({
                        route: 'device.channels',
                        header: 'Channels', // i18n
                    });
                    if (this.device.relationsCount.managedNotifications) {
                        this.availableTabs.push({
                            route: 'device.notifications',
                            header: 'Notifications', // i18n
                        });
                    }
                    if (Object.keys(this.device.config || {}).length) {
                        this.availableTabs.push({
                            route: 'device.settings',
                            header: 'Settings', // i18n
                        });
                    }
                }
            },
        },
        mounted() {
            this.detectAvailableTabs();
            if (this.availableTabs.length) {
                if (this.$router.currentRoute.name === 'device' || !this.availableTabs.map(t => t.route).includes(this.$router.currentRoute.name)) {
                    this.$router.replace({name: this.availableTabs[0].route, params: {id: this.device.id}});
                }
            }
        },
    };
</script>
