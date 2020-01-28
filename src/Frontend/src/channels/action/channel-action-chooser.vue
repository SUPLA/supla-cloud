<template>
    <div class="possible-actions">
        <div v-for="possibleAction in actionsToShow"
            class="possible-action">
            <slot :possibleAction="possibleAction">
                <div class="radio"
                    v-if="actionsToShow.length > 1">
                    <label>
                        <input type="radio"
                            :value="possibleAction.id"
                            @change="updateModel()"
                            v-model="action.id">
                        {{ $t(possibleAction.caption) }}
                    </label>
                </div>
                <div v-else>
                    {{ $t(possibleAction.caption) }}
                </div>
            </slot>
            <div class="possible-action-params">
                <transition-expand>
                    <div class="well clearfix"
                        v-if="possibleAction.id == 50 && action.id == possibleAction.id">
                        <rolette-shutter-partial-percentage v-model="action.param"
                            @change="updateModel"></rolette-shutter-partial-percentage>
                    </div>
                </transition-expand>
                <transition-expand>
                    <div v-if="possibleAction.id == 80 && action.id == possibleAction.id">
                        <rgbw-parameters-setter v-model="action.param"
                            class="well clearfix"
                            @change="updateModel()"
                            :channel-function="subject.function"></rgbw-parameters-setter>
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

    export default {
        components: {RgbwParametersSetter, RoletteShutterPartialPercentage, TransitionExpand},
        props: ['subject', 'value', 'possibleActionFilter'],
        data() {
            return {action: {}};
        },
        mounted() {
            if (this.value && this.value.id) {
                this.action = {id: this.value.id, param: this.value.param};
            } else {
                this.selectFirstActionIfOnlyOne();
            }
        },
        methods: {
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
            }
        },
        computed: {
            actionsToShow() {
                return this.subject.function.possibleActions.filter((action) => this.shouldShowAction(action));
            },
        },
        watch: {
            subject() {
                this.action = {};
                this.updateModel();
                Vue.nextTick(() => this.selectFirstActionIfOnlyOne());
            }
        }
    };
</script>
