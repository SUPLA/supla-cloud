<template>
    <div class="possible-actions">
        <div v-for="possibleAction in actionsToShow"
            :key="possibleAction.id"
            class="possible-action">
            <slot :possibleAction="possibleAction">
                <div class="radio"
                    v-if="actionsToShow.length > 1">
                    <label>
                        <input type="radio"
                            :value="possibleAction.id"
                            @change="actionChanged()"
                            v-model="action.id">
                        {{ $t(possibleAction.caption) }}
                    </label>
                </div>
                <p v-else>
                    {{ $t(possibleAction.caption) }}
                </p>
            </slot>
            <div class="possible-action-params">
                <transition-expand>
                    <div class="well clearfix"
                        v-if="(possibleAction.id == 50 || possibleAction.id == 120) && action.id == possibleAction.id">
                        <rolette-shutter-partial-percentage v-model="action.param"
                            @input="updateModel()"></rolette-shutter-partial-percentage>
                    </div>
                </transition-expand>
                <transition-expand>
                    <div v-if="possibleAction.id == 80 && action.id == possibleAction.id">
                        <rgbw-parameters-setter v-model="action.param"
                            class="well clearfix"
                            @input="updateModel()"
                            :channel-function="subject.function"></rgbw-parameters-setter>
                    </div>
                </transition-expand>
                <transition-expand>
                    <div v-if="possibleAction.id == 2000 && action.id == possibleAction.id">
                        <digiglass-parameters-setter v-if="subject.function.name.match(/^DIGIGLASS.+/)"
                            v-model="action.param"
                            @input="updateModel()"
                            :subject="subject"></digiglass-parameters-setter>
                    </div>
                </transition-expand>
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

    export default {
        components: {DigiglassParametersSetter, RgbwParametersSetter, RoletteShutterPartialPercentage, TransitionExpand},
        props: ['subject', 'value', 'possibleActionFilter'],
        data() {
            return {action: {}};
        },
        mounted() {
            this.updateAction();
            this.selectFirstActionIfOnlyOne();
        },
        methods: {
            updateAction() {
                if (this.value && this.value.id) {
                    this.action = {id: this.value.id, param: this.value.param || {}};
                } else {
                    this.action = {};
                }
            },
            selectFirstActionIfOnlyOne() {
                if (this.actionsToShow.length === 1 && (!this.value || !this.value.id)) {
                    this.action = {id: this.actionsToShow[0].id, param: {}};
                    this.updateModel();
                }
            },
            shouldShowAction(possibleAction) {
                return this.possibleActionFilter ? this.possibleActionFilter(possibleAction) : true;
            },
            updateModel() {
                this.$emit('input', this.action);
            },
            actionChanged() {
                this.action.param = {};
                this.updateModel();
            },
        },
        computed: {
            actionsToShow() {
                return ((this.subject.function || {}).possibleActions || []).filter((action) => this.shouldShowAction(action));
            },
        },
        watch: {
            subject() {
                this.action = {};
                this.updateModel();
                Vue.nextTick(() => this.selectFirstActionIfOnlyOne());
            },
            value() {
                if (this.value && this.value.id !== this.action.id) {
                    this.value.param = {};
                }
                this.updateAction();
            }
        },
    };
</script>
