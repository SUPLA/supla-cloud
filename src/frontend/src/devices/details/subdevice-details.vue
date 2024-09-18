<script setup>
    import {computed} from "vue";
    import {useSubDevicesStore} from "@/stores/subdevices-store";
    import {subDevicesApi} from "@/api/subdevices-api";
    import PromiseConfirmButton from "@/devices/details/promise-confirm-button.vue";

    const props = defineProps({
        channel: Object,
    });

    const subDevices = useSubDevicesStore();
    const subDeviceId = computed(() => props.channel?.subDeviceId)
    const subDevice = computed(() => subDevices.all[subDeviceId.value])

    const identify = () => subDevicesApi.identify(subDevice.value);
    const identifySubdeviceAvailable = computed(() => props.channel?.config?.identifySubdeviceAvailable);
</script>

<template>
    <div>
        <h3 v-if="subDevice && subDevice.name">{{ subDevice.name }}</h3>
        <h3 v-else>{{ $t('Subdevice #{id}', {id: subDeviceId}) }}</h3>
        <div v-if="identifySubdeviceAvailable" class="mb-3">
            <PromiseConfirmButton :action="identify" label-i18n="Identify device"/>
        </div>
        <div v-if="subDevice" class="mb-3">
            <span class="label label-default mr-2">{{ $t('Firmware') }}: {{ subDevice.softwareVersion }}</span>
            <span class="label label-default mr-2">{{ $t('P/C') }}: {{ subDevice.productCode }}</span>
            <span class="label label-default mr-2">{{ $t('S/N') }}: {{ subDevice.serialNumber }}</span>
        </div>
    </div>
</template>
