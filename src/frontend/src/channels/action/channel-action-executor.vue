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
            v-model="actionToExecute">
        </channel-action-chooser>
        <transition-expand>
            <button class="btn btn-default"
                v-if="actionToExecute.id || executed"
                :disabled="!isConnected || executing"
                @click="executeAction()">
                <span v-if="!executing">
                    <i v-if="executed"
                        class="pe-7s-check"></i>
                    {{ executed ? $t('executed') : $t('Wykonaj') }}
                </span>
                <button-loading-dots v-else></button-loading-dots>
            </button>
        </transition-expand>
        <modal-confirm v-if="actionToConfirm"
            class="modal-warning"
            @confirm="executeAction(true)"
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
    import TransitionExpand from "../../common/gui/transition-expand";

    export default {
        components: {TransitionExpand, ButtonLoadingDots, ChannelActionChooser},
        props: ['subject'],
        data() {
            return {
                executing: false,
                executed: false,
                actionToExecute: {},
                actionToConfirm: false,
            };
        },
        methods: {
            executeAction(confirmed = false) {
                this.actionToConfirm = false;
                if (this.actionToExecute.name == 'OPEN' && !confirmed && ['VALVEOPENCLOSE'].includes(this.subject.function.name)) {
                    if (this.subject.state && (this.subject.state.manuallyClosed || this.subject.state.flooding)) {
                        this.actionToConfirm = this.actionToExecute;
                        return;
                    }
                }
                if (this.actionToExecute.name == 'TURN_ON' && !confirmed && this.subject.state?.currentOverload) {
                    this.actionToConfirm = this.actionToExecute;
                    return;
                }
                this.executing = true;
                const toSend = {action: this.actionToExecute.name, ...this.actionToExecute.param};
                this.$http.patch(`${this.endpoint}/${this.subject.id}`, toSend)
                    .then(() => {
                        this.executed = true;
                        setTimeout(() => this.executed = false, 3000);
                        setTimeout(() => this.executing = false, 3000);
                        setTimeout(() => EventBus.$emit('channel-state-updated'), 1000);
                    })
                    .finally(() => {
                        this.actionToExecute = {};
                        this.executing = false;
                    });
            },
            requiresParams({name}) {
                return ['REVEAL_PARTIALLY', 'SHUT_PARTIALLY', 'OPEN_PARTIALLY', 'SET_RGBW_PARAMETERS'].includes(name);
            },
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
