<template>
    <div class="channel-params-action-trigger-selector">
        <div :class="{'form-group': !subject}">
            <subject-dropdown v-model="subject"
                channels-dropdown-params="io=output&hasFunction=1"></subject-dropdown>
        </div>
        <div v-if="subject">
            <channel-action-chooser :subject="subject"
                @input="onActionChange()"
                v-model="action"></channel-action-chooser>
        </div>
        <button v-if="value"
            type="button"
            class="btn btn-default btn-block"
            @click="clearAction()">
            <i class="pe-7s-close"></i>
            {{ $t('Clear') }}
        </button>
    </div>
</template>

<script>
    import SubjectDropdown from "../../devices/subject-dropdown";
    import ChannelActionChooser from "../action/channel-action-chooser";
    import changeCase from "change-case";
    import EventBus from "@/common/event-bus";

    export default {
        components: {ChannelActionChooser, SubjectDropdown},
        props: ['value'],
        data() {
            return {
                subject: undefined,
                action: undefined,
                channelSavedListener: undefined,
            };
        },
        mounted() {
            this.channelSavedListener = () => this.onValueChanged();
            EventBus.$on('channel-updated', this.channelSavedListener);
            this.onValueChanged();
        },
        beforeDestroy() {
            EventBus.$off('channel-updated', this.channelSavedListener);
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
            onActionChange() {
                if (this.action?.id) {
                    this.$emit('input', {
                        subjectId: this.subject.id,
                        subjectType: this.subject.subjectType,
                        action: this.action,
                    });
                } else {
                    this.$emit('input');
                }
            },
            clearAction() {
                this.$emit('input');
                this.subject = undefined;
                this.action = undefined;
            }
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
