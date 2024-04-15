<template>
    <div class="channel-reactions-config container">
        <CarouselPage
            permanent-carousel-view
            header-i18n="Reactions"
            tile="reaction-tile"
            :endpoint="`channels/${subject.id}/reactions?include=subject,owningChannel`"
            create-new-label-i18n="Create new reaction"
            list-route="channel.reactions"
            details-route="channel.reactions.details"
            id-param-name="reactionId"
            :limit="$user.userData.limits.schedule"
            :new-item-factory="newReactionFactory"/>
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
                    enabled: true,
                };
            },
        }
    };
</script>

<style lang="scss">
    .channel-reactions-config {
        min-height: 850px;
        .owning-channel-caption {
            display: none;
        }
    }
</style>
