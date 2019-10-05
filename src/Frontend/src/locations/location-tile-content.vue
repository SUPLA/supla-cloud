<template>
    <span>
        <h2>ID<strong>{{ location.id }} </strong></h2>
        <dl v-if="location.relationsCount">
            <dd>{{ $t('No. of devices') }}</dd>
            <dt>{{ location.relationsCount.iodevices }}</dt>
            <dd>{{ $t('No. of channel groups') }}</dd>
            <dt>{{ location.relationsCount.channelGroups }}</dt>
            <dd>{{ $t('No. of access identifiers') }}</dd>
            <dt>{{ location.relationsCount.accessIds }}</dt>
        </dl>
        <div v-if="location.caption">
            <div class="separator"></div>
            {{ location.caption }}
        </div>
        <div class="square-link-label">
            <span :class="'label label-' + (location.enabled ? 'success' : 'grey')">
                {{ $t(location.enabled ? 'Enabled' : 'Disabled') }}
            </span>
        </div>
    </span>
</template>

<script>
    export default {
        props: ['location']
    };
</script>
