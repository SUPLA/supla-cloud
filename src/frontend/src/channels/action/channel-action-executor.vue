<template>
    <div>
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
            v-model="actionToExecute"
            v-slot="{possibleAction}">
            <button :class="'btn ' + (requiresParams(actionToExecute) && possibleAction.id == actionToExecute.id ? 'btn-green' : 'btn-default')"
                :disabled="!isConnected || executing"
                @click="executeAction(possibleAction)"
                v-show="!requiresParams(actionToExecute) || possibleAction.id == actionToExecute.id">
                <span v-if="!possibleAction.executing">
                    <i v-if="possibleAction.executed"
                        class="pe-7s-check"></i>
                    <i v-else-if="requiresParams(possibleAction) && possibleAction.id != actionToExecute.id"
                        class="pe-7s-angle-down-circle"></i>
                    {{ possibleAction.executed ? $t('executed') : $t(possibleAction.caption) }}
                </span>
                <button-loading-dots v-else></button-loading-dots>
            </button>
            <button v-if="requiresParams(actionToExecute) && possibleAction.id == actionToExecute.id"
                class="btn btn-grey"
                @click="actionToExecute = {}">
                <i class="pe-7s-close"></i>
                {{ $t('Cancel') }}
            </button>
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
    import ButtonLoadingDots from "../../common/gui/loaders/button-loading-dots.vue";
    import Vue from "vue";
    import TransitionExpand from "../../common/gui/transition-expand";

    export default {
        components: {TransitionExpand, ButtonLoadingDots, ChannelActionChooser},
        props: ['subject'],
        data() {
            return {
                executing: false,
                actionToExecute: {},
                actionToConfirm: false,
            };
        },
        methods: {
            executeAction(action, confirmed = false) {
                if (this.requiresParams(action) && this.actionToExecute.id != action.id) {
                    this.actionToExecute = action;
                    return;
                }
                this.actionToConfirm = false;
                if (action.name == 'OPEN' && !confirmed && ['VALVEOPENCLOSE'].includes(this.subject.function.name)) {
                    if (this.subject.state && (this.subject.state.manuallyClosed || this.subject.state.flooding)) {
                        this.actionToConfirm = action;
                        return;
                    }
                }
                if (action.name == 'TURN_ON' && !confirmed && this.subject.state?.currentOverload) {
                    this.actionToConfirm = action;
                    return;
                }
                this.$set(action, 'executing', true);
                this.executing = true;
                const toSend = Vue.util.extend({}, this.actionToExecute.param);
                toSend.action = action.name;
                this.$http.patch(`${this.endpoint}/${this.subject.id}`, toSend)
                    .then(() => {
                        this.$set(action, 'executed', true);
                        setTimeout(() => this.$set(action, 'executed', false), 3000);
                        setTimeout(() => this.executing = false, 3000);
                        setTimeout(() => EventBus.$emit('channel-state-updated'), 1000);
                    })
                    .finally(() => {
                        this.actionToExecute = {};
                        this.$set(action, 'executing', false);
                        this.executing = false;
                    });
            },
            requiresParams({id}) {
                return id == 50 || id == 80 || id == 2000;
            }
        },
        computed: {
            endpoint() {
                return this.subject.subjectType === 'channel' ? 'channels' : 'channel-groups';
            },
            isConnected() {
                return !this.subject.state || this.subject.state.connected;
            }
        }
    };
</script>

<style lang="scss">
    .possible-actions {
        text-align: center;
        .possible-action {
            margin: 5px 15px;
            display: inline-block;
            .possible-action-params {
                margin: 5px auto;
                max-width: 600px;
            }
            .btn-grey {
                margin-left: .5em;
            }
        }
    }
</style>
