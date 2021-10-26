<template>
    <div class="channel-params-action-trigger-selector">
        <div :class="{'form-group': !subject}">
            <subject-dropdown v-model="subject"
                @input="onSubjectChange()"
                channels-dropdown-params="io=output&hasFunction=1">
                <template #other="props">
                    <action-trigger-other-actions-dropdown
                        v-model="props.subject"
                        :filter="filterOtherActions"
                        @input="props.onInput"></action-trigger-other-actions-dropdown>
                </template>
            </subject-dropdown>
        </div>
        <div v-if="subject && subject.subjectType !== 'other'">
            <channel-action-chooser :subject="subject"
                @input="onActionChange()"
                v-model="action"></channel-action-chooser>
        </div>
        <div v-if="subject && subject.subjectType === 'other' && subject.id === 'copyChannelState'">
            <copy-channel-state-source-target-selector
                :subject="subject"
                @input="onActionChange($event)"
                v-model="action.param"></copy-channel-state-source-target-selector>
        </div>
        <button v-if="value"
            type="button"
            class="btn btn-default btn-block mt-3"
            @click="clearAction()">
            <i class="pe-7s-close"></i>
            {{ $t('Clear') }}
        </button>
    </div>
</template>

<script>
    import SubjectDropdown from "../../devices/subject-dropdown";
    import ChannelActionChooser from "../action/channel-action-chooser";
    import EventBus from "@/common/event-bus";
    import ActionTriggerOtherActionsDropdown from "@/channels/action-trigger/action-trigger-other-actions-dropdown";
    import ChannelFunctionAction from "@/common/enums/channel-function-action";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";
    import CopyChannelStateSourceTargetSelector from "@/channels/action-trigger/copy-channel-state-source-target-selector";
    import {subjectEndpointUrl} from "@/common/utils";

    export default {
        components: {CopyChannelStateSourceTargetSelector, ActionTriggerOtherActionsDropdown, ChannelActionChooser, SubjectDropdown},
        props: ['value', 'trigger', 'channel'],
        data() {
            return {
                subject: undefined,
                action: undefined,
                channelSavedListener: undefined,
            };
        },
        mounted() {
            this.channelSavedListener = () => this.onValueChanged();
            EventBus.$on('channel-updated', this.channelSavedListener);
            this.onValueChanged();
        },
        beforeDestroy() {
            EventBus.$off('channel-updated', this.channelSavedListener);
        },
        methods: {
            onValueChanged() {
                if (this.value?.subjectType) {
                    if (this.value.subjectType === ActionableSubjectType.OTHER) {
                        if (this.value.action?.param?.action) {
                            const otherAction = this.value.action.param.action;
                            this.subject = {id: otherAction, subjectType: ActionableSubjectType.OTHER};
                            this.action = this.value.action;
                        }
                    } else if (!this.subject || this.value.subjectId !== this.subject.id) {
                        this.$http.get(subjectEndpointUrl(this.value))
                            .then(response => this.subject = response.body)
                            .then(() => this.action = this.value.action);
                    }
                } else {
                    this.subject = undefined;
                    this.action = undefined;
                }
            },
            onSubjectChange() {
                if (this.subject?.subjectType === ActionableSubjectType.OTHER) {
                    this.action = {
                        id: ChannelFunctionAction.GENERIC,
                        param: {action: this.subject.id},
                    };
                    this.onActionChange();
                }
            },
            onActionChange(a) {
                console.log('action', this.action, a);
                if (this.isActionFullySpecified()) {
                    this.$emit('input', {
                        subjectId: this.subject.subjectType === ActionableSubjectType.OTHER ? undefined : this.subject.id,
                        subjectType: this.subject.subjectType,
                        action: this.action,
                    });
                } else {
                    this.$emit('input');
                }
            },
            isActionFullySpecified() {
                if (this.action?.id) {
                    if (this.action.id === 10000) {
                        const genericActionName = this.action.param?.action;
                        if (genericActionName) {
                            if (genericActionName === 'copyChannelState') {
                                return !!this.action.param.sourceChannelId;
                            }
                            return true;
                        } else {
                            return false;
                        }
                    }
                    return true;
                }
                return false;
            },
            clearAction() {
                this.$emit('input');
                this.subject = undefined;
                this.action = undefined;
            },
            disablesLocalOperation(trigger) {
                return this.channel.config.disablesLocalOperation?.includes(trigger);
            },
            filterOtherActions(action) {
                return action.id !== 'disableLocalFunction' || this.disablesLocalOperation(this.trigger);
            }
        }
    };
</script>

<style lang="scss">
    .channel-params-action-trigger-selector {
        .possible-action-params {
            .well > div {
                clear: both;
                width: 100%;
            }
        }
    }
</style>
