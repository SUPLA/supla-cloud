<template>
    <div class="channel-action-executor">
        <!--        <div class="flex-left-full-width mb-3">-->
        <!--            <div>-->
        <!--                <function-icon :model="subject" width="80"></function-icon>-->
        <!--            </div>-->
        <!--            <div class="full pl-2"><h3 class="m-0">{{ subject.caption }}</h3></div>-->
        <!--        </div>-->
        <transition-expand>
            <div class="row"
                v-if="!isConnected">
                <div class="col-md-8 col-md-offset-2">
                    <div class="alert alert-warning text-center">
                        {{ $t('Cannot execute an action when the device is disconnected.') }}
                    </div>
                </div>
            </div>
        </transition-expand>
        <channel-action-chooser :subject="subject"
            :disabled="!isConnected"
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
            <span v-if="actionToConfirm.name === 'OPEN'">
                {{ $t('The valve has been closed in manual or radio mode. Before you open it, make sure it has not been closed due to flooding. To turn off the warning, open the valve manually. Are you sure you want to open it remotely?!') }}
            </span>
            <span v-else-if="actionToConfirm.name === 'TURN_ON'">
                {{ $t('The relay has been turned off due to a current overload. Before you turn it on, make sure you took required steps to solve the problem. Are you sure you want to turn it on remotely?') }}
            </span>
        </modal-confirm>
    </div>
</template>

<script>
    import ChannelActionChooser from "./channel-action-chooser";
    import EventBus from "../../common/event-bus";
    import TransitionExpand from "../../common/gui/transition-expand";
    import ChannelFunctionAction from "../../common/enums/channel-function-action";
    import {removeByValue} from "../../common/utils";

    export default {
        components: {TransitionExpand, ChannelActionChooser},
        props: ['subject'],
        data() {
            return {
                executing: [],
                executed: [],
                actionToConfirm: false,
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
                    const toSend = {action: action.name, ...action.param};
                    this.$http.patch(`${this.endpoint}/${this.subject.id}`, toSend)
                        .then(() => {
                            this.executed.push(action.id);
                            setTimeout(() => removeByValue(this.executed, action.id), 3000);
                            setTimeout(() => EventBus.$emit('channel-state-updated'), 1000);
                        })
                        .finally(() => {
                            removeByValue(this.executing, action.id);
                        });
                }
            },
            confirmExecution(action) {
                if (action.id === ChannelFunctionAction.OPEN && ['VALVEOPENCLOSE'].includes(this.subject.function.name)) {
                    if (this.subject.state && (this.subject.state.manuallyClosed || this.subject.state.flooding)) {
                        this.actionToConfirm = action;
                        return false;
                    }
                }
                if (action.id === ChannelFunctionAction.TURN_ON && this.subject.state?.currentOverload) {
                    this.actionToConfirm = action;
                    return false;
                }
                return true;
            },
        },
        computed: {
            endpoint() {
                return this.subject.subjectType === 'channel' ? 'channels' : 'channel-groups';
            },
            isConnected() {
                return !this.subject.state || this.subject.state.connected;
            },
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
