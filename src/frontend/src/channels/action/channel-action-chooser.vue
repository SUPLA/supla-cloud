<template>
    <div :class="['channel-action-chooser', `channel-action-chooser-action-${action && action.id}`]">
        <div class="w-100">
            <div class="alert alert-info" v-if="isSlaveThermostat">
                {{ $t('This thermostat is controlled by another device.') }}
                <i18n-t keypath="Use {theMasterThermostat} to control this device." tag="span">
                    <template #theMasterThermostat>
                        <router-link
                            :to="{name: 'channel.thermostatPrograms', params: {id: subject.config.masterThermostatChannelId}}">
                            {{ $t('the master thermostat ID{id}', {id: subject.config.masterThermostatChannelId}) }}
                        </router-link>
                    </template>
                </i18n-t>
            </div>
            <div :class="['panel-group panel-accordion m-0', {'panel-accordion-disabled': isDisabled}]">
                <div
                    :class="[{'panel panel-default': possibleAction.name !== 'SEND', 'panel-success': isSelected(possibleAction.id), 'action-without-params': !ChannelFunctionAction.requiresParams(possibleAction.id)}]"
                    v-for="possibleAction in actionsToShow"
                    :key="possibleAction.id">
                    <div class="panel-heading"
                        v-show="possibleAction.name !== 'SEND'"
                        @click="changeAction(possibleAction)">
                        <a role="button"
                            tabindex="0"
                            @keydown.enter.stop="changeAction(possibleAction)"
                            class="text-inherit">
                            {{ actionCaption(possibleAction, subject) }}
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
                        <div :class="{'panel-body': possibleAction.name !== 'SEND'}"
                            v-if="ChannelFunctionAction.requiresParams(possibleAction.id) && action && action.id === possibleAction.id">
                            <div
                                v-if="[ChannelFunctionAction.REVEAL_PARTIALLY, ChannelFunctionAction.SHUT_PARTIALLY, ChannelFunctionAction.OPEN_PARTIALLY, ChannelFunctionAction.CLOSE_PARTIALLY].includes(action.id)">
                                <PartialPercentageParamSetter v-model="param" :subject="subject" @input="paramsChanged()"/>
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
                                    :params="`function=${subject.function.id}&skipIds=${(subject.ownSubjectType === 'channel' && subject.id) || ''}`"></channels-id-dropdown>
                            </div>
                            <div v-if="action.id === ChannelFunctionAction.TURN_OFF_WITH_DURATION">
                                <DurationParamSetter v-model="param.durationMs" :with-calendar="executorMode" hide-no-timer disable-ms
                                    @input="paramsChanged()"/>
                            </div>
                            <div
                                v-if="action.id === ChannelFunctionAction.HVAC_SET_TEMPERATURES || action.id === ChannelFunctionAction.HVAC_SET_TEMPERATURE">
                                <HvacSetpointsSetter v-model="temperatureSetpoints" :subject="subject" @input="paramsChanged()"/>
                            </div>
                            <div v-if="action.id === ChannelFunctionAction.SEND">
                                <NotificationForm v-model="param" @input="paramsChanged()" display-validation-errors
                                    :subject="contextSubject"/>
                            </div>
                            <div v-if="executorMode" class="mt-3">
                                <button
                                    :class="['btn btn-block btn-execute', {'btn-grey': !isSelected(possibleAction.id), 'btn-green': isSelected(possibleAction.id)}]"
                                    type="button"
                                    :disabled="!isFullySpecified || executing.includes(action.id)"
                                    @click="updateModel()">
                                    <span class="text-inherit">
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
    import RgbwParametersSetter from "./rgbw-parameters-setter";
    import Vue from "vue";
    import DigiglassParametersSetter from "./digiglass-parameters-setter";
    import ChannelsIdDropdown from "../../devices/channels-id-dropdown";
    import ChannelFunction from "../../common/enums/channel-function";
    import ChannelFunctionAction from "../../common/enums/channel-function-action";
    import NotificationForm from "@/notifications/notification-form.vue";
    import {isEqual} from "lodash";
    import DurationParamSetter from "@/channels/action/duration-param-setter.vue";
    import HvacSetpointsSetter from "@/channels/action/hvac-setpoints-setter.vue";
    import PartialPercentageParamSetter from "@/channels/action/partial-percentage-param-setter.vue";
    import {actionCaption} from "@/channels/channel-helpers";

    export default {
        components: {
            PartialPercentageParamSetter,
            HvacSetpointsSetter,
            DurationParamSetter,
            NotificationForm,
            ChannelsIdDropdown,
            DigiglassParametersSetter,
            RgbwParametersSetter,
            TransitionExpand
        },
        props: {
            subject: {type: Object},
            contextSubject: {type: Object},
            value: {type: Object},
            disabled: {type: Boolean, default: false},
            possibleActionFilter: {type: Function, required: false, default: () => true},
            executorMode: {type: Boolean, default: false},
            alwaysSelectFirstAction: {type: Boolean, default: false},
            executing: {type: Array, default: () => []},
            executed: {type: Array, default: () => []},
        },
        data() {
            return {
                action: {},
                param: {},
                paramHistory: {},
                ChannelFunction,
                ChannelFunctionAction,
            };
        },
        mounted() {
            this.updateAction();
            this.selectFirstActionIfOnlyOne();
        },
        methods: {
            actionCaption,
            changeAction(action) {
                if (this.isDisabled) {
                    return;
                }
                if (action.id === this.action?.id && this.executorMode && ChannelFunctionAction.requiresParams(action.id)) {
                    return this.changeAction({}); // collapse params panel
                }
                if (this.action && this.isFullySpecified) {
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
                } else {
                    this.param = {};
                }
            },
            paramsChanged() {
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
                        } else {
                            this.action = {};
                            this.param = {};
                            this.updateModel();
                        }
                    } else {
                        this.param = this.value.param || {};
                    }
                    if (!this.isFullySpecified) {
                        this.param = {};
                    }
                } else {
                    this.action = {};
                    this.param = {};
                    this.selectFirstActionIfOnlyOne();
                }
            },
            selectFirstActionIfOnlyOne() {
                if (!this.executorMode && (this.actionsToShow.length === 1 || this.alwaysSelectFirstAction) && (!this.action || !this.action.id)) {
                    this.action = this.actionsToShow[0];
                    this.param = {};
                    this.updateModel();
                }
            },
            shouldShowAction(possibleAction) {
                return this.possibleActionFilter ? this.possibleActionFilter(possibleAction) : true;
            },
            updateModel() {
                if (this.isDisabled || (this.executorMode && !this.action.id)) {
                    return;
                }
                this.$emit('input', this.modelValue);
            },
            isSelected(actionId) {
                if (this.executorMode) {
                    return this.executed.includes(actionId);
                } else {
                    return this.action.id === actionId && this.isFullySpecified;
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
                    return ChannelFunctionAction.paramsValid(this.action.id, this.param);
                } else {
                    return true;
                }
            },
            modelValue() {
                const param = ChannelFunctionAction.requiresParams(this.action?.id) ? {...this.param} : null;
                return this.isFullySpecified ? {id: this.action.id, param} : undefined;
            },
            temperatureSetpoints: {
                get() {
                    if (this.action.id === ChannelFunctionAction.HVAC_SET_TEMPERATURE) {
                        return {heat: this.param.temperature, cool: this.param.temperature};
                    } else {
                        return {heat: this.param.temperatureHeat, cool: this.param.temperatureCool};
                    }
                },
                set(setpoints) {
                    if (this.action.id === ChannelFunctionAction.HVAC_SET_TEMPERATURE) {
                        this.$set(this.param, 'temperature', Number.isFinite(setpoints.heat) ? setpoints.heat : setpoints.cool);
                    } else {
                        this.$set(this.param, 'temperatureHeat', setpoints.heat);
                        this.$set(this.param, 'temperatureCool', setpoints.cool);
                    }
                }
            },
            isSlaveThermostat() {
                return !!this.subject?.config?.masterThermostatChannelId;
            },
            isDisabled() {
                return this.disabled || !!this.isSlaveThermostat;
            }
        },
        watch: {
            subject(newSubject, oldSubject) {
                if (newSubject?.functionId !== oldSubject?.functionId) {
                    this.changeAction({});
                    Vue.nextTick(() => this.selectFirstActionIfOnlyOne());
                } else {
                    this.updateAction();
                }
            },
            value() {
                if (this.value?.id !== this.modelValue?.id || !isEqual(this.value?.param, this.modelValue?.param)) {
                    this.updateAction();
                }
            },
        },
    };
</script>

<style lang="scss">
    @import "../../styles/variables";

    .channel-action-chooser {
        .panel-heading {
            display: flex;
            a {
                flex: 1;
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
