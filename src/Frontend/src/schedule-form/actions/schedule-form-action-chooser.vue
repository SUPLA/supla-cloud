<template>
    <div>
        <div class="form-group">
            <label>{{ $t('Subject') }}</label>
            <select class="form-control" ref="channelsDropdown" :data-placeholder="$t('choose the channel')"
                    v-model="channel">
                <option v-for="channel in userChannels" :value="channel.id">
                    {{ channelTitle(channel) }}
                </option>
            </select>
        </div>
        <div v-show="channel">
            <div v-for="possibleAction in channelFunctionMap[channel]">
                <div class="radio">
                    <label>
                        <input type="radio" :value="possibleAction" v-model="action">
                        {{ $t(actionStringMap[possibleAction]) }}
                    </label>
                    <span class="input-group" v-if="possibleAction == 70" v-show="action == possibleAction">
                        <input type="number" min="0" max="100" step="5" class="form-control"
                               maxlength="3" v-model="actionParam">
                        <span class="input-group-addon">%</span>
                    </span>
                    <span class="input-group" v-if="possibleAction == 80 && action == possibleAction">
                        <hue-colorpicker v-model="actionParam"></hue-colorpicker>
                    </span>
                </div>
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
    import HueColorpicker from "./hue-colorpicker.vue";

    export default {
        name: 'schedule-form-action-chooser',
        components: {HueColorpicker},
        data() {
            return {
                userChannels: [],
                channelFunctionMap: {},
                actionStringMap: {}
            }
        },
        mounted() {
            this.$http.get('account/schedulable-channels').then(({body}) => {
                this.userChannels = body.userChannels;
                this.channelFunctionMap = body.channelFunctionMap;
                this.actionStringMap = body.actionStringMap;
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
            channel: {
                get() {
                    Vue.nextTick(() => $(this.$refs.channelsDropdown).trigger("chosen:updated"));
                    return this.$store.state.channel;
                },
                set(channel) {
                    this.$store.commit('updateChannel', channel)
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
