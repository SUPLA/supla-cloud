<template>
    <div>
        <dl>
            <dd>{{ $t('Unit') }}</dd>
            <dt>
                <input type="text"
                    maxlength="8"
                    class="form-control text-center"
                    v-model="channel.config.unit"
                    @input="$emit('change')">
            </dt>
            <channel-params-meter-cost :channel="channel"
                :unit="unit"
                @change="$emit('change')"></channel-params-meter-cost>
            <dd>{{ $t('Impulses') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="1"
                        min="0"
                        max="1000000"
                        class="form-control text-center"
                        v-model="channel.config.impulsesPerUnit"
                        @change="$emit('change')">
                    <span class="input-group-addon">
                        / {{ unit }}
                    </span>
                </span>
            </dt>
            <dd class="valign-top">{{ $t('Value added') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="0.001"
                        min="-100000000"
                        max="100000000"
                        class="form-control text-center"
                        v-model="channel.config.initialValue"
                        @change="$emit('change')">
                    <span class="input-group-addon">
                        {{ unit }}
                    </span>
                </span>
                <channel-params-meter-initial-values-mode v-model="channel.config.addToHistory"
                    class="small"
                    @input="$emit('change')"></channel-params-meter-initial-values-mode>
            </dt>
            <dd>{{ $t('Associated measured channel') }}</dd>
            <dt>
                <channels-id-dropdown params="function=POWERSWITCH,LIGHTSWITCH,STAIRCASETIMER"
                    v-model="channel.config.relatedRelayChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
        <div class="form-group"></div>
        <channel-params-meter-reset :channel="channel"></channel-params-meter-reset>
    </div>
</template>

<script>
    import ChannelParamsMeterCost from "./channel-params-meter-cost";
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import ChannelParamsMeterReset from "@/channels/params/channel-params-meter-reset";
    import ChannelParamsMeterInitialValuesMode from "./channel-params-meter-initial-values-mode";
    import {measurementUnit} from "@/channels/channel-helpers";

    export default {
        components: {ChannelParamsMeterInitialValuesMode, ChannelParamsMeterReset, ChannelsIdDropdown, ChannelParamsMeterCost},
        props: ['channel'],
        computed: {
            unit() {
                return measurementUnit(this.channel);
            }
        }
    };
</script>
