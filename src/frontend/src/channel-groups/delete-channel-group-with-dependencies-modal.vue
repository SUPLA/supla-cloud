<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Some features depend on this channel group')">
        <p>{{ $t('Some of the features you have configured rely on this channel group.') }}</p>
        <channel-dependencies :dependencies="dependencies">
            <template v-slot:deletingHeader>
                {{ $t('The items below rely on this channel group, so they will be deleted.') }}
            </template>
            <template v-slot:removingHeader>
                {{ $t('Channel group reference will be removed from the items below.') }}
            </template>
        </channel-dependencies>
    </modal-confirm>
</template>

<script>
    import ChannelDependencies from "@/channels/channel-dependencies";

    export default {
        components: {ChannelDependencies},
        props: ['dependencies'],
    };
</script>
