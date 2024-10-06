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
                <dt>ID{{ device.locationId }} {{ location.caption || '' }}</dt>
            </dl>
            <div class="square-link-label" v-if="device.locked">
                <span class="label label-warning">{{ $t('Locked') }}</span>
            </div>
            <div class="square-link-label" v-else>
                <span class="label label-danger"
                    v-if="device.relationsCount && device.relationsCount.channelsWithConflict > 0">{{ $t('Conflict') }}</span>
                <ConnectionStatusLabel :model="device"/>
            </div>
        </router-link>
    </square-link>
</template>

<script>
    import {mapState} from "pinia";
    import {useLocationsStore} from "@/stores/locations-store";
    import ConnectionStatusLabel from "@/devices/list/connection-status-label.vue";
    import {escapeI18n} from "@/locale";

    export default {
        components: {ConnectionStatusLabel},
        props: ['device', 'noLink'],
        computed: {
            ...mapState(useLocationsStore, {locations: 'all'}),
            location() {
                return this.locations[this.device.locationId] || {};
            },
            caption() {
                return this.device.comment || this.$t(escapeI18n(this.device.name));
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
