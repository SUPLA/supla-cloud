<template>
    <div>
        <CarouselPage
            permanent-carousel-view
            header-i18n="Reactions"
            tile="reaction-tile"
            :endpoint="`channels/${subject.id}/reactions?include=subject,owningChannel`"
            create-new-label-i18n="Create New Reaction"
            list-route="channel"
            details-route="channelReaction"
            id-param-name="reactionId"
            :limit="$user.userData.limits.schedule"
            :new-item-factory="newReactionFactory"
            @count="$emit('count', $event)"/>
    </div>
</template>

<script>
    import ReactionTile from "./reaction-tile";
    import Vue from "vue";
    import CarouselPage from "@/common/pages/carousel-page.vue";

    Vue.component('ReactionTile', ReactionTile);

    export default {
        components: {CarouselPage},
        props: {
            subject: Object,
        },
        methods: {
            newReactionFactory() {
                return {
                    owningChannel: this.subject,
                };
            },
        }
    };
</script>
