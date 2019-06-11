<template>
    <channel-action-chooser :subject="subject"
        v-model="actionToExecute"></channel-action-chooser>
</template>

<script>
    import ChannelActionChooser from "./channel-action-chooser";

    export default {
        components: {ChannelActionChooser},
        props: ['subject'],
        data() {
            return {
                actionToExecute: {}
            };
        }
    };
</script>

<style lang="scss">

</style>
