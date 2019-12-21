<template>
    <dl>
        <dd>{{ $t('Full opening time') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.1"
                    min="0"
                    max="300"
                    class="form-control text-center"
                    v-model="channel.params.openingTimeS"
                    @change="$emit('change')">
                <span class="input-group-addon">
                    {{ $t('sec.') }}
                </span>
            </span>
        </dt>
        <dd>{{ $t('Full closing time') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.1"
                    min="0"
                    max="300"
                    class="form-control text-center"
                    v-model="channel.params.closingTimeS"
                    @change="$emit('change')">
                <span class="input-group-addon">
                    {{ $t('sec.') }}
                </span>
            </span>
        </dt>
        <dl>
            <dd>{{ $t('Opening sensor') }}</dd>
            <dt class="text-center"
                style="font-weight: normal">
                <channels-dropdown params="function=OPENINGSENSOR_ROLLERSHUTTER"
                    v-model="relatedChannel"
                    @input="relatedChannelChanged()"></channels-dropdown>
            </dt>
        </dl>
    </dl>
</template>

<script>
    import ChannelParamsControllingAnyLock from "./channel-params-controlling-any-lock";
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {ChannelsDropdown, ChannelParamsControllingAnyLock},
        props: ['channel'],
        data() {
            return {
                relatedChannel: undefined
            };
        },
        mounted() {
            if (this.channel.params.openingSensorChannelId) {
                this.$http.get(`channels/${this.channel.params.openingSensorChannelId}`)
                    .then(response => this.relatedChannel = response.body);
            }
        },
        methods: {
            relatedChannelChanged() {
                this.channel.params.openingSensorChannelId = this.relatedChannel ? this.relatedChannel.id : 0;
                this.$emit('change');
            }
        }
    };
</script>
