<template>
    <square-link :class="'clearfix pointer ' + (device.enabled ? 'green' : 'grey')">
        <router-link :to="linkSpec">
            <h3>{{ device.name }}</h3>
            <dl>
                <dd>{{ device.gUIDString }}</dd>
                <dt></dt>
            </dl>
            <div class="separator invisible"></div>
            <dl>
                <dd>{{ $t('SoftVer') }}</dd>
                <dt>{{ device.softwareVersion }}</dt>
            </dl>
            <dl class="ellipsis">
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{device.location.id}} {{ device.location.caption }}</dt>
            </dl>
            <div v-if="device.comment">
                <div class="separator"></div>
                {{ device.comment }}
            </div>
            <div class="square-link-label">
                <connection-status-label :model="device"></connection-status-label>
            </div>
        </router-link>
    </square-link>
</template>

<script>
    import ConnectionStatusLabel from "./connection-status-label.vue";

    export default {
        props: ['device', 'noLink'],
        components: {ConnectionStatusLabel},
        computed: {
            linkSpec() {
                return this.noLink ? {} : {name: 'device', params: {id: this.device.id}};
            }
        }
    };
</script>
