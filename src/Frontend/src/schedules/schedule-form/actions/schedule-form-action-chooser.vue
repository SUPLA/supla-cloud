<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Subject') }}</label>
            <select class="form-control"
                ref="channelsDropdown"
                :data-placeholder="$t('choose the channel')"
                v-model="channelId">
                <option v-for="channel in userChannels"
                    :value="channel.id">
                    {{ channelTitle(channel) }}
                </option>
            </select>
        </div>
        <div v-show="channelId">
            <div v-for="possibleAction in channelFunctionMap[channelId]">
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
                        :channel-function="chosenChannel.function"></rgbw-parameters-setter>
                </span>
            </div>
        </div>
        <modal v-if="userChannels === undefined"
            class="modal-warning"
            @confirm="goToSchedulesList()"
            :header="$t('You have no devices that can be added to the schedule')">
            {{ $t('You will be redirected back to the schedules list now.') }}
        </modal>
    </div>
</template>

<script>
    import Vue from "vue";
    import "chosen-js";
    import "bootstrap-chosen/bootstrap-chosen.css";
    import RgbwParametersSetter from "./rgbw-parameters-setter.vue";
    import RoletteShutterPartialPercentage from "./rolette-shutter-partial-percentage.vue";
    import {withBaseUrl} from "../../../common/filters";

    export default {
        name: 'schedule-form-action-chooser',
        components: {RgbwParametersSetter, RoletteShutterPartialPercentage},
        data() {
            return {
                userChannels: [],
                channelFunctionMap: {}
            };
        },
        mounted() {
            this.$http.get('users/current/schedulable-channels').then(({body}) => {
                if (body.userChannels.length) {
                    this.userChannels = body.userChannels;
                    this.channelFunctionMap = body.channelFunctionMap;
                    Vue.nextTick(() => $(this.$refs.channelsDropdown).chosen().change((e) => {
                        this.channelId = e.currentTarget.value;
                    }));
                } else {
                    this.userChannels = undefined;
                }
            });
        },
        methods: {
            channelTitle(channel) {
                return `ID${channel.id} ` + (channel.caption || channel.functionName)
                    + ` (${channel.device.location.caption} / ${channel.device.name})`;
            },
            goToSchedulesList() {
                window.location.assign(withBaseUrl('schedules'));
            }
        },
        computed: {
            chosenChannel() {
                return this.userChannels.filter(c => c.id == this.channelId)[0];
            },
            channelId: {
                get() {
                    Vue.nextTick(() => $(this.$refs.channelsDropdown).trigger("chosen:updated"));
                    return this.$store.state.channelId;
                },
                set(channelId) {
                    this.$store.commit('updateChannel', channelId);
                    if (channelId && this.channelFunctionMap[channelId].length == 1) {
                        this.actionId = this.channelFunctionMap[channelId][0];
                    }
                }
            },
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
            }
        }
    };
</script>
