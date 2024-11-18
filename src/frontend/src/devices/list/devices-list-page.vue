<template>
    <div>
        <loading-cover :loading="!devices">
            <div class="container" v-show="devices && devices.length">
                <device-filters @filter-function="filterFunction = $event"
                    @compare-function="compareFunction = $event"></device-filters>
            </div>
            <square-links-grid v-if="filteredDevices && filteredDevices.length || (showPossibleDevices && !devices.length)"
                :count="filteredDevices.length + (showPossibleDevices ? possibleDevices.length : 0)">
                <div v-for="device in filteredDevices"
                    :key="device.id"
                    :ref="'device-tile-' + device.id">
                    <device-tile :device="device"></device-tile>
                </div>
                <template v-if="showPossibleDevices">
                    <div v-for="possibleDevice in possibleDevices"
                        :key="'possible' + possibleDevice.title">
                        <square-link class="grey possible-device">
                            <a href="https://www.supla.org" target="_blank" class="valign-center">
                                <span>
                                    <i v-if="possibleDevice.icon" :class="possibleDevice.icon"></i>
                                    <img v-else :src="'/assets/img/' + possibleDevice.image" :alt="$t(possibleDevice.title)">
                                    <h3>{{ $t(possibleDevice.title) }}</h3>
                                    <p>{{ $t(possibleDevice.description) }}</p>
                                </span>
                            </a>
                        </square-link>
                    </div>
                </template>
            </square-links-grid>
            <empty-list-placeholder v-else-if="devices"></empty-list-placeholder>
        </loading-cover>
    </div>
</template>

<script>
    import DeviceTile from "./device-tile.vue";
    import EmptyListPlaceholder from "../../common/gui/empty-list-placeholder.vue";
    import DeviceFilters from "./device-filters";
    import {mapState, mapStores} from "pinia";
    import {useDevicesStore} from "@/stores/devices-store";

    export default {
        components: {
            DeviceFilters,
            DeviceTile,
            EmptyListPlaceholder,
        },
        data() {
            return {
                filterFunction: () => true,
                compareFunction: () => -1,
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
        beforeMount() {
            this.devicesStore.fetchAll(true);
        },
        computed: {
            showPossibleDevices() {
                return this.filteredDevices && this.devices.length < 3 && this.filteredDevices.length === this.devices.length;
            },
            ...mapStores(useDevicesStore),
            ...mapState(useDevicesStore, {devices: 'list'}),
            filteredDevices() {
                const filteredDevices = this.devices ? this.devices.filter(this.filterFunction) : this.devices;
                if (filteredDevices) {
                    filteredDevices.sort(this.compareFunction);
                }
                return filteredDevices;
            }
        },
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
