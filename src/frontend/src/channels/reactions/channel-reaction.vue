<template>
    <div>
        <h2 v-if="item.id">{{ $t('Edit reaction') }}</h2>
        <h2 v-else>{{ $t('New reaction') }}</h2>
        <div class="channel-reaction row">
            <div class="col-sm-6">
                <ChannelReactionConditionChooser :subject="owningChannel" v-model="trigger" @input="updateModel()"/>
            </div>
            <div class="col-sm-6">
                <SubjectDropdown v-model="targetSubject" class="mb-3" channels-dropdown-params="io=output&hasFunction=1"/>
                <div v-if="targetSubject">
                    <ChannelActionChooser :subject="targetSubject" :alwaysSelectFirstAction="true" v-model="action"
                        @input="updateModel()"/>
                </div>
            </div>
        </div>
    </div>
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
            item: Object,
        },
        data() {
            return {
                trigger: {},
                targetSubject: undefined,
                action: undefined,
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
            owningChannel() {
                return this.item?.owningChannel;
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
