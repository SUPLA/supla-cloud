<template>
    <channel-action-chooser :subject="subject"
        v-model="actionToExecute"
        v-slot="{possibleAction}">
        <button class="btn btn-default"
            :disabled="executing"
            @click="executeAction(possibleAction)">
            <span v-if="!possibleAction.executing">
                <i :class="'pe-7s-' + (possibleAction.executed ? 'check' : 'rocket')"></i>
                {{ possibleAction.executed ? $t('executed') : $t(possibleAction.caption) }}
            </span>
            <button-loading-dots v-else></button-loading-dots>
        </button>
    </channel-action-chooser>
</template>

<script>
    import ChannelActionChooser from "./channel-action-chooser";
    import EventBus from "src/common/event-bus";
    import ButtonLoadingDots from "src/common/gui/loaders/button-loading-dots.vue";

    export default {
        components: {ButtonLoadingDots, ChannelActionChooser},
        props: ['subject'],
        data() {
            return {
                executing: false,
                actionToExecute: {}
            };
        },
        methods: {
            executeAction(action) {
                this.$set(action, 'executing', true);
                this.executing = true;
                this.$http.patch('channels/' + this.subject.id, {action: action.name})
                    .then(() => {
                        this.$set(action, 'executed', true);
                        setTimeout(() => this.$set(action, 'executed', false), 3000);
                        setTimeout(() => this.executing = false, 3000);
                        setTimeout(() => EventBus.$emit('channel-state-updated'), 1000);
                    })
                    .finally(() => this.$set(action, 'executing', false));
            },
        }
    };
</script>

<style lang="scss">

</style>
