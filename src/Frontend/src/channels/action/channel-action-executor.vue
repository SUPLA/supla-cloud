<template>
    <channel-action-chooser :subject="subject"
        v-model="actionToExecute"
        v-slot="{possibleAction}">
        <button class="btn btn-white">
            {{ $t(possibleAction.caption) }}
        </button>
    </channel-action-chooser>
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
