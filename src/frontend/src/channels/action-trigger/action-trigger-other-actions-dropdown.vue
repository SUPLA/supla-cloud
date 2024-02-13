<template>
    <div>
        <SelectForSubjects
            do-not-hide-selected
            class="other-actions-dropdown"
            :options="actionsForDropdown"
            :option-html="actionHtml"
            choose-prompt-i18n="choose the action"
            v-model="chosenAction"/>
    </div>
</template>

<script>
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";
    import ChannelFunctionAction from "../../common/enums/channel-function-action";
    import SelectForSubjects from "@/devices/select-for-subjects.vue";

    export default {
        props: ['value', 'filter'],
        components: {SelectForSubjects},
        data() {
            return {
                availableActions: [
                    {
                        id: ChannelFunctionAction.AT_DISABLE_LOCAL_FUNCTION,
                        caption: 'Disable local function', // i18n
                        description: 'Disables local device function and does nothing more.', // i18n
                        icon: 'power',
                        ownSubjectType: ActionableSubjectType.OTHER,
                    },
                    {
                        id: ChannelFunctionAction.AT_FORWARD_OUTSIDE,
                        caption: 'Publish to integrations', // i18n
                        description: 'Publishes the event to integrated services like MQTT or webhooks.', // i18n
                        icon: 'speaker',
                        ownSubjectType: ActionableSubjectType.OTHER,
                    },
                ],
            };
        },
        methods: {
            actionHtml(action, escape) {
                return `<div>
                            <div class="subject-dropdown-option d-flex">
                                <div class="flex-grow-1">
                                    <h5 class="my-1">
                                        <span class="line-clamp line-clamp-2">${escape(this.$t(action.fullCaption))}</span>
                                    </h5>
                                    <p class="option-extra">${this.$t(action.description)}</p>
                                </div>
                                <div class="icon option-extra"><span class="pe-7s-${action.icon}"></span></div>
                            </div>
                        </div>`;
            },
        },
        computed: {
            actionsForDropdown() {
                const filter = this.filter || (() => true);
                return this.availableActions.filter(filter);
            },
            chosenAction: {
                get() {
                    return this.value;
                },
                set(action) {
                    this.$emit('input', action);
                }
            }
        },
    };
</script>

<style lang="scss">
    .other-actions-dropdown {
        .subject-dropdown-option {
            .icon {
                font-size: 3em;
            }
        }
    }
</style>

