<template>
    <div>
        <dl>
            <dd>{{ $t('Price per') }} {{ unit }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="0.0001"
                        min="0"
                        max="1000"
                        class="form-control text-center"
                        v-model="pricePerUnit">
                </span>
            </dt>

            <dd>{{ $t('Currency') }}</dd>
            <dt>
                <currency-picker v-model="currency"></currency-picker>
            </dt>
        </dl>
        <channel-params-related-channel
            :channel="channel"
            label-i18n="Associated measured channel"
            channel-filter="function=POWERSWITCH,LIGHTSWITCH"
            param-no="4"
            @change="$emit('change')"></channel-params-related-channel>
    </div>
</template>

<script>
    import CurrencyPicker from "./currency-picker";
    import ChannelParamsRelatedChannel from "@/channels/params/channel-params-related-channel";

    export default {
        components: {ChannelParamsRelatedChannel, CurrencyPicker},
        props: ['channel', 'unit'],
        computed: {
            pricePerUnit: {
                set(value) {
                    this.channel.param2 = value * 10000;
                    this.$emit('change');
                },
                get() {
                    return this.channel.param2 / 10000;
                }
            },
            currency: {
                set(value) {
                    this.channel.textParam1 = value;
                    this.$emit('change');
                },
                get() {
                    return this.channel.textParam1;
                }
            }
        },
    };
</script>
