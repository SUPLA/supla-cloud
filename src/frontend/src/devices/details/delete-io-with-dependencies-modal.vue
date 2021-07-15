<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Some features depend on this device')">
        <p>{{ $t('Some of the features you have configured rely on channels from this device.') }}</p>
        <channel-dependencies :dependencies="dependencies">
            <template v-slot:deletingHeader>
                {{ $t('The following items will be deleted with this device:') }}
            </template>
            <template v-slot:removingHeader>
                {{ $t('The following items use the channels of these device. These references will be also removed.') }}
            </template>
        </channel-dependencies>
    </modal-confirm>
</template>

<script>
    import ChannelDependencies from "../../channels/channel-dependencies";

    export default {
        components: {ChannelDependencies},
        props: ['dependencies'],
        data() {
            return {};
        },
        methods: {}
    };
</script>
