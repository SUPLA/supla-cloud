<template>
    <div>
        <div v-for="possibleAction in actionsToShow">
            <slot :possibleAction="possibleAction">
                <div class="radio"
                    v-if="actionsToShow.length > 1">
                    <label>
                        <input type="radio"
                            :value="possibleAction.id"
                            v-model="action.id">
                        {{ $t(possibleAction.caption) }}
                    </label>
                </div>
                <div v-else>
                    {{ $t(possibleAction.caption) }}
                </div>
            </slot>
            <div class="well clearfix"
                v-if="possibleAction.id == 50 && action.id == possibleAction.id">
                <rolette-shutter-partial-percentage v-model="actionParam"></rolette-shutter-partial-percentage>
            </div>
            <transition-expand>
                <div v-if="possibleAction.id == 80 && action.id == possibleAction.id">
                    <rgbw-parameters-setter v-model="action.param"
                        class="well clearfix"
                        :channel-function="subject.function"></rgbw-parameters-setter>
                </div>
            </transition-expand>
        </div>
    </div>
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";
    import RoletteShutterPartialPercentage from "./rolette-shutter-partial-percentage";
    import RgbwParametersSetter from "./rgbw-parameters-setter";

    export default {
        components: {RgbwParametersSetter, RoletteShutterPartialPercentage, TransitionExpand},
        props: ['subject', 'value', 'possibleActionFilter'],
        data() {
            return {
                action: {},
            };
        },
        mounted() {
            if (this.actionsToShow.length === 1) {
                this.action = {
                    id: this.actionsToShow[0].id,
                    param: {}
                };
                this.updateAction();
            }
        },
        methods: {
            updateAction() {
                this.$emit('input', this.action);
            },
            shouldShowAction(possibleAction) {
                return this.possibleActionFilter ? this.possibleActionFilter(possibleAction) : true;
            }
        },
        computed: {
            actionsToShow() {
                return this.subject.function.possibleActions.filter((action) => this.shouldShowAction(action));
            }
        }
    };
</script>
