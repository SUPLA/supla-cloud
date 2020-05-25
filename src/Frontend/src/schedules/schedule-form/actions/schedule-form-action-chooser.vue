<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Subject') }}</label>
            <subject-dropdown v-model="subjectWithType"
                channels-dropdown-params="io=output&hasFunction=1"
                @input="subjectChanged()"
                :filter="filterOutNotSchedulableSubjects"></subject-dropdown>
        </div>
        <div v-if="subject">
            <channel-action-chooser :subject="subject"
                v-model="action"
                :possible-action-filter="possibleActionFilter"></channel-action-chooser>
        </div>
    </div>
</template>

<script>

    import ChannelsDropdown from "../../../devices/channels-dropdown";
    import SubjectDropdown from "../../../devices/subject-dropdown";
    import {mapState} from "vuex";
    import ChannelActionChooser from "../../../channels/action/channel-action-chooser";

    export default {
        components: {ChannelActionChooser, SubjectDropdown, ChannelsDropdown},
        data() {
            return {
                subjectWithType: {},
            };
        },
        mounted() {
            this.subjectWithType = {
                subject: this.subject,
                type: this.subjectType,
            };
        },
        methods: {
            goToSchedulesList() {
                this.$router.push({name: 'schedules'});
            },
            subjectChanged() {
                this.$emit('subject-change', this.subjectWithType.subject);
                this.$store.commit('updateSubject', this.subjectWithType);
            },
            filterOutNotSchedulableSubjects(subject) {
                if (subject.function.possibleActions.length === 0) {
                    return false;
                }
                if (subject.subjectType === 'channelGroup'
                    && ['CONTROLLINGTHEGATE', 'CONTROLLINGTHEGARAGEDOOR', 'VALVEOPENCLOSE', 'VALVEPERCENTAGE']
                        .indexOf(subject.function.name) !== -1) {
                    return false;
                }
                return true;
            },
            possibleActionFilter(possibleAction) {
                return possibleAction.name != 'OPEN_CLOSE' && possibleAction.name != 'TOGGLE';
            }
        },
        computed: {
            actionId: {
                get() {
                    return this.$store.state.actionId;
                },
                set(actionId) {
                    this.$store.commit('updateAction', actionId);
                }
            },
            actionParam: {
                get() {
                    return this.$store.state.actionParam;
                },
                set(actionParam) {
                    this.$store.commit('updateActionParam', actionParam);
                }
            },
            action: {
                get() {
                    return {id: this.actionId, param: this.actionParam};
                },
                set(action) {
                    this.actionId = action.id;
                    this.actionParam = action.param || {};
                }
            },
            ...mapState(['subject', 'subjectType']),
        },
        watch: {
            subject() {
                this.subjectWithType = {
                    subject: this.subject,
                    type: this.subjectType,
                };
            }
        }
    };
</script>
