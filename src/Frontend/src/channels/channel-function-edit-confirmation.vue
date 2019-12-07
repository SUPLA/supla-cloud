<template>
    <modal-confirm @confirm="$emit('confirm')"
        @cancel="$emit('cancel')"
        :header="$t('Are you sure you want to change channel’s function?')">
        <p>{{ $t('Changing channel’s function will also result in the following changes.') }}</p>
        <channel-dependencies :dependencies="confirmationObject"></channel-dependencies>
    </modal-confirm>
</template>

<script>
    import ChannelDependencies from "./channel-dependencies";

    export default {
        components: {ChannelDependencies},
        props: ['confirmationObject']
    };
</script>
