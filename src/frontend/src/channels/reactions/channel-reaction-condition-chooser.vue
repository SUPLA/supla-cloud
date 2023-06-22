<template>
    <div>
        <div class="panel-group panel-accordion">
            <div v-for="possibleCondition in possibleConditions" :key="possibleCondition.id">
                <transition-expand>
                    <div v-if="!currentCondition || isSelected(possibleCondition)"
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
                        <transition-expand>
                            <div class="panel-body" v-if="isSelected(possibleCondition)">
                                asdf
                            </div>
                        </transition-expand>
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
            return {};
        },
        methods: {
            isSelected(condition) {
                return condition.id === this.currentCondition?.id;
            },
            changeCondition(condition) {
                this.currentCondition = this.isSelected(condition) ? undefined : condition;
            }
        },
        computed: {
            currentCondition: {
                get() {
                    if (this.value) {
                        return this.possibleConditions.find(c => isEqual(c.def(), this.value));
                    } else {
                        return undefined;
                    }
                },
                set(condition) {
                    this.$emit('input', condition?.def());
                }
            },
            possibleConditions() {
                return ChannelReactionConditions[this.subject.functionId] || [];
            }
        }
    }
</script>
