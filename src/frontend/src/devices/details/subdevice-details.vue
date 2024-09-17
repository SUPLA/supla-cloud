<script setup>
    import {computed} from "vue";

    const props = defineProps({
        subDeviceId: Number,
        device: Object,
    });

    const subdevice = computed(() => props.device?.subDevices?.find((sd) => sd.id == props.subDeviceId));
</script>

<template>
    <div>
        <h3 v-if="subdevice && subdevice.name">{{ subdevice.name }}</h3>
        <h3 v-else>Urządzenie podwykonawcze #{{ subDeviceId }}</h3>
        <div v-if="subdevice" class="mb-3">
            <span class="label label-default mr-2">{{ $t('SoftVer') }}: {{ subdevice.softwareVersion }}</span>
            <span class="label label-default mr-2">{{ $t('Product code') }}: {{ subdevice.productCode }}</span>
            <span class="label label-default mr-2">{{ $t('S/N') }}: {{ subdevice.serialNumber }}</span>
        </div>
    </div>
</template>
