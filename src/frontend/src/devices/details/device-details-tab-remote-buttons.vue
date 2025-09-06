<template>
    <div>
        <DeviceEnterConfigurationModeButton :device="device" class="mb-2"/>
        <DeviceIdentifyDeviceButton :device="device" class="mb-2"/>
        <DeviceRemoteRestartButton :device="device" class="mb-2"/>
        <DeviceRemoteFactoryResetButton :device="device" class="mb-2"/>
        <DeviceSetCfgPasswordButton :device="device" class="mb-2"/>
    </div>
</template>

<script setup lang="ts">
    import DeviceEnterConfigurationModeButton from "@/devices/details/device-enter-configuration-mode-button.vue";
    import DeviceIdentifyDeviceButton from "@/devices/details/device-identify-device-button.vue";
    import DeviceRemoteRestartButton from "@/devices/details/device-remote-restart-button.vue";
    import DeviceRemoteFactoryResetButton from "@/devices/details/device-remote-factory-reset-button.vue";
    import DeviceSetCfgPasswordButton from "@/devices/details/device-set-cfg-password-button.vue";

    defineProps<{ device: IODevice }>();
</script>
