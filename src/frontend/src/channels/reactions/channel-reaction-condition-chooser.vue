<template>
    <div>
        <div class="panel-group panel-accordion m-0">
            <div v-for="possibleCondition in possibleConditions" :key="possibleCondition.caption">
                <transition-expand>
                    <div v-if="!currentCondition || isSelected(possibleCondition)">
                        <div v-if="possibleConditions.length > 1"
                            :class="['panel panel-default', {'panel-success': isSelected(possibleCondition), 'action-without-params': true}]">
                            <div class="panel-heading d-flex"
                                @click="changeCondition(possibleCondition)">
                                <a role="button"
                                    tabindex="0" @keydown.enter.stop="changeCondition(possibleCondition)"
                                    class="text-inherit flex-grow-1">
                                    {{ $t(possibleCondition.caption) }}
                                </a>
                                <div>
                                    <span v-if="isSelected(possibleCondition)" class="glyphicon glyphicon-ok"></span>
                                </div>
                            </div>
                        </div>
                        <div v-if="possibleCondition.component">
                            <transition-expand>
                                <div class="" v-if="isSelected(possibleCondition)">
                                    <Component :is="possibleCondition.component"
                                        v-model="currentConditionJson" v-bind="possibleCondition.props || {}"/>
                                </div>
                            </transition-expand>
                        </div>
                    </div>
                </transition-expand>
            </div>
        </div>
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {ChannelReactionConditions} from "@/channels/reactions/channel-reaction-conditions";
    import {isEqual} from "lodash";

    export default {
        components: {TransitionExpand},
        props: {
            subject: Object,
            value: Object,
        },
        data() {
            return {
                currentCondition: undefined,
            };
        },
        mounted() {
            if (!this.currentCondition && this.value) {
                this.currentCondition = this.possibleConditions.find(c => c.test ? c.test(this.value) : isEqual(c.def(), this.value));
            }
            if (!this.currentCondition && this.possibleConditions.length === 1) {
                this.changeCondition(this.possibleConditions[0]);
            }
        },
        methods: {
            isSelected(condition) {
                return condition && (condition.caption === this.currentCondition?.caption);
            },
            changeCondition(condition) {
                if (this.possibleConditions.length > 1 || !this.currentCondition) {
                    this.currentCondition = this.isSelected(condition) ? undefined : condition;
                    this.currentConditionJson = this.currentCondition?.def ? this.currentCondition.def() : undefined;
                }
            }
        },
        computed: {
            currentConditionJson: {
                get() {
                    return this.value;
                },
                set(def) {
                    this.$emit('input', def);
                }
            },
            possibleConditions() {
                return ChannelReactionConditions[this.subject.functionId] || [];
            }
        }
    }
</script>
