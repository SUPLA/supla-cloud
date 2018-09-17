<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Subject') }}</label>
            <subject-dropdown v-model="subjectWithType"
                channels-dropdown-params="io=output&hasFunction=1"
                @input="subjectChanged()"></subject-dropdown>
        </div>
        <div v-if="subject">
            <div v-for="possibleAction in subject.function.possibleActions">
                <div class="radio"
                    v-if="possibleAction.name != 'OPEN_CLOSE'">
                    <label>
                        <input type="radio"
                            :value="possibleAction.id"
                            v-model="actionId">
                        {{ $t(possibleAction.caption) }}
                    </label>
                </div>
                <span v-if="possibleAction.id == 50 && actionId == possibleAction.id">
                    <rolette-shutter-partial-percentage v-model="actionParam"></rolette-shutter-partial-percentage>
                </span>
                <span v-if="possibleAction.id == 80 && actionId == possibleAction.id">
                    <rgbw-parameters-setter v-model="actionParam"
                        :channel-function="subject.function"></rgbw-parameters-setter>
                </span>
            </div>
        </div>
    </div>
</template>

<script>
    import RgbwParametersSetter from "./rgbw-parameters-setter.vue";
    import RoletteShutterPartialPercentage from "./rolette-shutter-partial-percentage.vue";
    import ChannelsDropdown from "../../../devices/channels-dropdown";
    import SubjectDropdown from "../../../devices/subject-dropdown";
    import {mapState} from "vuex";

    export default {
        components: {SubjectDropdown, ChannelsDropdown, RgbwParametersSetter, RoletteShutterPartialPercentage},
        data() {
            return {
                subjectWithType: {}
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
