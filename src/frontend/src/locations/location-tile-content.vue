<template>
    <span>
        <h3>{{ caption }}</h3>
        <dl v-if="location.relationsCount">
            <dd>ID</dd>
            <dt>{{ location.id }}</dt>
            <dd>{{ $t("No{'.'} of devices") }}</dd>
            <dt>{{ location.relationsCount.ioDevices }}</dt>
            <dd>{{ $t("No{'.'} of channel groups") }}</dd>
            <dt>{{ location.relationsCount.channelGroups }}</dt>
            <dd>{{ $t("No{'.'} of access identifiers") }}</dd>
            <dt>{{ location.relationsCount.accessIds }}</dt>
        </dl>
        <div class="square-link-label">
            <span :class="'label label-' + (location.enabled ? 'success' : 'grey')">
                {{ $t(location.enabled ? 'Enabled' : 'Disabled') }}
            </span>
        </div>
    </span>
</template>

<script>
    export default {
        props: ['location'],
        computed: {
            caption() {
                return this.location.caption || this.$t('Location') + ' ID' + this.location.id;
            }
        }
    };
</script>
