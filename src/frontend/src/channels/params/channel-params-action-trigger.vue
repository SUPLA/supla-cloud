<template>
    <div>
        <h4>{{ $t('Triggers') }}</h4>
        <p>
            {{ $t('Actual behavior of the triggers depend on the device you use and its configuration. The descriptions below are only examples of how the device may work.') }}
        </p>
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
