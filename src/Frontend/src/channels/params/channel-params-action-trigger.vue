<template>
    <div>
        <h4>{{ $t('Triggers') }}</h4>
        <p>
            {{ $t('Actual behavior of the triggers depend on the device you use and its configuration. The descriptions below are only examples of how the device may work.') }}
        </p>
        <div class="panel-group hovered"
            role="tablist"
            aria-multiselectable="true">
            <div :class="'panel panel-' + (channel.config.actions[behavior] ? 'success' : 'default')"
                v-for="(behavior, index) in channel.config.supportedBehaviors">
                <div class="panel-heading"
                    role="tab"
                    :id="'heading' + behavior">
                    <div class="panel-title"
                        @click="toggleExpand(behavior)">
                        <a role="button"
                            :aria-expanded="!!expanded[behavior]"
                            :aria-controls="'collapse' + behavior">
                            {{ $t('Action') }} {{ index + 1 }}
                            <span class="small">{{ $t('actionTriggerBehavior_' + behavior) }}</span>
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
                            <p class="small text-muted">{{ $t('actionTriggerBehaviorDescription_' + behavior) }}</p>
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
