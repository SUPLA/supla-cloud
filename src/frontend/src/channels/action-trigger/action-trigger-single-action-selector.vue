<template>
    <div class="channel-params-action-trigger-selector">
        <div class="form-group">
            <subject-dropdown v-model="subject"
                @input="onSubjectChange()"
                :filter="filterOutNotSupportedSubjects"
                channels-dropdown-params="io=output&hasFunction=1">
                <template #other="props">
                    <action-trigger-other-actions-dropdown
                        v-model="props.subject"
                        :filter="filterOtherActions"
                        @input="props.onInput"></action-trigger-other-actions-dropdown>
                </template>
                <div v-if="subject && subject.ownSubjectType !== 'other'" class="mt-3">
                    <channel-action-chooser :subject="subject"
                        @input="onActionChange()"
                        :alwaysSelectFirstAction="true"
                        v-model="action"/>
                </div>
            </subject-dropdown>
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
    import {subjectEndpointUrl} from "@/common/utils";
    import ChannelFunction from "@/common/enums/channel-function";

    export default {
        components: {ActionTriggerOtherActionsDropdown, ChannelActionChooser, SubjectDropdown},
        props: ['value', 'trigger', 'channel'],
        data() {
            return {
                subject: undefined,
                action: undefined,
                channelSavedListener: undefined,
            };
        },
        beforeMount() {
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
                        if (this.value?.action?.id) {
                            const otherAction = this.value.action.id;
                            this.subject = {id: otherAction, ownSubjectType: ActionableSubjectType.OTHER};
                            this.action = this.value.action;
                        }
                    } else if (!this.subject || this.value.subjectId !== this.subject.id) {
                        this.$http.get(subjectEndpointUrl(this.value))
                            .then(response => {
                                this.subject = response.body;
                                this.action = this.value?.action;
                            });
                    } else {
                        this.action = this.value?.action;
                    }
                } else {
                    this.subject = undefined;
                    this.action = undefined;
                }
            },
            onSubjectChange() {
                if (this.subject?.ownSubjectType === ActionableSubjectType.OTHER) {
                    this.action = {id: this.subject.id};
                }
                if (!this.subject) {
                    this.action = undefined;
                }
                this.onActionChange();
            },
            onActionChange() {
                if (this.subject) {
                    this.$emit('input', {
                        subjectId: this.subject.ownSubjectType === ActionableSubjectType.OTHER ? undefined : this.subject.id,
                        subjectType: this.subject.ownSubjectType,
                        action: this.action,
                    });
                }
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
                return action.id !== ChannelFunctionAction.AT_DISABLE_LOCAL_FUNCTION || this.disablesLocalOperation(this.trigger);
            },
            filterOutNotSupportedSubjects(subject) {
                const notSupportedInAt = [
                    ChannelFunction.HVAC_THERMOSTAT,
                    ChannelFunction.HVAC_DOMESTIC_HOT_WATER,
                    ChannelFunction.HVAC_THERMOSTAT_DIFFERENTIAL,
                    ChannelFunction.HVAC_THERMOSTAT_HEAT_COOL,
                ];
                return !notSupportedInAt.includes(subject.functionId);
            },
        },
        watch: {
            value() {
                this.onValueChanged();
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
