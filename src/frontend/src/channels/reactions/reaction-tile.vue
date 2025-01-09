<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? 'green' : 'grey')" @click="$emit('click')">
        <router-link :to="{name: 'channel.reactions.details', params: {id: this.model.owningChannel.id, reactionId: this.model.id}}"
            class="text-center">
            <h2 class="owning-channel-caption">{{ owningChannelCaption }}</h2>
            <h3>{{ triggerCaption }}</h3>
            <fa icon="chevron-down"/>
            <h4>
                {{ actionCaption(model.action, model) }}
                {{ subjectCaption }}
            </h4>
        </router-link>
    </square-link>
</template>

<script>
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";
    import {reactionTriggerCaption} from "@/channels/reactions/channel-function-triggers";
    import {channelTitle} from "@/common/filters";
    import {actionCaption} from "../channel-helpers";

    export default {
        methods: {actionCaption},
        props: ['model'],
        computed: {
            triggerCaption() {
                return reactionTriggerCaption(this.model);
            },
            owningChannelCaption() {
                return channelTitle(this.model.owningChannel);
            },
            subjectCaption() {
                if (this.model.subject.ownSubjectType === ActionableSubjectType.NOTIFICATION) {
                    return '';
                }
                return this.model.subject.caption || `ID${this.model.subject.id} ${this.$t(this.model.subject.function.caption)}`;
            },
        }
    };
</script>

<style lang="scss" scoped>
    .active > h3:before {
        content: ' ';
        width: 20px;
        float: right;
        height: 5px;
    }
</style>
