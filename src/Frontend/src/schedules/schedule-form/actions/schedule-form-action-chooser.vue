<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Subject') }}</label>
            <subject-dropdown v-model="subjectWithType"
                channels-dropdown-params="io=output&hasFunction=1"
                @input="subjectChanged()"></subject-dropdown>
            <!--<channels-dropdown params="io=output&hasFunction=1"-->
            <!--:initial-id="channelId"-->
            <!--@input="channelId = $event.id"-->
            <!--hide-none="true">-->
            <!--</channels-dropdown>-->
        </div>
        <div v-if="subject">
            <div v-for="possibleAction in subject.function.possibleActions">
                <div class="radio">
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
        <!--<modal v-if="userChannels === undefined"-->
        <!--class="modal-warning"-->
        <!--@confirm="goToSchedulesList()"-->
        <!--:header="$t('You have no devices that can be added to the schedule')">-->
        <!--{{ $t('You will be redirected back to the schedules list now.') }}-->
        <!--</modal>-->
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
                // userChannels: [],
                // channelFunctionMap: {},
                subjectWithType: {}
            };
        },
        mounted() {
            this.$http.get('users/current/schedulable-channels').then(({body}) => {
                // if (body.userChannels.length) {
                //     this.userChannels = body.userChannels;
                //     this.channelFunctionMap = body.channelFunctionMap;
                // } else {
                //     this.userChannels = undefined;
                // }
                this.subjectWithType = {
                    subject: this.subject,
                    type: this.subjectType,
                };
            });
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
            // channelId: {
            //     get() {
                    // Vue.nextTick(() => $(this.$refs.channelsDropdown).trigger("chosen:updated"));
            // return this.$store.state.channelId;
            // },
            // set(channelId) {
            //     this.$store.commit('updateChannel', channelId);
            //     if (channelId) {
            //         this.actionId = this.channelFunctionMap[channelId][0].id;
            //     }
            // }
            // },
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
        }
    };
</script>
