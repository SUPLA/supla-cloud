<template>
    <square-link :class="'clearfix pointer with-label ' + (device.enabled ? 'green' : 'grey')">
        <router-link :to="linkSpec">
            <h3>{{ caption }}</h3>
            <dl>
                <dd>{{ device.gUIDString }}</dd>
                <dt></dt>
            </dl>
            <div class="separator invisible"></div>
            <dl>
                <dd>ID</dd>
                <dt>{{ device.id }}</dt>
                <dd v-if="device.comment">{{ $t('Name') }}</dd>
                <dt v-if="device.comment">{{ device.name }}</dt>
                <dd>{{ $t('SoftVer') }}</dd>
                <dt>{{ device.softwareVersion }}</dt>
            </dl>
            <dl class="ellipsis">
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{device.location.id}} {{ device.location.caption }}</dt>
            </dl>
            <div class="square-link-label">
                <device-connection-status-label :device="device"></device-connection-status-label>
            </div>
        </router-link>
    </square-link>
</template>

<script>
    import DeviceConnectionStatusLabel from "./device-connection-status-label.vue";

    export default {
        props: ['device', 'noLink'],
        components: {DeviceConnectionStatusLabel},
        computed: {
            caption() {
                return this.device.comment || this.$t(this.device.name);
            },
            linkSpec() {
                return this.noLink ? {} : {name: 'device', params: {id: this.device.id}};
            }
        }
    };
</script>
