<script setup>
    import {computed, ref} from "vue";
    import {useDebounceFn} from "@vueuse/core";
    import {useSubDevicesStore} from "@/stores/subdevices-store";
    import {subDevicesApi} from "@/api/subdevices-api";

    const props = defineProps({
        channel: Object,
    });

    const subDevices = useSubDevicesStore();
    const subDeviceId = computed(() => props.channel?.subDeviceId)
    const subDevice = computed(() => subDevices.all[subDeviceId.value])

    const identifySuccess = ref(false);
    const identifying = ref(false);
    const clearIdentifySuccess = useDebounceFn(() => identifySuccess.value = false, 3000);

    function identify() {
        if (!identifying.value && !identifySuccess.value) {
            identifying.value = true;
            subDevicesApi.identify(subDevice.value)
                .then(() => {
                    identifySuccess.value = true;
                    clearIdentifySuccess();
                })
                .finally(() => identifying.value = false);
        }
    }

    const identifySubdeviceAvailable = computed(() => props.channel?.config?.identifySubdeviceAvailable);
</script>

<template>
    <div>
        <h3 v-if="subDevice && subDevice.name">{{ subDevice.name }}</h3>
        <h3 v-else>{{ $t('Subdevice #{id}', {id: subDeviceId}) }}</h3>
        <div v-if="identifySubdeviceAvailable" class="mb-3">
            <button type="button" :class="['btn btn-sm', identifySuccess ? 'btn-green' : 'btn-white']" v-if="identifySubdeviceAvailable"
                @click="identify()">
                <button-loading-dots v-if="identifying"/>
                <span v-else-if="identifySuccess">
                    <fa icon="check" class="mr-1"/>
                    {{ $t('Success') }}
                </span>
                <span v-else>{{ $t('Identify device') }}</span>
            </button>
        </div>
        <div v-if="subDevice" class="mb-3">
            <span class="label label-default mr-2">{{ $t('Firmware') }}: {{ subDevice.softwareVersion }}</span>
            <span class="label label-default mr-2">{{ $t('P/C') }}: {{ subDevice.productCode }}</span>
            <span class="label label-default mr-2">{{ $t('S/N') }}: {{ subDevice.serialNumber }}</span>
        </div>
    </div>
</template>
