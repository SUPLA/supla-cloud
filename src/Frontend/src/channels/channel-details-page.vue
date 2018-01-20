<template>
    <div>
        <loading-cover :loading="!channel">
            <div class="container"
                v-if="channel">
                <h1>
                    <a :href="`/iodev/${channel.iodeviceId}/view` | withBaseUrl">{{ deviceTitle }}</a>
                    &raquo;
                    {{ channelTitle }}
                </h1>
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
                            <function-icon :model="channel"
                                width="100"></function-icon>
                            <h4>{{ $t(channel.function.caption) }}</h4>
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

    export default {
        props: ['channelId'],
        components: {DotsRoute, FunctionIcon},
        data() {
            return {
                channel: undefined
            };
        },
        mounted() {
            this.$http.get(`channels/${this.channelId}?include=iodevice,location,type,function`).then(response => {
                this.channel = response.body;
            });
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
