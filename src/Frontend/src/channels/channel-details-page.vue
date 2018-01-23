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
                            <function-icon :model="channel.function"
                                width="100"></function-icon>
                            <h4>{{ $t(channel.function.caption) }}</h4>
                            <select v-model="channel.function"
                                v-if="channel.enabled && channel.supportedFunctions.length > 1"
                                @change="updateChannel()"
                                class="form-control">
                                <option v-for="fnc in channel.supportedFunctions"
                                    :value="fnc">
                                    {{ $t(fnc.caption) }}
                                </option>
                            </select>
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

    export default {
        props: ['channelId'],
        components: {DotsRoute, FunctionIcon, Switches},
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
                    .finally(() => this.loading = false);
            }
        },
        computed: {
            channelTitle() {
                return channelTitle(this.channel, this);
            },
            deviceTitle() {
                return deviceTitle(this.channel.iodevice, this);
            }
        }
    };
</script>
