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
                            <action-trigger-single-action-selector v-model="channel.config.actions[trigger]"
                                @input="$emit('change')"></action-trigger-single-action-selector>
                        </div>
                    </transition-expand>
                </div>
            </div>
        </div>
    </div>
    <!-- i18n:['actionTrigger_TURN_ON','actionTriggerDescription_TURN_ON'] -->
    <!-- i18n:['actionTrigger_TURN_OFF','actionTriggerDescription_TURN_OFF'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X1','actionTriggerDescription_TOGGLE_X1'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X2','actionTriggerDescription_TOGGLE_X2'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X3','actionTriggerDescription_TOGGLE_X3'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X4','actionTriggerDescription_TOGGLE_X4'] -->
    <!-- i18n:['actionTrigger_TOGGLE_X5','actionTriggerDescription_TOGGLE_X5'] -->
    <!-- i18n:['actionTrigger_HOLD','actionTriggerDescription_HOLD'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X1','actionTriggerDescription_SHORT_PRESS_X1'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X2','actionTriggerDescription_SHORT_PRESS_X2'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X3','actionTriggerDescription_SHORT_PRESS_X3'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X4','actionTriggerDescription_SHORT_PRESS_X4'] -->
    <!-- i18n:['actionTrigger_SHORT_PRESS_X5','actionTriggerDescription_SHORT_PRESS_X5'] -->
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";
    import ActionTriggerSingleActionSelector from "./action-trigger-single-action-selector";

    export default {
        components: {TransitionExpand, ActionTriggerSingleActionSelector},
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

<style scoped>
    .panel-group .panel-heading {
        cursor: pointer;
    }
</style>
