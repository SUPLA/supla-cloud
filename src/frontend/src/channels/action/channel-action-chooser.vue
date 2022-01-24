<template>
    <div class="possible-actions">
        <div class="form-group"
            v-if="actionsToShow.length > 3">
            <select class="selectpicker possible-actions-picker"
                ref="dropdown"
                data-live-search="true"
                :data-live-search-placeholder="$t('Search')"
                data-width="100%"
                :data-none-selected-text="$t('choose the action')"
                :data-none-results-text="$t('No results match {0}')"
                data-style="btn-default btn-wrapped"
                v-model="action"
                @change="actionChanged()">
                <option v-for="possibleAction in actionsToShow"
                    :key="possibleAction.id"
                    :value="possibleAction">
                    {{ $t(possibleAction.caption) }}
                </option>
            </select>
        </div>
        <div class="form-group"
            v-else-if="actionsToShow.length >= 1">
            <div v-for="possibleAction in actionsToShow"
                :key="possibleAction.id"
                class="possible-action">
                <slot :possibleAction="possibleAction">
                    <div class="radio"
                        v-if="actionsToShow.length > 1">
                        <label>
                            <input type="radio"
                                :value="possibleAction"
                                @change="actionChanged()"
                                v-model="action">
                            {{ $t(possibleAction.caption) }}
                        </label>
                    </div>
                    <p v-else
                        class="text-center form-group">
                        {{ $t(action.caption) }}
                    </p>
                </slot>
            </div>
        </div>

        <div class="possible-action-params mb-3"
            v-if="action.id">
            <transition-expand>
                <div class="well clearfix"
                    v-if="['REVEAL_PARTIALLY', 'SHUT_PARTIALLY', 'OPEN_PARTIALLY'].includes(action.name)">
                    <rolette-shutter-partial-percentage v-model="param"
                        @input="updateModel()"></rolette-shutter-partial-percentage>
                </div>
            </transition-expand>
            <transition-expand>
                <div v-if="action.name === 'SET_RGBW_PARAMETERS'">
                    <rgbw-parameters-setter v-model="param"
                        class="well clearfix"
                        @input="updateModel()"
                        :channel-function="subject.function"></rgbw-parameters-setter>
                </div>
            </transition-expand>
            <transition-expand>
                <div v-if="action.name === 'SET'">
                    <digiglass-parameters-setter v-if="subject.function.name.match(/^DIGIGLASS.+/)"
                        v-model="param"
                        @input="updateModel()"
                        :subject="subject"></digiglass-parameters-setter>
                </div>
            </transition-expand>
            <transition-expand>
                <div v-if="action.name === 'COPY'">
                    <channels-id-dropdown v-model="param.sourceChannelId"
                        @input="updateModel()"
                        :params="`function=${subject.function.id}&skipIds=${(subject.subjectType === 'channel' && subject.id) || ''}`"></channels-id-dropdown>
                </div>
            </transition-expand>
        </div>
    </div>
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";
    import RoletteShutterPartialPercentage from "./rolette-shutter-partial-percentage";
    import RgbwParametersSetter from "./rgbw-parameters-setter";
    import Vue from "vue";
    import DigiglassParametersSetter from "./digiglass-parameters-setter";
    import $ from "jquery";
    import ChannelsIdDropdown from "../../devices/channels-id-dropdown";

    export default {
        components: {
            ChannelsIdDropdown,
            DigiglassParametersSetter,
            RgbwParametersSetter,
            RoletteShutterPartialPercentage,
            TransitionExpand
        },
        props: ['subject', 'value', 'possibleActionFilter'],
        data() {
            return {
                action: {},
                param: {},
            };
        },
        mounted() {
            this.updateAction();
            this.selectFirstActionIfOnlyOne();
            Vue.nextTick(() => $(this.$refs.dropdown).selectpicker());
        },
        methods: {
            updateDropdownOptions() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker('refresh'));
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
                        this.updateDropdownOptions();
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
            actionChanged() {
                this.param = {};
                this.updateModel();
            },
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
                this.updateDropdownOptions();
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
