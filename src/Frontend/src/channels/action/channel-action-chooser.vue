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
                            v-model="actionId">
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
                        v-if="possibleAction.id == 50 && actionId == possibleAction.id">
                        <rolette-shutter-partial-percentage v-model="actionParam"></rolette-shutter-partial-percentage>
                    </div>
                </transition-expand>
                <transition-expand>
                    <div v-if="possibleAction.id == 80 && actionId == possibleAction.id">
                        <rgbw-parameters-setter v-model="actionParam"
                            class="well clearfix"
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
        mounted() {
            this.selectFirstActionIfOnlyOne();
            if (this.value && this.value.id) {
                this.actionId = this.value.id;
                this.actionParam = this.value.param;
            }
        },
        methods: {
            selectFirstActionIfOnlyOne() {
                if (this.actionsToShow.length === 1 && (!this.value || !this.value.id)) {
                    this.actionId = this.actionsToShow[0].id;
                    this.actionParam = {};
                }
            },
            shouldShowAction(possibleAction) {
                return this.possibleActionFilter ? this.possibleActionFilter(possibleAction) : true;
            }
        },
        computed: {
            actionsToShow() {
                return this.subject.function.possibleActions.filter((action) => this.shouldShowAction(action));
            },
            actionId: {
                get() {
                    return this.value && this.value.id;
                },
                set(id) {
                    this.$emit('input', {id, param: {}});
                }
            },
            actionParam: {
                get() {
                    return this.value && this.value.param || {};
                },
                set(param) {
                    this.$emit('input', {id: this.actionId, param});
                }
            }
        },
        watch: {
            subject() {
                this.actionId = undefined;
                Vue.nextTick(() => this.selectFirstActionIfOnlyOne());
            }
        }
    };
</script>
