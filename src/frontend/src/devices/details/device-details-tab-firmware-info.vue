<template>
    <div>
        <dl>
            <dt>GUID</dt>
            <dd class="text-monospace">{{ device.gUIDString }}</dd>
            <dt>{{ $t('Firmware version') }}</dt>
            <dd>
                <div>{{ device.softwareVersion }}</div>
                <DeviceOtaUpdatesButtons :device="device"/>
            </dd>
            <dt>{{ $t('Registered') }}</dt>
            <dd>
                {{ formatDateTime(device.regDate) }}
                <DateTimeRelativeLabel :datetime="device.regDate" pattern="(%s)" class="small text-muted"/>
            </dd>
            <dt>{{ $t('Last connection') }}</dt>
            <dd>
                {{ formatDateTime(device.lastConnected) }}
                <DateTimeRelativeLabel :datetime="device.lastConnected" pattern="(%s)" class="small text-muted"/>
            </dd>
        </dl>
    </div>
</template>

<script setup>
    import {formatDateTime} from "@/common/filters-date";
    import DateTimeRelativeLabel from "@/common/date-time-relative-label.vue";
    import DeviceOtaUpdatesButtons from "@/devices/details/device-ota-updates-buttons.vue";

    defineProps({device: Object});
</script>

<style lang="scss">
    dd {
        margin-bottom: 0.5rem;
    }
</style>
