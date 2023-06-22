<template>
    <div class="channel-reactions-config">
        <PendingChangesPage @cancel="cancelChanges()" @save="saveReactions()" :is-pending="hasPendingChanges">
            <div v-for="(r, $index) in reactions" :key="$index"
                :class="['channel-reaction-item', {'channel-reaction-item-error': displayValidationErrors && r.reaction.isValid === false}]">
                <ChannelReaction :subject="subject" v-model="r.reaction" @input="hasPendingChanges = true"/>
            </div>
            <div class="text-right">
                <a @click="addReaction()"
                    class="btn btn-green btn-lg btn-wrapped">
                    <i class="pe-7s-plus"></i>
                    {{ $t('Add new reaction') }}
                </a>
            </div>
        </PendingChangesPage>
    </div>
</template>

<script>
    import ChannelReaction from "@/channels/reactions/channel-reaction.vue";
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import {warningNotification} from "@/common/notifier";

    export default {
        components: {PendingChangesPage, ChannelReaction},
        props: {
            subject: Object,
        },
        data() {
            return {
                hasPendingChanges: false,
                displayValidationErrors: false,
                reactions: [],
            }
        },
        methods: {
            addReaction() {
                this.reactions.push({reaction: {}});
            },
            saveReactions() {
                if (this.reactions.find(r => r.reaction.isValid === false)) {
                    this.displayValidationErrors = true;
                    warningNotification(this.$t('Error'), this.$t('At least one reaction is not valid.'));
                    return;
                }
                this.displayValidationErrors = false;
                const reactions = this.reactions.map(r => r.reaction);
                this.$http.put(`channels/${this.subject.id}/reactions`, reactions);
            }
        }
    }
</script>

<style lang="scss">
    @import "../../styles/variables";

    .channel-reaction-item {
        padding: 1em;
        padding-bottom: 1.5em;
        margin: 1em 0;
        border-bottom: 1px solid $supla-grey-light;
        &-error {
            border: 1px solid $supla-red;
        }
    }
</style>
