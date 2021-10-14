<template>
    <div>
        <select class="selectpicker other-actions-picker"
            ref="dropdown"
            data-live-search="true"
            :data-live-search-placeholder="$t('Search')"
            data-width="100%"
            :data-none-selected-text="$t('choose other action')"
            :data-none-results-text="$t('No results match {0}')"
            data-style="btn-default btn-wrapped"
            v-model="chosenAction"
            @change="$emit('input', chosenAction)">
            <option v-for="action in availableActions"
                :key="action.id"
                :value="action"
                :data-content="actionHtml(action)">
                {{ $t(action.label) }}
            </option>
        </select>
    </div>
</template>

<script>
    import Vue from "vue";
    import $ from "jquery";
    import "bootstrap-select";
    import "bootstrap-select/dist/css/bootstrap-select.css";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";

    export default {
        props: ['value'],
        components: {},
        data() {
            return {
                availableActions: [
                    {
                        id: 'disableLocalFunction',
                        label: 'Disable local function', // i18n
                        description: 'Disables local device function and does nothing more.', // i18n
                        icon: 'power',
                        subjectType: ActionableSubjectType.OTHER,
                    },
                    {
                        id: 'publishToIntegrations',
                        label: 'Publish to integrations', // i18n
                        description: 'Publishes the event to integrated services like MQTT or webhooks.', // i18n
                        icon: 'speaker',
                        subjectType: ActionableSubjectType.OTHER,
                    },
                ],
                chosenAction: undefined,
            };
        },
        mounted() {
            Vue.nextTick(() => $(this.$refs.dropdown).selectpicker());
            this.setActionFromModel();
        },
        methods: {
            actionHtml(action) {
                return `
                <div class='subject-dropdown-option flex-left-full-width'>
                    <div class="labels full">
                        <h4><span class="line-clamp line-clamp-2">${this.$t(action.label)}</span></h4>
                        <p class="line-clamp line-clamp-2">${this.$t(action.description)}</p>
                    </div>
                    <div class="icon"><span class="pe-7s-${action.icon}"></span></div>
                </div>
                `;
            },
            updateDropdownOptions() {
                Vue.nextTick(() => $(this.$refs.dropdown).selectpicker('refresh'));
            },
            setActionFromModel() {
                if (this.value) {
                    this.chosenAction = this.availableActions.filter(ch => ch.id == this.value.id)[0];
                } else {
                    this.chosenAction = undefined;
                }
            }
        },
        watch: {
            value() {
                this.setActionFromModel();
                this.updateDropdownOptions();
            },
        }
    };
</script>

<style lang="scss">
    .other-actions-picker {
        .subject-dropdown-option {
            .icon {
                font-size: 3em;
            }
        }
    }
</style>
