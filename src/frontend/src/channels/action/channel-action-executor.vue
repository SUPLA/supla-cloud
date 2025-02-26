<template>
    <div class="channel-action-executor">
        <transition-expand>
            <div class="row"
                v-if="!isConnected">
                <div class="col-md-10 col-md-offset-1">
                    <div class="alert alert-warning text-center">
                        {{ $t('Cannot execute an action when the channel is not available.') }}
                    </div>
                </div>
            </div>
        </transition-expand>
        <channel-action-chooser :subject="subject"
            :disabled="disabled || !isConnected"
            :executor-mode="true"
            :executing="executing"
            :executed="executed"
            @input="executeAction($event)">
        </channel-action-chooser>
        <modal-confirm v-if="actionToConfirm"
            class="modal-warning"
            @confirm="executeAction(actionToConfirm, true)"
            @cancel="actionToConfirm = false"
            :header="$t('Are you sure?')">
            <span v-if="actionToConfirm.id === ChannelFunctionAction.OPEN">
                <span v-if="actionToConfirm.confirmationReason === 'flooding'">{{ $t('valveOpenConfirm_flooding') }}</span>
                <span v-else>{{ $t('valveOpenConfirm_manual') }}</span>
            </span>
            <span v-else-if="actionToConfirm.id === ChannelFunctionAction.TURN_ON">
                {{ $t('The relay has been turned off due to a current overload. Before you turn it on, make sure you took required steps to solve the problem. Are you sure you want to turn it on remotely?') }}
            </span>
        </modal-confirm>
    </div>
</template>

<script>
    import ChannelActionChooser from "./channel-action-chooser";
    import TransitionExpand from "../../common/gui/transition-expand";
    import ChannelFunctionAction from "../../common/enums/channel-function-action";
    import {removeByValue} from "../../common/utils";
    import ActionableSubjectType from "../../common/enums/actionable-subject-type";
    import {mapStores} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";
    import ChannelFunction from "@/common/enums/channel-function";

    export default {
        components: {TransitionExpand, ChannelActionChooser},
        props: {
            subject: Object,
            disabled: {
                type: Boolean,
                default: false,
            }
        },
        data() {
            return {
                executing: [],
                executed: [],
                actionToConfirm: false,
                ChannelFunctionAction,
            };
        },
        methods: {
            isBusy(action) {
                return this.isExecuting(action) || this.isExecuted(action);
            },
            isExecuting(action) {
                return this.executing.includes(action.id);
            },
            isExecuted(action) {
                return this.executed.includes(action.id);
            },
            executeAction(action, confirmed = false) {
                if (this.isBusy(action)) {
                    return;
                }
                if (confirmed || this.confirmExecution(action)) {
                    this.actionToConfirm = false;
                    this.executing.push(action.id);
                    const toSend = {action: action.id, ...action.param};
                    this.$http.patch(`${this.endpoint}/${this.subject.id}`, toSend)
                        .then(() => {
                            this.executed.push(action.id);
                            setTimeout(() => removeByValue(this.executed, action.id), 3000);
                            setTimeout(() => this.channelsStore.fetchStates(), 1000);
                        })
                        .finally(() => {
                            removeByValue(this.executing, action.id);
                        });
                }
            },
            confirmExecution(action) {
                if (action.id === ChannelFunctionAction.OPEN && [ChannelFunction.VALVEOPENCLOSE].includes(this.subject.functionId)) {
                    if (this.state.manuallyClosed || this.state.flooding) {
                        this.actionToConfirm = {...action, confirmationReason: this.state.manuallyClosed ? 'manually' : 'flooding'};
                        return false;
                    }
                }
                if (action.id === ChannelFunctionAction.TURN_ON && this.state.currentOverload) {
                    this.actionToConfirm = action;
                    return false;
                }
                return true;
            },
        },
        computed: {
            endpoint() {
                switch (this.subject.ownSubjectType) {
                    case ActionableSubjectType.SCENE:
                        return 'scenes';
                    case ActionableSubjectType.CHANNEL_GROUP:
                        return 'channel-groups';
                    default:
                        return 'channels';
                }
            },
            isConnected() {
                return this.subject.ownSubjectType !== 'channel' || this.channelsStore.all[this.subject.id]?.state?.connectedCode === 'CONNECTED';
            },
            state() {
                return (this.subject.ownSubjectType === 'channel' && this.channelsStore.all[this.subject.id]?.state) || {};
            },
            ...mapStores(useChannelsStore),
        }
    };
</script>

<style lang="scss">
    .channel-action-executor {
        .channel-action-chooser {
            .channel-action-immediate-indicator {
                visibility: visible;
            }
        }
    }
</style>
