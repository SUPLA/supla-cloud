<template>
    <div>
        <div class="container">
            <h1>{{ $t('I/O Devices') }}</h1>
        </div>
        <square-links-grid v-if="devices && filteredDevices.length"
            :count="filteredDevices.length"
            class="square-links-height-240">
            <div v-for="device in filteredDevices"
                :key="device.id">
                <device-tile :device="device"></device-tile>
            </div>
        </square-links-grid>
        <div v-else-if="devices"
            class="text-center">
            <h3><i class="pe-7s-paint-bucket"></i></h3>
            <h2>Pusto!</h2>
        </div>
        <loader-dots v-else></loader-dots>
    </div>
</template>

<script>
    import BtnFilters from "src/common/btn-filters.vue";
    import LoaderDots from "src/common/loader-dots.vue";
    import SquareLinksGrid from "src/common/square-links-grid.vue";
    import DeviceTile from "./device-tile.vue";

    export default {
        components: {BtnFilters, LoaderDots, SquareLinksGrid, DeviceTile},
        data() {
            return {
                devices: undefined,
                filters: {
                    sort: 'az',
                    enabled: undefined,
                    connected: undefined,
                    search: '',
                }
            };
        },
        mounted() {
            this.$http.get('iodev').then(({body}) => {
                body.forEach(app => {
                    app.connected = undefined;
                });
                this.devices = body;
//                this.fetchConnectedClientApps();
//                this.timer = setInterval(this.fetchConnectedClientApps, 7000);
            });
        },
        computed: {
            filteredDevices() {
                return this.devices;
                let apps = this.clientApps;
                if (this.filters.enabled !== undefined) {
                    apps = apps.filter(app => app.enabled == this.filters.enabled);
                }
                if (this.filters.connected !== undefined) {
                    apps = apps.filter(app => app.connected == this.filters.connected);
                }
                if (this.filters.search) {
                    apps = apps.filter(app => app.name.toLowerCase().indexOf(this.filters.search.toLowerCase()) >= 0);
                }
                if (this.filters.sort == 'az') {
                    apps = apps.sort((a1, a2) => a1.name.toLowerCase() < a2.name.toLowerCase() ? -1 : 1);
                } else if (this.filters.sort == 'lastAccess') {
                    apps = apps.sort((a1, a2) => moment(a2.lastAccessDate).diff(moment(a1.lastAccessDate)));
                }
                return apps;
            }
        },
        methods: {
            fetchConnectedClientApps() {
                this.$http.get('client-apps?onlyConnected=true').then(({body}) => {
                    const connectedIds = body.map(app => app.id);
                    this.clientApps.forEach(app => app.connected = connectedIds.indexOf(app.id) >= 0);
                });
            }
        },
        beforeDestroy() {
            clearInterval(this.timer);
        }
    };
</script>
