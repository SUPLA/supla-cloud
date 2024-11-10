<template>
    <channels-dropdown :params="params"
        v-model="channel"
        :hide-none="hideNone"
        :choose-prompt-i18n="choosePromptI18n"
        :disabled="disabled"
        :filter="filter"
        @input="channelChanged()"></channels-dropdown>
</template>

<script>
    import ChannelsDropdown from "./channels-dropdown";

    export default {
        components: {ChannelsDropdown},
        props: ['value', 'params', 'hideNone', 'choosePromptI18n', 'disabled', 'filter'],
        data() {
            return {
                channel: undefined,
            };
        },
        mounted() {
            this.updateChannel();
        },
        methods: {
            updateChannel() {
                if (this.value) {
                    this.$http.get(`channels/${this.value}`).then(response => {
                        this.channel = response.body;
                        this.emitChannel();
                    });
                } else {
                    this.channel = undefined;
                    this.emitChannel();

                }
            },
            channelChanged() {
                this.$emit('input', this.channel?.id || 0);
                this.emitChannel();
            },
            emitChannel() {
                this.$emit('channelChanged', this.channel);
            }
        },
        watch: {
            value() {
                this.updateChannel();
            },
        }
    };
</script>
