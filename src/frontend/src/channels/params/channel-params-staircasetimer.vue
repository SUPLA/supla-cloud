<template>
    <div>
        <dl v-if="channel.config.timeSettingAvailable">
            <dd>{{ $t('Relay switching time') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="number"
                        step="0.5"
                        min="0.5"
                        max="7200"
                        class="form-control text-center"
                        @input="$emit('change')"
                        v-model="channel.config.relayTimeS">
                    <span class="input-group-addon">
                        {{ $t('sec.') }}
                    </span>
                </span>
            </dt>
        </dl>
        <dl>
            <dd>{{ $t('Associated measurement channel') }}</dd>
            <dt>
                <channels-id-dropdown params="function=ELECTRICITYMETER,IC_ELECTRICITYMETER,IC_GASMETER,IC_WATERMETER,IC_HEATMETER"
                    v-model="channel.config.relatedMeterChannelId"
                    @input="$emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
    </div>
</template>

<script>
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";

    export default {
        components: {ChannelsIdDropdown},
        props: ['channel'],
    };
</script>
