<template>
    <div>
        <dl v-if="channel.config.timeSettingAvailable">
            <dd>{{ $t('Full opening time') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="0.1"
                        min="0"
                        max="300"
                        class="form-control text-center"
                        v-model="channel.config.openingTimeS"
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
                        v-model="channel.config.closingTimeS"
                        @change="$emit('change')">
                    <span class="input-group-addon">
                        {{ $t('sec.') }}
                    </span>
                </span>
            </dt>
        </dl>
            <dl>
                <dd>{{ $t('Opening sensor') }}</dd>
                <dt class="text-center"
                    style="font-weight: normal">
                    <channels-dropdown :params="channelsDropdownFilter"
                        v-model="relatedChannel"
                        @input="relatedChannelChanged()"></channels-dropdown>
                </dt>
            </dl>
    </div>
</template>

<script>
    import ChannelsDropdown from "../../devices/channels-dropdown";

    export default {
        components: {ChannelsDropdown},
        props: ['channel', 'sensorFunction'],
        data() {
            return {
                relatedChannel: undefined
            };
        },
        mounted() {
            if (this.channel.config.openingSensorChannelId) {
                this.$http.get(`channels/${this.channel.config.openingSensorChannelId}`)
                    .then(response => this.relatedChannel = response.body);
            }
        },
        methods: {
            relatedChannelChanged() {
                this.channel.config.openingSensorChannelId = this.relatedChannel ? this.relatedChannel.id : 0;
                this.$emit('change');
            }
        },
        computed: {
            channelsDropdownFilter() {
                return 'function=' + (this.sensorFunction || 'OPENINGSENSOR_ROLLERSHUTTER');
            }
        }
    };
</script>
