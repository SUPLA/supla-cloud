<template>
    <square-link :class="`clearfix pointer with-label ${backgroundColor}`">
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
            <dl class="ellipsis" v-if="!device.locked">
                <dd>{{ $t('Location') }}</dd>
                <dt>ID{{ device.location.id }} {{ device.location.caption }}</dt>
            </dl>
            <div class="square-link-label" v-if="device.locked">
                <span class="label label-warning">{{ $t('Locked') }}</span>
            </div>
            <div class="square-link-label" v-else>
                <span class="label label-danger"
                    v-if="device.relationsCount && device.relationsCount.channelsWithConflict > 0">{{ $t('Conflict') }}</span>
                <connection-status-label v-else :model="device"></connection-status-label>
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
            caption() {
                return this.device.comment || this.$t(this.device.name);
            },
            linkSpec() {
                return this.noLink ? {} : {name: 'device', params: {id: this.device.id}};
            },
            backgroundColor() {
                if (this.device.relationsCount?.channelsWithConflict > 0) {
                    return 'yellow';
                } else if (!this.device.enabled || this.device.locked) {
                    return 'grey';
                } else {
                    return 'green';
                }
            },
        }
    };
</script>
