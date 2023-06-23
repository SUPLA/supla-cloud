<template>
    <div>
        <ListPage header-i18n="Reactions"
            tile="reaction-tile"
            endpoint="reactions?include=subject,owningChannel"
            create-new-label-i18n="Create New Reaction"
            :limit="$user.userData.limits.schedule"
            :subject="subject"
            @add="newReaction = {}"/>
        <ChannelReactionModal v-if="newReaction" @cancel="newReaction = undefined" :subject="subject" v-model="newReaction"/>
    </div>
</template>

<script>
    import ReactionTile from "./reaction-tile";
    import ListPage from "../../common/pages/list-page";
    import Vue from "vue";
    import ChannelReactionModal from "@/channels/reactions/channel-reaction-modal.vue";

    Vue.component('ReactionTile', ReactionTile);

    export default {
        components: {ChannelReactionModal, ListPage},
        props: {
            subject: Object,
        },
        data() {
            return {
                newReaction: undefined,
            };
        },
    };
</script>
