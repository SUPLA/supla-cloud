<template>
    <modal-confirm @cancel="$emit('cancel')" @confirm="submitForm()" :header="$t('Configure behavior')" :loading="loading">
        <div class="channel-reaction">
            <div class="mb-3">
                <ChannelReactionConditionChooser :subject="subject" v-model="trigger" @input="updateModel()"
                    v-if="editingWhat === 'trigger'"/>
                <a class="step-link" @click="editingWhat = 'trigger'" v-else>{{ triggerCaption }}</a>
            </div>

            <div v-if="editingWhat === 'action'">

                <SubjectDropdown v-model="targetSubject" class="mb-3" channels-dropdown-params="io=output&hasFunction=1"
                    dropdown-container=".modal-wrapper"/>
                <div v-if="targetSubject">
                    <ChannelActionChooser :subject="targetSubject" :alwaysSelectFirstAction="true" v-model="action"
                        @input="updateModel()" dropdown-container=".modal-wrapper"/>
                </div>
            </div>
            <a class="step-link" @click="editingWhat = 'action'" v-else>
                <span v-if="action">
                    {{ $t(action.caption) }}
                    {{ subjectCaption }}
                </span>
                <span v-else>{{ $t('choose the action') }}</span>
            </a>
        </div>
    </modal-confirm>
</template>

<script>
    import ChannelReactionConditionChooser from "@/channels/reactions/channel-reaction-condition-chooser.vue";
    import SubjectDropdown from "@/devices/subject-dropdown.vue";
    import ChannelActionChooser from "@/channels/action/channel-action-chooser.vue";
    import {triggerHumanizer} from "@/channels/reactions/trigger-humanizer";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";

    export default {
        components: {ChannelActionChooser, SubjectDropdown, ChannelReactionConditionChooser},
        props: {
            subject: Object,
            value: Object,
        },
        data() {
            return {
                trigger: {},//{on_change_to: {gt: 33, name: 'temperature'}},
                targetSubject: undefined,
                action: undefined,
                editingWhat: 'trigger',
                loading: false,
            };
        },
        methods: {
            updateModel() {
                this.$emit('input', {
                    trigger: this.trigger,
                    subjectId: this.targetSubject?.id,
                    subjectType: this.targetSubject?.ownSubjectType,
                    actionId: this.action?.id,
                    actionParam: this.action?.param,
                    isValid: !!(this.trigger && this.action),
                });
            },
            submitForm() {
                // TODO validate
                this.loading = true;
                this.$emit('confirm', this.value);
            }
        },
        computed: {
            triggerCaption() {
                return triggerHumanizer(this.subject.functionId, this.trigger, this);
            },
            subjectCaption() {
                if (this.targetSubject.ownSubjectType === ActionableSubjectType.NOTIFICATION) {
                    return '';
                }
                return this.targetSubject.caption || `ID${this.targetSubject.id} ${this.$t(this.targetSubject.function.caption)}`;
            },
        }
    }
</script>

<style lang="scss" scoped>
    .step-link {
        font-size: 1.3em;
        text-align: center;
        display: block;
    }
</style>
