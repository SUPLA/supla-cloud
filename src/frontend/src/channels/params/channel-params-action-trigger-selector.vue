<template>
    <div class="channel-params-action-trigger-selector">
        <subject-dropdown v-model="subject"
            @input="onSubjectChange()"
            channels-dropdown-params="io=output&hasFunction=1"></subject-dropdown>
        <div v-if="subject">
            <channel-action-chooser :subject="subject"
                @input="onActionChange()"
                :possible-action-filter="possibleActionFilter"
                v-model="action"></channel-action-chooser>
        </div>
        <button v-if="value"
            type="button"
            class="btn btn-default btn-block"
            @click="$emit('input')">
            <i class="pe-7s-close"></i>
            {{ $t('Clear') }}
        </button>
    </div>
</template>

<script>
    import SubjectDropdown from "../../devices/subject-dropdown";
    import ChannelActionChooser from "../action/channel-action-chooser";
    import changeCase from "change-case";

    export default {
        components: {ChannelActionChooser, SubjectDropdown},
        props: ['value'],
        data() {
            return {
                subject: undefined,
                action: undefined,
            };
        },
        mounted() {
            this.onValueChanged();
        },
        watch: {
            value() {
                this.onValueChanged();
            }
        },
        methods: {
            onValueChanged() {
                if (this.value && this.value.subjectType) {
                    if (!this.subject || this.value.subjectId !== this.subject.id) {
                        const endpoint = `${changeCase.paramCase(this.value.subjectType)}s/${this.value.subjectId}`;
                        this.$http.get(endpoint)
                            .then(response => this.subject = response.body)
                            .then(() => this.action = this.value.action);
                    }
                } else {
                    this.subject = undefined;
                    this.action = undefined;
                }
            },
            onSubjectChange() {
                this.action = undefined;
            },
            onActionChange() {
                this.$emit('input', {
                    subjectId: this.subject.id,
                    subjectType: this.subject.subjectType,
                    action: this.action,
                });
            },
            possibleActionFilter(possibleAction) {
                if (['CONTROLLINGTHEGATE', 'CONTROLLINGTHEGARAGEDOOR'].includes(this.subject.function.name)) {
                    return !(['OPEN', 'CLOSE'].includes(possibleAction.name));
                }
                return true;
            },
        }
    };
</script>

<style lang="scss">
    .channel-params-action-trigger-selector {
        .possible-action-params {
            .well > div {
                clear: both;
                width: 100%;
            }
        }
    }
</style>
