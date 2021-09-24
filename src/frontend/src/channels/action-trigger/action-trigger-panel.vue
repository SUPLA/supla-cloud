<template>
    <div>
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
                            {{ $t('actionTrigger_' + trigger) }}
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
                            <action-trigger-single-action-selector v-model="channel.config.actions[trigger]"
                                @input="$emit('change')"></action-trigger-single-action-selector>
                        </div>
                    </transition-expand>
                </div>
            </div>
        </div>
    </div>
    <!-- i18n:['actionTrigger_TURN_ON'] -->
    <!-- i18n:['actionTrigger_TURN_OFF'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X1'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X2'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X3'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X4'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X5'] -->
    <!-- i18n:['actionTrigger_HOLD'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X1'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X2'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X3'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X4'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X5'] -->
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";
    import ActionTriggerSingleActionSelector from "./action-trigger-single-action-selector";
    import {forIn} from "lodash";
    import EventBus from "@/common/event-bus";

    export default {
        components: {TransitionExpand, ActionTriggerSingleActionSelector},
        props: ['channel'],
        data() {
            return {
                expanded: {},
                channelSavedListener: undefined,
            };
        },
        mounted() {
            // clear unsupported configs
            if (this.channel.config.actions) {
                forIn(this.channel.config.actions, (value, triggerName) => this.initAtAction(triggerName));
            }
            this.channelSavedListener = () => this.collapseEmptyActions();
            EventBus.$on('channel-updated', this.channelSavedListener);
        },
        beforeDestroy() {
            EventBus.$off('channel-updated', this.channelSavedListener);
        },
        methods: {
            initAtAction(triggerName) {
                if ((this.channel.config.actionTriggerCapabilities || []).includes(triggerName)) {
                    this.toggleExpand(triggerName);
                } else {
                    delete this.channel.config.actions[triggerName];
                }
            },
            toggleExpand(trigger) {
                this.$set(this.expanded, trigger, !this.expanded[trigger]);
            },
            collapseEmptyActions() {
                for (const triggerName of (this.channel.config.actionTriggerCapabilities || [])) {
                    if (!this.channel.config.actions[triggerName]) {
                        this.$set(this.expanded, triggerName, false);
                    }
                }
            },
        }
    };
</script>

<style scoped>
    .panel-group .panel-heading {
        cursor: pointer;
    }
</style>