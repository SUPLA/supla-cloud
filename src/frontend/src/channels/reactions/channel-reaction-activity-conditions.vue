<template>
    <div>
        <h4 class="text-center">
            {{ $t('Additional criteria') }}
            <a @click="showHelp = !showHelp">
                <fa icon="question-circle" class="ml-2 small"/>
            </a>
        </h4>
        <transition-expand>
            <div v-if="showHelp" class="alert alert-info">
                {{ $t('The reaction will be active when all of the conditions will be meet. If you choose to set all of the available time settings, the reaction will be active when the time is between active from and active to, is within the selected working schedule and meets one of the additional criteria.') }}
            </div>
        </transition-expand>
        <div>
            <div v-for="(c, $index) in conditions" :key="$index">
                <div class="d-flex">
                    <ChannelReactionActivityCondition v-model="c.condition" @input="updateModel()"
                        :display-validation-errors="displayValidationErrors" class="flex-grow-1"/>
                    <a class="text-default">
                        <fa icon="trash" class="text-muted"/>
                    </a>
                </div>
                <div class="or-hr mb-3" v-if="$index < conditions.length - 1">{{ $t('OR') }}</div>
            </div>
        </div>
        <a class="btn btn-white" @click="addCondition()">
            <fa icon="plus"/>
            {{ $t('Add') }}
        </a>
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import {deepCopy} from "@/common/utils";
    import ChannelReactionActivityCondition from "@/channels/reactions/channel-reaction-activity-condition.vue";

    export default {
        components: {
            ChannelReactionActivityCondition,
            TransitionExpand
        },
        props: {
            value: Array,
            displayValidationErrors: Boolean,
        },
        data() {
            return {
                showHelp: false,
                conditions: [],
            };
        },
        mounted() {
            this.conditions = (deepCopy(this.value || [])).map(condition => ({condition}));
        },
        methods: {
            addCondition() {
                this.conditions.push({condition: []});
            },
            updateModel() {
                this.$emit('input', this.modelValue);
            },
        },
        computed: {
            modelValue() {
                return this.conditions.map(({condition}) => condition).filter(c => c.length > 0);
            }
        },
    }
</script>

<style lang="scss">
    @import "../../styles/variables";

    .or-hr {
        display: flex;
        align-items: center;
        text-align: center;
        color: $supla-grey-dark;

        &::before,
        &::after {
            content: '';
            flex: 1;
            border-bottom: 1px solid $supla-grey-dark;
        }

        &:not(:empty)::before {
            margin-right: 1em;
        }

        &:not(:empty)::after {
            margin-left: 1em;
        }
    }
</style>
