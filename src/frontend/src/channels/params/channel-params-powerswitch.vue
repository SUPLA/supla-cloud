<template>
    <div>
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
