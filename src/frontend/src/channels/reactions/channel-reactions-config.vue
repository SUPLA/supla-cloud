<template>
    <div>
        <ListPage v-if="listLoaded"
            header-i18n="Reactions"
            tile="reaction-tile"
            endpoint="reactions?include=subject,owningChannel"
            create-new-label-i18n="Create New Reaction"
            :limit="$user.userData.limits.schedule"
            :subject="subject"
            @add="newReaction = {}"/>
        <ChannelReactionModal v-if="newReaction" :subject="subject" v-model="newReaction"
            @cancel="newReaction = undefined" @confirm="addNewReaction($event)"/>
    </div>
</template>

<script>
    import ReactionTile from "./reaction-tile";
    import ListPage from "../../common/pages/list-page";
    import Vue from "vue";
    import ChannelReactionModal from "@/channels/reactions/channel-reaction-modal.vue";
    import {successNotification} from "@/common/notifier";

    Vue.component('ReactionTile', ReactionTile);

    export default {
        components: {ChannelReactionModal, ListPage},
        props: {
            subject: Object,
        },
        data() {
            return {
                newReaction: undefined,
                listLoaded: true,
            };
        },
        methods: {
            addNewReaction(reaction) {
                this.$http.post(`channels/${this.subject.id}/reactions`, reaction)
                    .then(() => {
                        this.newReaction = undefined;
                        successNotification(this.$t('Success'), this.$t('Reakcja została dodana'));
                        this.listLoaded = false;
                        this.$nextTick(() => this.listLoaded = true);
                    });
            }
        }
    };
</script>
