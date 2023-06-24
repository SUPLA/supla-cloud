<template>
    <div>
        <CarouselPage v-if="listLoaded"
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
            @add="newReaction = {}"/>
    </div>
</template>

<script>
    import ReactionTile from "./reaction-tile";
    import Vue from "vue";
    import {successNotification} from "@/common/notifier";
    import CarouselPage from "@/common/pages/carousel-page.vue";

    Vue.component('ReactionTile', ReactionTile);

    export default {
        components: {CarouselPage},
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
            newReactionFactory() {
                return {
                    owningChannel: this.subject,
                };
            },
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
