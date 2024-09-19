<template>
    <div>
        <div class="panel-group panel-accordion m-0">
            <div v-for="(possibleCondition, $index) in possibleConditions" :key="$index">
                <div :class="['panel panel-default', {'panel-success': isSelected(possibleCondition)}]">
                    <div class="panel-heading d-flex" v-if="possibleConditions.length > 1"
                        @click="changeCondition(possibleCondition)">
                        <a role="button"
                            tabindex="0" @keydown.enter.stop="changeCondition(possibleCondition)"
                            class="text-inherit flex-grow-1">
                            {{ $t(possibleCondition.caption(subject)) }}
                        </a>
                        <div>
                            <span v-if="isSelected(possibleCondition)" class="glyphicon glyphicon-ok"></span>
                        </div>
                    </div>
                    <div v-if="isSelected(possibleCondition) && possibleCondition.component">
                        <transition-expand>
                            <div class="panel-body" v-if="isSelected(possibleCondition)">
                                <Component :is="possibleCondition.component"
                                    :subject="subject"
                                    v-model="currentConditionJson"
                                    v-bind="possibleCondition.props || {}"/>
                            </div>
                        </transition-expand>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {ChannelFunctionTriggers, findTriggerDefinition} from "@/channels/reactions/channel-function-triggers";

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
        beforeMount() {
            if (!this.currentCondition && this.value) {
                this.currentCondition = findTriggerDefinition(this.subject.functionId, this.value);
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
                    this.currentCondition = condition;
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
                return (ChannelFunctionTriggers[this.subject.functionId] || [])
                    .filter((trigger) => !trigger.canBeSetForChannel || trigger.canBeSetForChannel(this.subject));
            }
        },
        watch: {
            value() {
                if (this.value) {
                    this.currentCondition = findTriggerDefinition(this.subject.functionId, this.value);
                }
            }
        }
    }
</script>
