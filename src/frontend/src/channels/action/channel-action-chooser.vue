<template>
    <div class="possible-actions">
        <div class="w-100 mb-3">
            <div class="panel-group panel-accordion">
                <div :class="['panel panel-default', {'panel-success': executed.includes(possibleAction.id)}]"
                    v-for="possibleAction in actionsToShow"
                    :key="possibleAction.id">
                    <div class="panel-heading"
                        @click="changeAction(possibleAction)">
                        <div class="left-right-header">
                            <a role="button"
                                class="text-inherit">
                                {{ $t(possibleAction.caption) }}
                            </a>
                            <div>
                                <button-loading-dots v-if="executing.includes(possibleAction.id)"></button-loading-dots>
                                <span v-else-if="executed.includes(possibleAction.id)"
                                    class="glyphicon glyphicon-ok">
                                </span>
                                <span v-else-if="ChannelFunctionAction.requiresParams(possibleAction.id)"
                                    class="glyphicon glyphicon-cog"></span>
                                <span v-else
                                    class="glyphicon glyphicon-play"></span>
                            </div>
                        </div>
                    </div>
                    <transition-expand>
                        <div class="panel-body"
                            v-if="ChannelFunctionAction.requiresParams(possibleAction.id) && action && action.id === possibleAction.id">
                            <div class="well clearfix"
                                v-if="[ChannelFunctionAction.REVEAL_PARTIALLY, ChannelFunctionAction.SHUT_PARTIALLY, ChannelFunctionAction.OPEN_PARTIALLY, ChannelFunctionAction.CLOSE_PARTIALLY].includes(action.id)">
                                <rolette-shutter-partial-percentage v-model="param"
                                    @input="paramsChanged()"></rolette-shutter-partial-percentage>
                            </div>
                            <div v-if="action.name === 'SET_RGBW_PARAMETERS'">
                                <rgbw-parameters-setter v-model="param"
                                    class="well clearfix"
                                    @input="paramsChanged()"
                                    :channel-function="subject.function"></rgbw-parameters-setter>
                            </div>
                            <div v-if="action.name === 'SET'">
                                <digiglass-parameters-setter v-if="subject.function.name.match(/^DIGIGLASS.+/)"
                                    v-model="param"
                                    @input="paramsChanged()"
                                    :subject="subject"></digiglass-parameters-setter>
                            </div>
                            <div v-if="action.name === 'COPY'">
                                <channels-id-dropdown v-model="param.sourceChannelId"
                                    class="mb-3"
                                    @input="paramsChanged()"
                                    :params="`function=${subject.function.id}&skipIds=${(subject.subjectType === 'channel' && subject.id) || ''}`"></channels-id-dropdown>
                            </div>
                            <div v-if="confirmActionsWithParameters">
                                <button class="btn btn-default"
                                    type="button"
                                    :disabled="executing.includes(action.id)"
                                    @click="updateModel()">
                                    <span v-if="!executing.includes(action.id)">
                                        <i v-if="executed.includes(action.id)"
                                            class="pe-7s-check"></i>
                                        {{ executed.includes(action.id) ? $t('executed') : $t('Execute') }}
                                    </span>
                                    <button-loading-dots v-else></button-loading-dots>
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
    import LoaderDots from "../../common/gui/loaders/loader-dots";

    export default {
        components: {
            LoaderDots,
            ChannelsIdDropdown,
            DigiglassParametersSetter,
            RgbwParametersSetter,
            RoletteShutterPartialPercentage,
            TransitionExpand
        },
        props: {
            subject: {type: Object},
            value: {type: Object},
            possibleActionFilter: {type: Function, required: false, default: () => true},
            confirmActionsWithParameters: {type: Boolean, default: false},
            executing: {type: Array, default: []},
            executed: {type: Array, default: []},
        },
        data() {
            return {
                action: {},
                param: {},
                paramHistory: {},
                ChannelFunctionAction,
            };
        },
        mounted() {
            this.updateAction();
            this.selectFirstActionIfOnlyOne();
        },
        methods: {
            changeAction(action) {
                if (this.action) {
                    this.paramHistory[this.action.id] = {...this.param};
                }
                this.action = action;
                this.param = this.paramHistory[this.action.id] ? {...this.paramHistory[this.action.id]} : {};
                if (!ChannelFunctionAction.requiresParams(this.action.id)) {
                    this.updateModel();
                }
            },
            paramsChanged() {
                if (!this.confirmActionsWithParameters) {
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
                        // this.updateDropdownOptions();
                    }
                } else {
                    this.action = {};
                    this.param = {};
                }
            },
            selectFirstActionIfOnlyOne() {
                if (this.actionsToShow.length === 1 && (!this.value || !this.value.id)) {
                    this.action = this.actionsToShow[0];
                    this.param = {};
                    this.updateModel();
                }
            },
            shouldShowAction(possibleAction) {
                return this.possibleActionFilter ? this.possibleActionFilter(possibleAction) : true;
            },
            updateModel() {
                this.$emit('input', {...this.action, param: {...this.param}});
            },
            // actionChanged() {
            //     this.param = {};
            //     this.updateModel();
            // },
        },
        computed: {
            actionsToShow() {
                return ((this.subject || {}).possibleActions || []).filter((action) => this.shouldShowAction(action));
            },
        },
        watch: {
            subject(newSubject, oldSubject) {
                if (newSubject?.functionId !== oldSubject?.functionId) {
                    this.action = {};
                }
                // this.updateDropdownOptions();
                this.updateModel();
                Vue.nextTick(() => this.selectFirstActionIfOnlyOne());
            },
            value() {
                if (this.value && this.action?.id && this.value.id !== this.action.id) {
                    this.value.param = {};
                }
                this.updateAction();
            }
        },
    };
</script>
