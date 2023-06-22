<template>
    <div class="channel-reaction">
        <div class="row">
            <div class="col-sm-6">
                <h4>{{ $t('Trigger') }}</h4>
                <ChannelReactionConditionChooser :subject="subject" v-model="trigger" @input="updateModel()"/>
            </div>
            <div class="col-sm-6">
                <h4>{{ $t('Action') }}</h4>
                <SubjectDropdown v-model="targetSubject" class="mb-3"/>
                <div v-if="targetSubject">
                    <ChannelActionChooser :subject="targetSubject" :alwaysSelectFirstAction="true" v-model="action" @input="updateModel()"/>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import ChannelReactionConditionChooser from "@/channels/reactions/channel-reaction-condition-chooser.vue";
    import SubjectDropdown from "@/devices/subject-dropdown.vue";
    import ChannelActionChooser from "@/channels/action/channel-action-chooser.vue";

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
            }
        }
    }
</script>

<style lang="scss">
</style>
