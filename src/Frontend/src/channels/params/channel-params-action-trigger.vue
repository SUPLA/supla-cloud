<template>
    <div>
        <div class="panel-group hovered"
            role="tablist"
            aria-multiselectable="true">
            <div :class="'panel panel-' + (channel.config.actions[behavior] ? 'success' : 'default')"
                v-for="behavior in channel.config.supportedBehaviors">
                <div class="panel-heading"
                    role="tab"
                    :id="'heading' + behavior">
                    <div class="panel-title"
                        @click="toggleExpand(behavior)">
                        <a role="button"
                            :aria-expanded="!!expanded[behavior]"
                            :aria-controls="'collapse' + behavior">
                            {{ $t('actionTriggerBehavior_' + behavior) }}
                        </a>
                    </div>
                </div>
                <div class="panel-collapse"
                    :id="'collapse' + behavior"
                    role="tabpanel"
                    :aria-labelledby="'heading' + behavior">
                    <transition-expand>
                        <div class="panel-body"
                            v-if="expanded[behavior]">
                            <channel-params-action-trigger-selector v-model="channel.config.actions[behavior]"
                                @input="$emit('change')"></channel-params-action-trigger-selector>
                        </div>
                    </transition-expand>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";
    import ChannelParamsActionTriggerSelector from "./channel-params-action-trigger-selector";

    export default {
        components: {ChannelParamsActionTriggerSelector, TransitionExpand},
        props: ['channel'],
        data() {
            return {
                expanded: {}
            };
        },
        mounted() {
            if (!this.channel.config.actions) {
                this.$set(this.channel.config, 'actions', {});
            }
        },
        methods: {
            toggleExpand(behavior) {
                this.$set(this.expanded, behavior, !this.expanded[behavior]);
            }
        }
    };
</script>

<style>
    .panel-group .panel-heading {
        cursor: pointer;
    }
</style>
