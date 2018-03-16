<template>
    <div>
        <div class="container">
            <div class="clearfix left-right-header">
                <h1>{{ $t('I/O Devices') }}</h1>
                <devices-registration-button field="ioDevicesRegistrationEnabled"
                    caption="Registration of new I/O devices"></devices-registration-button>
            </div>
            <device-filters @filter-function="filterFunction = $event"
                @compare-function="compareFunction = $event"
                @filter="filter()"></device-filters>
        </div>
        <loading-cover :loading="!devices">
            <square-links-grid v-if="filteredDevices && filteredDevices.length || (showPossibleDevices && !devices.length)"
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
        </loading-cover>
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
    import BtnFilters from "src/common/btn-filters.vue";
    import DeviceTile from "./device-tile.vue";
    import DeviceConnectionStatusLabel from "./device-connection-status-label.vue";
    import DevicesRegistrationButton from "./devices-registration-button.vue";
    import EmptyListPlaceholder from "src/common/gui/empty-list-placeholder.vue";
    import DeviceFilters from "./device-filters";

    export default {
        components: {
            DeviceFilters,
            BtnFilters,
            DeviceConnectionStatusLabel,
            DevicesRegistrationButton,
            DeviceTile,
            EmptyListPlaceholder,
        },
        data() {
            return {
                devices: undefined,
                filteredDevices: [],
                filterFunction: () => true,
                compareFunction: () => -1,
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
            this.$http.get('iodevices?include=location')
                .then(({body}) => this.devices = body)
                .then(() => this.filter());
        },
        computed: {
            showPossibleDevices() {
                return this.devices && this.devices.length < 3 && this.filteredDevices.length === this.devices.length;
            }
        },
        methods: {
            filter() {
                this.filteredDevices = this.devices ? this.devices.filter(this.filterFunction) : this.devices;
                if (this.filteredDevices) {
                    this.filteredDevices = this.filteredDevices.sort(this.compareFunction);
                }
            },
            fetchConnectedClientApps() {
                this.$http.get('client-apps?onlyConnected=true').then(({body}) => {
                    const connectedIds = body.map(app => app.id);
                    this.clientApps.forEach(app => app.connected = connectedIds.indexOf(app.id) >= 0);
                });
            }
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
