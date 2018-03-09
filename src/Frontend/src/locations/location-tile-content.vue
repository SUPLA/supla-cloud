<template>
    <span>
        <h2>ID<strong>{{ location.id }} </strong></h2>
        <dl>
            <dd>{{ $t('Devices no') }}</dd>
            <dt>{{ location.iodevicesIds.length }}</dt>
            <dd>{{ $t('Channel groups no') }}</dd>
            <dt>{{ location.channelGroupsIds.length }}</dt>
            <dd>{{ $t('Access identifiers no') }}</dd>
            <dt>{{ location.accessIdsIds.length }}</dt>
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
