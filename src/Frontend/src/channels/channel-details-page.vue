<template>
    <div>
        <loading-cover :loading="!channel || loading">
            <div class="container"
                v-if="channel">
                <a :href="`/iodev/${channel.iodeviceId}/view` | withBaseUrl">&laquo; {{ deviceTitle }}</a>
                <div class="clearfix left-right-header">
                    <h1>{{ channelTitle }}</h1>
                    <div>
                        <switches v-model="channel.enabled"
                            @input="toggleEnabled()"
                            type-bold="true"
                            color="green"
                            class="pull-right"
                            :emit-on-mount="false"
                            :text-enabled="$t('Enabled')"
                            :text-disabled="$t('Disabled')"></switches>
                    </div>
                </div>
                <h4>{{ $t(channel.type.caption) }}</h4>
                <div class="row hidden-xs">
                    <div class="col-xs-12">
                        <dots-route></dots-route>
                    </div>
                </div>
                <div class="form-group">
                    <div class="row text-center">
                        <div class="col-sm-4">
                            <h3>{{ $t('Function') }}</h3>
                            <div class="hover-editable text-left">
                                <div class="form-group"
                                    v-if="channel.supportedFunctions.length > 1">
                                    <div class="dropdown">
                                        <button class="btn btn-default dropdown-toggle btn-block btn-wrapped"
                                            type="button"
                                            data-toggle="dropdown">
                                            <h4>{{ $t(channel.function.caption) }}</h4>
                                            <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li v-for="fnc in channel.supportedFunctions">
                                                <a @click="onFunctionChange(fnc)"
                                                    v-show="channel.function.id != fnc.id">{{ $t(fnc.caption) }}</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <h4 class="text-center"
                                    v-else>{{ $t(channel.function.caption) }}</h4>
                                <dl>
                                    <dd>{{ $t('Caption') }}</dd>
                                    <dt>
                                        <input type="text"
                                            class="form-control text-center"
                                            @change="updateChannel()"
                                            v-model="channel.caption">
                                    </dt>
                                </dl>
                                <channel-params-form :channel="channel"
                                    @change="updateChannel()"></channel-params-form>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <h3>{{ $t('Location') }}</h3>
                            <square-location-chooser v-model="channel.location"
                                @input="onLocationChange($event)"></square-location-chooser>
                        </div>
                        <div class="col-sm-4">
                            <h3>{{ $t('State') }}</h3>
                            <function-icon :model="channel.function"
                                :alternative="channel.altIcon"
                                width="100"></function-icon>
                            <channel-alternative-icon-chooser :channel="channel"
                                @change="updateChannel()"></channel-alternative-icon-chooser>
                            <channel-state-table :channel="channel"></channel-state-table>
                        </div>
                    </div>
                </div>
            </div>
        </loading-cover>
    </div>
</template>

<script>
    import {channelTitle, deviceTitle} from "../common/filters";
    import DotsRoute from "../common/gui/dots-route.vue";
    import FunctionIcon from "./function-icon";
    import Switches from "vue-switches";
    import ChannelParamsForm from "./params/channel-params-form";
    import SquareLocationChooser from "../locations/square-location-chooser";
    import Vue from "vue";
    import ChannelAlternativeIconChooser from "./channel-alternative-icon-chooser";
    import ChannelStateTable from "./channel-state-table";

    export default {
        props: ['channelId'],
        components: {
            ChannelStateTable,
            ChannelAlternativeIconChooser,
            SquareLocationChooser,
            ChannelParamsForm,
            DotsRoute, FunctionIcon, Switches
        },
        data() {
            return {
                channel: undefined,
                loading: false,
            };
        },
        mounted() {
            this.$http.get(`channels/${this.channelId}?include=iodevice,location,type,function,supportedFunctions`).then(response => {
                this.channel = response.body;
                this.$set(this.channel, 'enabled', !!this.channel.function.id);
            });
        },
        methods: {
            toggleEnabled() {
                if (this.channel.enabled && !this.channel.function.id) {
                    this.$set(this.channel, 'function', this.channel.supportedFunctions[0]);
                } else if (!this.channel.enabled) {
                    this.$set(this.channel, 'function', {id: 0, caption: 'None'});
                }
                this.updateChannel();
            },
            updateChannel() {
                this.loading = true;
                this.$http.put(`channels/${this.channelId}`, this.channel)
                    .then(response => Vue.extend(this.channel, response.body))
                    .finally(() => this.loading = false);
            },
            onLocationChange(location) {
                this.$set(this.channel, 'location', location);
                this.updateChannel();
            },
            onFunctionChange(fnc) {
                this.channel.function = fnc;
                this.updateChannel();
            }
        },
        computed: {
            channelTitle() {
                return channelTitle(this.channel, this);
            },
            deviceTitle() {
                return deviceTitle(this.channel.iodevice, this);
            },

        }
    };
</script>

<style lang="scss">
    .dropdown h4 {
        margin: 0;
        display: inline-block;
    }
</style>
