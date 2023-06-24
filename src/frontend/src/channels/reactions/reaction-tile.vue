<template>
    <square-link :class="'clearfix pointer lift-up ' + (model.enabled ? 'green' : 'grey')" @click="$emit('click')">
        <router-link :to="{name: 'channelReaction', params: {id: this.model.owningChannel.id, reactionId: this.model.id}}"
            class="text-center">
            <h3>{{ triggerCaption }}</h3>
            <fa icon="chevron-down"/>
            <h4>
                {{ $t(model.action.caption) }}
                {{ subjectCaption }}
            </h4>
        </router-link>
    </square-link>
</template>

<script>
    import {triggerHumanizer} from "@/channels/reactions/trigger-humanizer";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";

    export default {
        props: ['model'],
        computed: {
            triggerCaption() {
                return triggerHumanizer(this.model.owningChannel.functionId, this.model.trigger, this);
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
