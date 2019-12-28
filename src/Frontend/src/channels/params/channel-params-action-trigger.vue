<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Action when pressed (ON)' )}}</label>
            <subject-dropdown v-model="subjectWithType"
                channels-dropdown-params="io=output&hasFunction=1"></subject-dropdown>
            <div v-if="subjectWithType">
                <channel-action-chooser :subject="subjectWithType.subject"
                    v-model="action"></channel-action-chooser>
            </div>
        </div>
        <div class="form-group">
            <label>{{ $t('Action when released (OFF)' )}}</label>
            <subject-dropdown v-model="subject2WithType"
                channels-dropdown-params="io=output&hasFunction=1"></subject-dropdown>
            <div v-if="subject2WithType">
                <channel-action-chooser :subject="subject2WithType.subject"
                    v-model="action2"></channel-action-chooser>
            </div>
        </div>
    </div>
</template>

<script>
    import SubjectDropdown from "../../devices/subject-dropdown";
    import ChannelActionChooser from "../action/channel-action-chooser";

    export default {
        components: {ChannelActionChooser, SubjectDropdown},
        props: ['channel'],
        data() {
            return {
                subjectWithType: undefined,
                subject2WithType: undefined,
                action: undefined,
                action2: undefined,
            };
        }
    };
</script>
