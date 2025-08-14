<template>
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <div class="details-page-block" v-for="tile in firstColumnTiles" :key="tile.title">
                    <h3 class="text-center">{{ $t(tile.title) }}</h3>
                    <Component :is="tile.component" :device="device"/>
                </div>
            </div>
            <div class="col-md-4">
                <div class="details-page-block" v-for="tile in secondColumnTiles" :key="tile.title">
                    <h3 class="text-center">{{ $t(tile.title) }}</h3>
                    <Component :is="tile.component" :device="device"/>
                </div>
            </div>
            <div class="col-md-4">
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

    const props = defineProps({device: Object});

    const tiles = computed(() => {
        const t = [
            {title: 'Device information', component: DeviceDetailsTabFirmwareInfo, show: !props.device.isVirtual},
            {title: 'Basic settings', component: DeviceDetailsTabBasicSettings, show: true},
            {title: 'Location', component: DeviceDetailsTabLocation, show: !props.device.locked},
            {title: 'Access ID', component: DeviceDetailsTabAccessIds, show: !props.device.locked},
            {title: 'Remote access', component: DeviceDetailsTabRemoteButtons, show: !props.device.locked},
        ];

        return t.filter(t => t.show);
    });

    const firstColumnTiles = computed(() => {
        return tiles.value.filter((_, index) => index % 3 === 0);
    });

    const secondColumnTiles = computed(() => {
        return tiles.value.filter((_, index) => index % 3 === 1);
    });

    const thirdColumnTiles = computed(() => {
        return tiles.value.filter((_, index) => index % 3 === 2);
    });


</script>
