<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Subject') }}</label>
            <select class="form-control"
                ref="channelsDropdown"
                :data-placeholder="$t('choose the channel')"
                v-model="channel">
                <option v-for="channel in userChannels"
                    :value="channel.id">
                    {{ channelTitle(channel) }}
                </option>
            </select>
        </div>
        <div v-show="channel">
            <div v-for="possibleAction in channelFunctionMap[channel]">
                <div class="radio">
                    <label>
                        <input type="radio"
                            :value="possibleAction.id"
                            v-model="action">
                        {{ $t(possibleAction.caption) }}
                    </label>
                </div>
                <span v-if="possibleAction.id == 50 && action == possibleAction.id">
                    <rolette-shutter-partial-percentage v-model="actionParam"></rolette-shutter-partial-percentage>
                </span>
                <span v-if="possibleAction.id == 80 && action == possibleAction.id">
                    <rgbw-parameters-setter v-model="actionParam"
                        :channel-function="chosenChannel.function"></rgbw-parameters-setter>
                </span>
            </div>
        </div>
        <modal v-if="userChannels === undefined"
            class="modal-warning"
            @close="goToSchedulesList()"
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
                        this.channel = e.currentTarget.value;
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
                return this.userChannels.filter(c => c.id == this.channel)[0];
            },
            channel: {
                get() {
                    Vue.nextTick(() => $(this.$refs.channelsDropdown).trigger("chosen:updated"));
                    return this.$store.state.channel;
                },
                set(channel) {
                    this.$store.commit('updateChannel', channel);
                    if (channel && this.channelFunctionMap[channel].length == 1) {
                        this.action = this.channelFunctionMap[channel][0];
                    }
                }
            },
            action: {
                get() {
                    return this.$store.state.action;
                },
                set(action) {
                    this.$store.commit('updateAction', action);
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
