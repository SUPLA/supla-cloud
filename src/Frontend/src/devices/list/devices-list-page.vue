<template>
    <div>
        <div class="container">
            <div class="clearfix left-right-header">
                <h1>{{ $t('I/O Devices') }}</h1>
                <devices-registration-button field="ioDevicesRegistrationEnabled"
                    caption="Registration of new I/O devices"></devices-registration-button>
            </div>
            <div class="grid-filters">
                <btn-filters v-model="filters.enabled"
                    :filters="[{label: $t('All'), value: undefined}, {label: $t('Enabled'), value: true}, {label: $t('Disabled'), value: false}]"></btn-filters>
                <btn-filters v-model="filters.connected"
                    :filters="[{label: $t('All'), value: undefined}, {label: $t('Connected'), value: true}, {label: $t('Disconnected'), value: false}]"></btn-filters>
                <input type="text"
                    class="form-control"
                    v-model="filters.search"
                    :placeholder="$t('Search')">
            </div>
        </div>
        <square-links-grid v-if="devices && filteredDevices.length || (showPossibleDevices && !devices.length)"
            :count="filteredDevices.length + (showPossibleDevices ? possibleDevices.length : 0)"
            class="square-links-height-240">
            <div v-for="device in filteredDevices"
                :key="device.id"
                :ref="'device-tile-' + device.id">
                <device-tile :device="device"></device-tile>
            </div>
            <div v-for="possibleDevice in possibleDevices"
                :key="'possible' + possibleDevice.title"
                v-if="showPossibleDevices">
                <square-link class="grey possible-device">
                    <a href="https://www.supla.org"
                        target="_blank"
                        class="valign-center">
                        <span>
                            <i v-if="possibleDevice.icon"
                                :class="possibleDevice.icon"></i>
                            <img v-else
                                :src="'/assets/img/' + possibleDevice.image"
                                :alt="$t(possibleDevice.title)">
                            <h3>{{ $t(possibleDevice.title) }}</h3>
                            <p>{{ $t(possibleDevice.description) }}</p>
                        </span>
                    </a>
                </square-link>
            </div>
        </square-links-grid>
        <empty-list-placeholder v-else-if="devices"></empty-list-placeholder>
        <loader-dots v-else></loader-dots>
        <div class="hidden"
            v-if="devices">
            <!--allow filtered-out items to still receive status updates-->
            <device-connection-status-label :device="device"
                :key="device.id"
                v-for="device in devices"></device-connection-status-label>
        </div>
    </div>
</template>

<script>
    import Vue from "vue";
    import BtnFilters from "src/common/btn-filters.vue";
    import LoaderDots from "src/common/loader-dots.vue";
    import SquareLinksGrid from "src/common/square-links-grid.vue";
    import DeviceTile from "./device-tile.vue";
    import DeviceConnectionStatusLabel from "./device-connection-status-label.vue";
    import SquareLink from "src/common/square-link.vue";
    import DevicesRegistrationButton from "./devices-registration-button.vue";
    import latinize from "latinize";
    import EmptyListPlaceholder from "./empty-list-placeholder.vue";

    export default {
        components: {
            BtnFilters,
            DeviceConnectionStatusLabel,
            DevicesRegistrationButton,
            DeviceTile,
            EmptyListPlaceholder,
            LoaderDots,
            SquareLinksGrid,
            SquareLink,
        },
        data() {
            return {
                devices: undefined,
                filters: {
                    enabled: undefined,
                    connected: undefined,
                    search: '',
                },
                possibleDevices: [
                    {icon: 'pe-7s-light', title: 'Lighting', description: 'With SUPLA you can operate the lights in your home or office'},
                    {image: 'thermometer.svg', title: 'Temperature', description: '...you can monitor temperature'},
                    {image: 'gate.svg', title: 'Doors and gates', description: '...open gateways, gates or doors'},
                    {image: 'window-rollers.svg', title: 'Roller shutters', description: '...open and shut roller shutters'},
                    {icon: 'pe-7s-radio', title: 'Home appliances', description: '...or control home appliances'},
                    {
                        icon: 'pe-7s-smile',
                        title: 'And more',
                        description: 'All the above and many more can be done from your phone or tablet'
                    },
                    {
                        icon: 'pe-7s-plane',
                        title: 'From anywhere',
                        description: 'SUPLA is available from anywhere at any time, so do not worry, if you forget to turn the lights off next time'
                    },
                ]
            };
        },
        mounted() {
            this.$http.get('iodevices')
                .then(({body}) => this.devices = body)
                .then(() => Vue.nextTick(() => this.calculateSearchStrings()));
        },
        computed: {
            filteredDevices() {
                let devices = this.devices;
                if (this.filters.enabled !== undefined) {
                    devices = devices.filter(device => device.enabled == this.filters.enabled);
                }
                if (this.filters.connected !== undefined) {
                    devices = devices.filter(device => device.connected == this.filters.connected);
                }
                if (this.filters.search) {
                    devices = devices.filter(device => device.searchString.indexOf(latinize(this.filters.search).toLowerCase()) >= 0);
                }
                return devices;
            },
            showPossibleDevices() {
                return this.devices && this.devices.length < 3
                    && this.filters.enabled === undefined && this.filters.connected === undefined && !this.filters.search;
            }
        },
        methods: {
            calculateSearchStrings() {
                for (let device of this.devices) {
                    const ref = this.$refs['device-tile-' + device.id];
                    if (ref && ref.length) {
                        this.$set(device, 'searchString', latinize(ref[0].innerText).toLowerCase());
                    }
                }
            },
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

<style lang="scss">
    @import "../../styles/variables";

    .possible-device a {
        text-align: center;
        img {
            width: 70px;
            height: auto;
            margin-top: 8px;
        }
        i {
            font-size: 70px;
        }
        p {
            color: $supla-grey-dark;
        }
    }
</style>
