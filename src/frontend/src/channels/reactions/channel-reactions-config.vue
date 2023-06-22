<template>
    <div class="channel-reactions-config">
        <ChannelReactionConditionChooser :subject="subject" v-model="condition"/>
    </div>
</template>

<script>
    import ChannelReactionConditionChooser from "@/channels/reactions/channel-reaction-condition-chooser.vue";

    export default {
        components: {ChannelReactionConditionChooser},
        props: {
            subject: Object,
            reactions: Array,
        },
        data() {
            return {
                condition: {},
            }
        }
    }
</script>

<style lang="scss">
    .channel-reactions-config {
        min-height: 500px;
    }
</style>
