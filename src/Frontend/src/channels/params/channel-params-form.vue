<template>
    <component :is="additionalChannelParamsComponent"
        :channel="channel"
        @change="$emit('change')"></component>
</template>

<script>
    import changeCase from "change-case";
    import ChannelParamsNone from "./channel-params-none";
    import ChannelParamsControllingthegatewaylock from "./channel-params-controllingthegatewaylock";
    import ChannelParamsControllingthegate from "./channel-params-controllingthegate";
    import ChannelParamsControllingthegaragedoor from "./channel-params-controllingthegaragedoor";
    import ChannelParamsControllingthedoorlock from "./channel-params-controllingthedoorlock";

    export default {
        props: ['channel'],
        components: {
            ChannelParamsControllingthedoorlock,
            ChannelParamsControllingthegaragedoor,
            ChannelParamsNone,
            ChannelParamsControllingthegate,
            ChannelParamsControllingthegatewaylock,
        },
        computed: {
            additionalChannelParamsComponent() {
                const fncName = changeCase.camelCase(this.channel.function.name);
                let componentName = 'ChannelParams' + changeCase.upperCaseFirst(fncName);
                console.log(componentName);
                if (this.$options.components[componentName]) {
                    return changeCase.headerCase(componentName);
                } else {
                    return 'channel-params-none';
                }
            }
        }
    };
</script>
