<template>
    <div class="channel-action-chooser">
        <div class="w-100 mb-3">
            <div :class="['panel-group panel-accordion', {'panel-accordion-disabled': disabled}]">
                <div :class="['panel panel-default', {'panel-success': isSelected(possibleAction.id), 'action-without-params': !ChannelFunctionAction.requiresParams(possibleAction.id)}]"
                    v-for="possibleAction in actionsToShow"
                    :key="possibleAction.id">
                    <div class="panel-heading"
                        @click="changeAction(possibleAction)">
                        <a role="button"
                            tabindex="0"
                            @keydown.enter.stop="changeAction(possibleAction)"
                            class="text-inherit">
                            {{ $t(possibleAction.caption) }}
                        </a>
                        <div>
                            <button-loading-dots v-if="executing.includes(possibleAction.id)"></button-loading-dots>
                            <span v-else-if="isSelected(possibleAction.id)"
                                class="glyphicon glyphicon-ok">
                            </span>
                            <span v-else-if="ChannelFunctionAction.requiresParams(possibleAction.id)"
                                class="glyphicon glyphicon-cog channel-action-immediate-indicator"></span>
                            <span v-else
                                class="glyphicon glyphicon-play channel-action-immediate-indicator"></span>
                        </div>
                    </div>
                    <transition-expand>
                        <div class="panel-body"
                            v-if="ChannelFunctionAction.requiresParams(possibleAction.id) && action && action.id === possibleAction.id">
                            <div v-if="[ChannelFunctionAction.REVEAL_PARTIALLY, ChannelFunctionAction.SHUT_PARTIALLY, ChannelFunctionAction.OPEN_PARTIALLY, ChannelFunctionAction.CLOSE_PARTIALLY].includes(action.id)">
                                <rolette-shutter-partial-percentage v-model="param"
                                    @input="paramsChanged()"></rolette-shutter-partial-percentage>
                            </div>
                            <div v-if="action.id === ChannelFunctionAction.SET_RGBW_PARAMETERS">
                                <rgbw-parameters-setter v-model="param"
                                    @input="paramsChanged()"
                                    :subject="subject"></rgbw-parameters-setter>
                            </div>
                            <div v-if="action.id === ChannelFunctionAction.SET">
                                <digiglass-parameters-setter v-if="subject.function.name.match(/^DIGIGLASS.+/)"
                                    v-model="param"
                                    @input="paramsChanged()"
                                    :subject="subject"></digiglass-parameters-setter>
                            </div>
                            <div v-if="action.id === ChannelFunctionAction.COPY">
                                <channels-id-dropdown v-model="param.sourceChannelId"
                                    :hide-none="true"
                                    @input="paramsChanged()"
                                    :params="`function=${subject.function.id}&skipIds=${(subject.subjectType === 'channel' && subject.id) || ''}`"></channels-id-dropdown>
                            </div>
                            <div v-if="executorMode"
                                class="mt-3">
                                <button :class="['btn btn-block btn-execute', {'btn-grey': !isSelected(possibleAction.id), 'btn-green': isSelected(possibleAction.id)}]"
                                    type="button"
                                    :disabled="!paramsSet || executing.includes(action.id)"
                                    @click="updateModel()">
                                    <span
                                        class="text-inherit">
                                        {{ executed.includes(action.id) ? $t('executed') : $t('Execute') }}
                                    </span>
                                    <span>
                                        <button-loading-dots v-if="executing.includes(possibleAction.id)"></button-loading-dots>
                                        <span v-else-if="isSelected(possibleAction.id)"
                                            class="glyphicon glyphicon-ok">
                                        </span>
                                        <span v-else
                                            class="glyphicon glyphicon-play channel-action-immediate-indicator"></span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </transition-expand>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";
    import RoletteShutterPartialPercentage from "./rolette-shutter-partial-percentage";
    import RgbwParametersSetter from "./rgbw-parameters-setter";
    import Vue from "vue";
    import DigiglassParametersSetter from "./digiglass-parameters-setter";
    import ChannelsIdDropdown from "../../devices/channels-id-dropdown";
    import ChannelFunctionAction from "../../common/enums/channel-function-action";

    export default {
        components: {
            ChannelsIdDropdown,
            DigiglassParametersSetter,
            RgbwParametersSetter,
            RoletteShutterPartialPercentage,
            TransitionExpand
        },
        props: {
            subject: {type: Object},
            value: {type: Object},
            disabled: {type: Boolean, default: false},
            possibleActionFilter: {type: Function, required: false, default: () => true},
            executorMode: {type: Boolean, default: false},
            executing: {type: Array, default: () => []},
            executed: {type: Array, default: () => []},
        },
        data() {
            return {
                action: {},
                param: {},
                paramHistory: {},
                paramsSet: false,
                ChannelFunctionAction,
            };
        },
        mounted() {
            this.updateAction();
            this.selectFirstActionIfOnlyOne();
        },
        methods: {
            changeAction(action) {
                if (action.id === this.action?.id && this.executorMode && ChannelFunctionAction.requiresParams(action.id)) {
                    return this.changeAction({}); // collapse params panel
                }
                if (this.action && this.paramsSet) {
                    this.paramHistory[this.action.id] = {...this.param};
                }
                this.action = action;
                this.resetParams();
                if (!this.executorMode || !ChannelFunctionAction.requiresParams(this.action.id)) {
                    this.updateModel();
                }
            },
            resetParams() {
                if (this.paramHistory[this.action.id]) {
                    this.param = {...this.paramHistory[this.action.id]};
                    this.paramsSet = true;
                } else {
                    this.param = {};
                    this.paramsSet = false;
                }
            },
            paramsChanged() {
                this.paramsSet = true;
                if (!this.executorMode) {
                    this.updateModel();
                }
            },
            updateAction() {
                if (this.value?.id) {
                    if (this.value.id != this.action?.id) {
                        const action = this.actionsToShow.find((action) => action.id === this.value.id);
                        if (action) {
                            this.action = action;
                            this.param = this.value.param || {};
                            this.paramsSet = true;
                        } else {
                            this.action = {};
                            this.param = {};
                            this.updateModel();
                        }
                    }
                } else if (this.isFullySpecified) {
                    this.action = {};
                    this.param = {};
                    this.selectFirstActionIfOnlyOne();
                }
            },
            selectFirstActionIfOnlyOne() {
                if (!this.executorMode && this.actionsToShow.length === 1 && (!this.value || !this.value.id)) {
                    this.action = this.actionsToShow[0];
                    this.param = {};
                    this.updateModel();
                }
            },
            shouldShowAction(possibleAction) {
                return this.possibleActionFilter ? this.possibleActionFilter(possibleAction) : true;
            },
            updateModel() {
                if (this.disabled || (this.executorMode && !this.action.id)) {
                    return;
                }
                const param = ChannelFunctionAction.requiresParams(this.action.id) ? {...this.param} : null;
                this.$emit('input', this.isFullySpecified ? {id: this.action.id, param} : undefined);
            },
            isSelected(actionId) {
                if (this.executorMode) {
                    return this.executed.includes(actionId);
                } else {
                    return this.action.id === actionId && (this.paramsSet || !ChannelFunctionAction.requiresParams(actionId));
                }
            },
        },
        computed: {
            actionsToShow() {
                return ((this.subject || {}).possibleActions || []).filter((action) => this.shouldShowAction(action));
            },
            isFullySpecified() {
                if (!this.action?.id) {
                    return false;
                }
                if (ChannelFunctionAction.requiresParams(this.action.id)) {
                    return this.paramsSet;
                } else {
                    return true;
                }
            },
        },
        watch: {
            subject(newSubject, oldSubject) {
                if (newSubject?.functionId !== oldSubject?.functionId) {
                    this.changeAction({});
                    Vue.nextTick(() => this.selectFirstActionIfOnlyOne());
                }
            },
            value() {
                if (this.value && this.action?.id && this.value.id !== this.action.id) {
                    this.value.param = {};
                }
                this.updateAction();
            },

        },
    };
</script>

<style lang="scss">
    .channel-action-chooser {
        .panel-heading {
            display: flex;
            a {
                flex: 1;
                &:focus {
                    font-weight: bold;
                }
            }
        }
        .btn-execute {
            display: flex;
            text-align: left;
            > span:first-child {
                flex: 1;
            }
        }
        .action-without-params {
            .panel-heading {
                border-radius: 3px;
            }
        }
        .channel-action-immediate-indicator {
            visibility: hidden;
        }
    }
</style>
