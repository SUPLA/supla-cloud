<template>
    <div>
        <h4>{{ $t('Triggers') }}</h4>
        <p>
            {{ $t('Actual behavior of the triggers depend on the device you use and its configuration. The descriptions below are only examples of how the device may work.') }}
        </p>
        <div class="panel-group hovered"
            role="tablist"
            aria-multiselectable="true">
            <div :class="'panel panel-' + (channel.config.actions[trigger] ? 'success' : 'default')"
                :key="index"
                v-for="(trigger, index) in channel.config.supportedTriggers">
                <div class="panel-heading"
                    role="tab"
                    :id="'heading' + trigger">
                    <div class="panel-title"
                        @click="toggleExpand(trigger)">
                        <a role="button"
                            :aria-expanded="!!expanded[trigger]"
                            :aria-controls="'collapse' + trigger">
                            {{ $t('Action') }} {{ index + 1 }}
                            <span class="small">{{ $t('actionTrigger_' + trigger) }}</span>
                        </a>
                    </div>
                </div>
                <div class="panel-collapse"
                    :id="'collapse' + trigger"
                    role="tabpanel"
                    :aria-labelledby="'heading' + trigger">
                    <transition-expand>
                        <div class="panel-body"
                            v-if="expanded[trigger]">
                            <p class="small text-muted">{{ $t('actionTriggerDescription_' + trigger) }}</p>
                            <channel-params-action-trigger-selector v-model="channel.config.actions[trigger]"
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
            toggleExpand(trigger) {
                this.$set(this.expanded, trigger, !this.expanded[trigger]);
            }
        }
    };
</script>

<style>
    .panel-group .panel-heading {
        cursor: pointer;
    }
</style>
