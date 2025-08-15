<template>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-sm-6">
                <div class="details-page-block" v-for="tile in firstColumnTiles" :key="tile.title">
                    <h3 class="text-center">{{ $t(tile.title) }}</h3>
                    <Component :is="tile.component" :device="device"/>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="details-page-block" v-for="tile in secondColumnTiles" :key="tile.title">
                    <h3 class="text-center">{{ $t(tile.title) }}</h3>
                    <Component :is="tile.component" :device="device"/>
                </div>
            </div>
            <div class="col-lg-4 col-sm-6">
                <div class="details-page-block" v-for="tile in thirdColumnTiles" :key="tile.title">
                    <h3 class="text-center">{{ $t(tile.title) }}</h3>
                    <Component :is="tile.component" :device="device"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
    import DeviceDetailsTabBasicSettings from "@/devices/details/device-details-tab-basic-settings.vue";
    import DeviceDetailsTabFirmwareInfo from "@/devices/details/device-details-tab-firmware-info.vue";
    import DeviceDetailsTabLocation from "@/devices/details/device-details-tab-location.vue";
    import DeviceDetailsTabAccessIds from "@/devices/details/device-details-tab-access-ids.vue";
    import DeviceDetailsTabRemoteButtons from "@/devices/details/device-details-tab-remote-buttons.vue";
    import {computed} from "vue";
    import DeviceDetailsTabSettingsUserInterface from "@/devices/details/device-details-tab-settings-user-interface.vue";
    import DeviceDetailsTabSettingsScreenBrightness from "@/devices/details/device-details-tab-settings-screen-brightness.vue";
    import DeviceDetailsTabSettingsLed from "@/devices/details/device-details-tab-settings-led.vue";
    import DeviceDetailsTabSettingsHomeScreen from "@/devices/details/device-details-tab-settings-home-screen.vue";
    import DeviceDetailsTabSettingsModbus from "@/devices/details/device-details-tab-settings-modbus.vue";
    import DeviceDetailsTabSettingsOther from "@/devices/details/device-details-tab-settings-other.vue";

    const props = defineProps({device: Object});

    const availableTiles = [
        {title: 'Device information', component: DeviceDetailsTabFirmwareInfo, show: !props.device.isVirtual},
        {title: 'Basic settings', component: DeviceDetailsTabBasicSettings, show: true},
        {title: 'Location', component: DeviceDetailsTabLocation, show: !props.device.locked},
        {title: 'Access ID', component: DeviceDetailsTabAccessIds, show: !props.device.locked},
        {
            title: 'Remote access',
            component: DeviceDetailsTabRemoteButtons,
            show: !props.device.locked && (
                props.device.flags.enterConfigurationModeAvailable
                || props.device.flags.identifyDeviceAvailable
                || props.device.remoteRestartAvailable
                || props.device.flags.factoryResetSupported
                || props.device.flags.setCfgModePasswordSupported
                || props.device.flags.pairingSubdevicesAvailable
            )
        },
        {title: 'MODBUS', component: DeviceDetailsTabSettingsModbus, show: props.device.config?.modbus !== undefined},
        {title: 'Home screen', component: DeviceDetailsTabSettingsHomeScreen, show: props.device.config?.homeScreen !== undefined},
        {
            title: 'Device interface',
            component: DeviceDetailsTabSettingsUserInterface,
            show: props.device.config?.userInterface !== undefined || props.device.config?.buttonVolume !== undefined
        },
        {
            title: 'Screen brightness',
            component: DeviceDetailsTabSettingsScreenBrightness,
            show: props.device.config?.screenBrightness !== undefined
        },
        {
            title: 'LED settings',
            component: DeviceDetailsTabSettingsLed,
            show: props.device.config?.powerStatusLed !== undefined || props.device.config?.statusLed !== undefined,
        },
        {
            title: 'Other settings',
            component: DeviceDetailsTabSettingsOther,
            show: props.device.config?.automaticTimeSync !== undefined
                || props.device.config?.firmwareUpdatePolicy !== undefined,
        },
    ];

    const tiles = computed(() => availableTiles.filter(t => t.show));
    const firstColumnTiles = computed(() => tiles.value.filter((_, index) => index % 3 === 0));
    const secondColumnTiles = computed(() => tiles.value.filter((_, index) => index % 3 === 1));
    const thirdColumnTiles = computed(() => tiles.value.filter((_, index) => index % 3 === 2));
</script>
