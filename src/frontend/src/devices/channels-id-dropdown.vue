<template>
    <channels-dropdown :params="params"
        v-model="channel"
        @input="channelChanged()"></channels-dropdown>
</template>

<script>
    import ChannelsDropdown from "./channels-dropdown";

    export default {
        components: {ChannelsDropdown},
        props: ['value', 'params'],
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
                    this.$http.get(`channels/${this.value}`).then(response => this.channel = response.body);
                } else {
                    this.channel = undefined;
                }
            },
            channelChanged() {
                this.$emit('input', this.channel?.id || 0);
            }
        },
        watch: {
            value() {
                this.updateChannel();
            },
        }
    };
</script>
