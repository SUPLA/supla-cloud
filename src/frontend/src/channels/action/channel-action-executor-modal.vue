<template>
    <modal :header="$t('Execute an action')"
        @confirm="$emit('confirm')">
        <channel-action-executor :subject="subject"/>
    </modal>
</template>

<script>
    import ChannelActionExecutor from "./channel-action-executor";

    export default {
        components: {ChannelActionExecutor},
        props: ['subject'],
    };
</script>
