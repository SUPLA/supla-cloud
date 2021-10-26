<template>
    <div>
        <div class="form-group mt-3">
            <label>
                {{ $t("Read the following channel's state") }}
            </label>
            <channels-dropdown v-model="sourceChannel"
                @input="updateModel()"
                params="io=output&hasFunction=1"></channels-dropdown>
        </div>
        <div class="form-group mt-3"
            v-if="sourceChannel">
            <label>
                {{ $t("And set the same state for") }}
            </label>
            <subject-dropdown v-model="target"
                @input="updateModel()"
                :filter="filterOutSourceChannel"
                :channels-dropdown-params="`function=${sourceChannel.functionId}`"></subject-dropdown>
        </div>
    </div>
</template>

<script>
    import SubjectDropdown from "@/devices/subject-dropdown";
    import ChannelsDropdown from "@/devices/channels-dropdown";
    import {subjectEndpointUrl} from "@/common/utils";
    import ActionableSubjectType from "@/common/enums/actionable-subject-type";

    export default {
        props: ['subject', 'value', 'possibleActionFilter'],
        components: {ChannelsDropdown, SubjectDropdown},
        data() {
            return {
                sourceChannel: undefined,
                target: undefined,
            };
        },
        mounted() {
            this.setActionFromModel();
        },
        methods: {
            setActionFromModel() {
                if (this.value && this.value.sourceChannelId) {
                    if (this.sourceChannel?.id !== this.value.sourceChannelId) {
                        this.$http.get(`channels/${this.value.sourceChannelId}`).then(({body: channel}) => this.sourceChannel = channel);
                    }
                    if (this.target?.id !== this.value.subjectId || this.target?.subjectType !== this.value.subjectType) {
                        this.$http.get(subjectEndpointUrl(this.value)).then(({body: subject}) => this.target = subject);
                    }
                } else {
                    this.sourceChannel = undefined;
                    this.target = undefined;
                }
            },
            updateModel() {
                if (this.sourceChannel && this.target) {
                    this.$emit('input', {
                        action: 'copyChannelState',
                        sourceChannelId: this.sourceChannel.id,
                        subjectType: this.target.subjectType,
                        subjectId: this.target.id,
                    });
                } else {
                    this.$emit('input', {action: 'copyChannelState'});
                }
            },
            filterOutSourceChannel({subjectType, id}) {
                return subjectType !== ActionableSubjectType.CHANNEL || id != this.sourceChannel.id;
            }
        },
    };
</script>

<style lang="scss">

</style>
