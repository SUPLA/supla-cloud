<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Some features depend on this device')">
        <p></p>
        <channel-dependencies :dependencies="dependencies">
            <template v-slot:deletingHeader>
                {{ $t('Turning this device off will result in disabling features listed below.') }}
            </template>
            <template v-slot:removingHeader>
                {{ $t('Turning this device off will cause its channels not working in the following features.') }}
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
