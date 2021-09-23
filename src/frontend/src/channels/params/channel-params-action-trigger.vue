<template>
    <div>
        <h4>
            {{ $t('Triggers') }}
        </h4>
        <action-trigger-panel :channel="channel"
            @change="$emit('change')"></action-trigger-panel>
    </div>
</template>

<script>
    import ActionTriggerPanel from "@/channels/action-trigger/action-trigger-panel";

    export default {
        components: {ActionTriggerPanel},
        props: ['channel'],
    };
</script>
