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
            v-slot="{possibleAction}"
            :possible-action-filter="possibleActionFilter">
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
        <modal-confirm v-if="valveOpenConfirm"
            class="modal-warning"
            @confirm="executeAction(valveOpenConfirm, true)"
            @cancel="valveOpenConfirm = false"
            :header="$t('Are you sure?')">
            {{ $t('The valve has been closed in manual or radio mode. Before you open it, make sure it has not been closed due to flooding. To turn off the warning, open the valve manually. Are you sure you want to open it from the application?!') }}
        </modal-confirm>
    </div>
</template>

<script>
    import ChannelActionChooser from "./channel-action-chooser";
    import EventBus from "src/common/event-bus";
    import ButtonLoadingDots from "src/common/gui/loaders/button-loading-dots.vue";
    import Vue from "vue";
    import TransitionExpand from "../../common/gui/transition-expand";

    export default {
        components: {TransitionExpand, ButtonLoadingDots, ChannelActionChooser},
        props: ['subject'],
        data() {
            return {
                executing: false,
                actionToExecute: {},
                valveOpenConfirm: false,
            };
        },
        methods: {
            executeAction(action, confirmed = false) {
                if (this.requiresParams(action) && this.actionToExecute.id != action.id) {
                    this.actionToExecute = action;
                    return;
                }
                if (!confirmed && ['VALVEOPENCLOSE'].includes(this.subject.function.name)) {
                    if (this.subject.state && (this.subject.state.manuallyClosed || this.subject.state.flooding)) {
                        this.valveOpenConfirm = action;
                        return;
                    }
                } else {
                    this.valveOpenConfirm = false;
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
                return id == 50 || id == 80;
            },
            possibleActionFilter(possibleAction) {
                if (this.subject.subjectType === 'channelGroup'
                    && ['CONTROLLINGTHEGATE', 'CONTROLLINGTHEGARAGEDOOR'].indexOf(this.subject.function.name) !== -1) {
                    return !(['OPEN', 'CLOSE'].includes(possibleAction.name));
                }
                return true;
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
        }
    }
</style>
