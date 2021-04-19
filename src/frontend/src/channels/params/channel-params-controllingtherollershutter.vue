<template>
    <div>
        <dl v-if="timeSelectorEnabled">
            <dd>{{ $t('Full opening time') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="0.1"
                        min="0"
                        max="300"
                        class="form-control text-center"
                        v-model="param1">
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
                        v-model="param3">
                    <span class="input-group-addon">
                        {{ $t('sec.') }}
                    </span>
                </span>
            </dt>
        </dl>
        <dl>
            <dl>
                <dd>{{ $t('Opening sensor') }}</dd>
                <dt class="text-center"
                    style="font-weight: normal">
                    <channels-dropdown :params="channelsDropdownFilter"
                        v-model="relatedChannel"
                        @input="relatedChannelChanged()"></channels-dropdown>
                </dt>
            </dl>
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
            if (this.channel.param2) {
                this.$http.get(`channels/${this.channel.param2}`).then(response => this.relatedChannel = response.body);
            }
        },
        methods: {
            relatedChannelChanged() {
                this.channel.param2 = this.relatedChannel ? this.relatedChannel.id : 0;
                this.$emit('change');
            }
        },
        computed: {
            channelsDropdownFilter() {
                return 'function=' + (this.sensorFunction || 'OPENINGSENSOR_ROLLERSHUTTER');
            },
            param1: {
                set(value) {
                    this.channel.param1 = Math.round(value * 10);
                    this.$emit('change');
                },
                get() {
                    return this.channel.param1 / 10;
                }
            },
            param3: {
                set(value) {
                    this.channel.param3 = Math.round(value * 10);
                    this.$emit('change');
                },
                get() {
                    return this.channel.param3 / 10;
                }
            },
            timeSelectorEnabled() {
                return this.channel && !(this.channel.flags & 0x00100000);
            }
        }
    };
</script>
