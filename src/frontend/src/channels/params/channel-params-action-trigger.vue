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
                v-for="(trigger, index) in channel.config.actionTriggerCapabilities">
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
    <!-- i18n:['actionTrigger_TURN_ON','actionTriggerDescription_TURN_ON'] -->
    <!-- i18n:['actionTrigger_TURN_OFF','actionTriggerDescription_TURN_OFF'] -->
    <!-- i18n:['actionTrigger_TOGGLE_x1','actionTriggerDescription_TOGGLE_x1'] -->
    <!-- i18n:['actionTrigger_TOGGLE_x2','actionTriggerDescription_TOGGLE_x2'] -->
    <!-- i18n:['actionTrigger_TOGGLE_x3','actionTriggerDescription_TOGGLE_x3'] -->
    <!-- i18n:['actionTrigger_TOGGLE_x4','actionTriggerDescription_TOGGLE_x4'] -->
    <!-- i18n:['actionTrigger_TOGGLE_x5','actionTriggerDescription_TOGGLE_x5'] -->
    <!-- i18n:['actionTrigger_HOLD','actionTriggerDescription_HOLD'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_x1','actionTriggerDescription_SHORT_PRESS_x1'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_x2','actionTriggerDescription_SHORT_PRESS_x2'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_x3','actionTriggerDescription_SHORT_PRESS_x3'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_x4','actionTriggerDescription_SHORT_PRESS_x4'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_x5','actionTriggerDescription_SHORT_PRESS_x5'] -->
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
