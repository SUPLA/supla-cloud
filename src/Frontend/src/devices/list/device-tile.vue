<template>
    <square-link :class="'clearfix pointer ' + (device.enabled ? 'green' : 'grey')">
        <a :href="'devices/' + device.id"
            v-fade-out-link>
            <h3>{{ device.name }}</h3>
            <dl>
                <dd>{{ device.gUIDString }}</dd>
                <dt></dt>
            </dl>
            <div class="separator invisible"></div>
            <dl>
                <dd>{{ $t('SoftVer') }}</dd>
                <dt>{{ device.softwareVersion }}</dt>
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{device.location.id}} {{ device.location.caption }}</dt>
            </dl>
            <div v-if="device.comment">
                <div class="separator"></div>
                {{ device.comment }}
            </div>
            <div class="square-link-label">
                <device-connection-status-label :device="device"></device-connection-status-label>
            </div>
        </a>
    </square-link>
</template>

<script>
    import DeviceConnectionStatusLabel from "./device-connection-status-label.vue";

    export default {
        props: ['device'],
        components: {DeviceConnectionStatusLabel},
    };
</script>
