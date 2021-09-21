<template>
    <div>
        <h4>
            {{ $t('Triggers') }}
            <span v-tooltip="$t('Actual behavior of the triggers depend on the device you use and its configuration. The descriptions below are only examples of how the device may work.')">
                <i class="pe-7s-help1"></i>
            </span>
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
