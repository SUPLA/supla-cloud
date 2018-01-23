<template>
    <component :is="additionalChannelParamsComponent"></component>
</template>

<script>
    import changeCase from "change-case";
    import ChannelParamsNone from "./channel-params-none";
    import ChannelParamsControllingthegatewaylock from "./channel-params-controllingthegatewaylock";

    export default {
        props: ['channel'],
        components: {
            ChannelParamsNone,
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
