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
                            :value="possibleAction"
                            v-model="action">
                        {{ $t(actionCaptions[possibleAction]) }}
                    </label>
                </div>
                <span v-if="possibleAction == 50 && action == possibleAction">
                    <rolette-shutter-partial-percentage v-model="actionParam"></rolette-shutter-partial-percentage>
                </span>
                <span v-if="possibleAction == 80 && action == possibleAction">
                    <rgbw-parameters-setter v-model="actionParam"></rgbw-parameters-setter>
                </span>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    import Vue from "vue";
    import {mapState} from "vuex";
    import moment from "moment";
    import "chosen-js";
    import "bootstrap-chosen/bootstrap-chosen.css";
    import RgbwParametersSetter from "./rgbw-parameters-setter.vue";
    import RoletteShutterPartialPercentage from "./rolette-shutter-partial-percentage.vue";

    export default {
        name: 'schedule-form-action-chooser',
        components: {RgbwParametersSetter, RoletteShutterPartialPercentage},
        data() {
            return {
                userChannels: [],
                channelFunctionMap: {},
                actionCaptions: {}
            }
        },
        mounted() {
            this.$http.get('account/schedulable-channels').then(({body}) => {
                this.userChannels = body.userChannels;
                this.channelFunctionMap = body.channelFunctionMap;
                this.actionCaptions = body.actionCaptions;
                Vue.nextTick(() => $(this.$refs.channelsDropdown).chosen().change((e) => {
                    this.channel = e.currentTarget.value;
                }));
            });
        },
        methods: {
            channelTitle(channel) {
                return (channel.caption || channel.functionName) + ` (${channel.device.location.caption} / ${channel.device.name})`;
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
                    if (this.channelFunctionMap[channel].length == 1) {
                        this.action = this.channelFunctionMap[channel][0];
                    }
                }
            },
            action: {
                get() {
                    return this.$store.state.action;
                },
                set(action) {
                    this.$store.commit('updateAction', action)
                }
            },
            actionParam: {
                get() {
                    return this.$store.state.actionParam;
                },
                set(actionParam) {
                    this.$store.commit('updateActionParam', actionParam)
                }
            }
        }
    }
</script>
