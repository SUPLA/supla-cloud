<script setup>
    import {computed} from "vue";
    import {useSubDevicesStore} from "@/stores/subdevices-store";
    import {subDevicesApi} from "@/api/subdevices-api";
    import PromiseConfirmButton from "@/devices/details/promise-confirm-button.vue";
    import ChannelDeleteButton from "@/channels/channel-delete-button.vue";

    const props = defineProps({
        channels: Array,
    });

    const subDevices = useSubDevicesStore();
    subDevices.fetchAll();
    const subDevice = computed(() => subDevices.forChannel(props.channels[0]))

    const identify = () => subDevicesApi.identify(props.channels[0]);
    const identifyAvailable = computed(() => props.channels[0]?.config?.identifySubdeviceAvailable);

    const restart = () => subDevicesApi.restart(props.channels[0]);
    const restartAvailable = computed(() => props.channels[0]?.config?.restartSubdeviceAvailable);

    const deleteAvailable = computed(() => !props.channels.find((ch) => !ch.deletable));
</script>

<template>
    <div>
        <h3 class="mt-3 mb-2" v-if="subDevice && subDevice.name">{{ subDevice.name }}</h3>
        <h3 class="mt-3 mb-2" v-else>{{ $t('Subdevice #{id}', {id: channels[0].subDeviceId}) }}</h3>
        <div class="mb-3 d-flex" v-if="identifyAvailable || restartAvailable || deleteAvailable">
            <PromiseConfirmButton :action="identify" label-i18n="Identify device" v-if="identifyAvailable" class="mr-2"/>
            <PromiseConfirmButton :action="restart" label-i18n="Restart device" v-if="restartAvailable" class="mr-2"/>
            <ChannelDeleteButton v-if="deleteAvailable" :channel="channels[0]" deleting-subdevice/>
        </div>
        <div v-if="subDevice" class="mb-3">
            <span class="label label-default mr-2" v-if="subDevice.softwareVersion">
                {{ $t('Firmware') }}: {{ subDevice.softwareVersion }}
            </span>
            <span class="label label-default mr-2" v-if="subDevice.productCode">{{ $t('P/C') }}: {{ subDevice.productCode }}</span>
            <span class="label label-default mr-2" v-if="subDevice.serialNumber">{{ $t('S/N') }}: {{ subDevice.serialNumber }}</span>
        </div>
    </div>
</template>
