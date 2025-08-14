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
    import DeviceSettingsModbus from "@/devices/details/device-settings-modbus.vue";

    const props = defineProps({device: Object});

    const availableTiles = [
        {title: 'Device information', component: DeviceDetailsTabFirmwareInfo, show: !props.device.isVirtual},
        {title: 'Basic settings', component: DeviceDetailsTabBasicSettings, show: true},
        {title: 'Location', component: DeviceDetailsTabLocation, show: !props.device.locked},
        {title: 'Access ID', component: DeviceDetailsTabAccessIds, show: !props.device.locked},
        {title: 'Remote access', component: DeviceDetailsTabRemoteButtons, show: !props.device.locked},
        {title: 'MODBUS', component: DeviceSettingsModbus, show: props.device.config?.modbus !== undefined},
    ];

    const tiles = computed(() => availableTiles.filter(t => t.show));
    const firstColumnTiles = computed(() => tiles.value.filter((_, index) => index % 3 === 0));
    const secondColumnTiles = computed(() => tiles.value.filter((_, index) => index % 3 === 1));
    const thirdColumnTiles = computed(() => tiles.value.filter((_, index) => index % 3 === 2));
</script>
