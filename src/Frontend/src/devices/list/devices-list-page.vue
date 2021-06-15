<template>
    <div>
        <div class="container">
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
            <connection-status-label :model="device"
                :key="device.id"
                v-for="device in devices"></connection-status-label>
        </div>
    </div>
</template>

<script>
    import BtnFilters from "src/common/btn-filters.vue";
    import DeviceTile from "./device-tile.vue";
    import ConnectionStatusLabel from "./connection-status-label.vue";
    import EmptyListPlaceholder from "src/common/gui/empty-list-placeholder.vue";
    import DeviceFilters from "./device-filters";
    import EventBus from "src/common/event-bus";

    export default {
        components: {
            DeviceFilters,
            BtnFilters,
            ConnectionStatusLabel,
            DeviceTile,
            EmptyListPlaceholder,
        },
        data() {
            return {
                devices: undefined,
                filteredDevices: undefined,
                filterFunction: () => true,
                compareFunction: () => -1,
                loadNewDevicesListener: undefined,
                possibleDevices: [
                    {
                        icon: 'pe-7s-light',
                        title: 'Lighting', // i18n
                        description: 'With SUPLA you can operate the lights in your home or office', // i18n
                    },
                    {
                        image: 'thermometer.svg',
                        title: 'Temperature', // i18n
                        description: '...you can monitor temperature', // i18n
                    },
                    {
                        image: 'gate.svg',
                        title: 'Doors and gates', // i18n
                        description: '...open gateways, gates or doors', // i18n
                    },
                    {
                        image: 'window-rollers.svg',
                        title: 'Roller shutters', // i18n
                        description: '...open and shut roller shutters', // i18n
                    },
                    {
                        icon: 'pe-7s-radio',
                        title: 'Home appliances', // i18n
                        description: '...or control home appliances', // i18n
                    },
                    {
                        icon: 'pe-7s-smile',
                        title: 'And more',// i18n
                        description: 'All the above and many more can be done from your phone or tablet', // i18n
                    },
                    {
                        icon: 'pe-7s-plane',
                        title: 'From anywhere', // i18n
                        description: 'SUPLA is available from anywhere at any time, so do not worry, if you forget to turn the lights off next time', // i18n
                    },
                ]
            };
        },
        mounted() {
            this.loadNewDevicesListener = () => this.loadDevices();
            EventBus.$on('device-count-changed', this.loadNewDevicesListener);
            this.loadDevices();
        },
        computed: {
            showPossibleDevices() {
                return this.filteredDevices && this.devices.length < 3 && this.filteredDevices.length === this.devices.length;
            }
        },
        methods: {
            filter() {
                this.filteredDevices = this.devices ? this.devices.filter(this.filterFunction) : this.devices;
                if (this.filteredDevices) {
                    this.filteredDevices = this.filteredDevices.sort(this.compareFunction);
                }
            },
            loadDevices() {
                this.$http.get('iodevices?include=location')
                    .then(({body}) => this.devices = body)
                    .then(() => this.filter());
            }
        },
        beforeDestroy() {
            EventBus.$off('device-count-changed', this.loadNewDevicesListener);
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
