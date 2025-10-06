<template>
    <div>
        <dl>
            <dd>{{ $t('Associated measurement channel') }}</dd>
            <dt>
                <channels-id-dropdown params="function=ELECTRICITYMETER,IC_ELECTRICITYMETER,IC_GASMETER,IC_WATERMETER,IC_HEATMETER"
                    v-model="channel.config.relatedMeterChannelId"
                    @input="emit('change')"></channels-id-dropdown>
            </dt>
        </dl>
        <ChannelParamsOvercurrentThreshold :channel="channel" @change="emit('change')"/>
    </div>
</template>

<script setup>
  import ChannelsIdDropdown from "@/devices/channels-id-dropdown.vue";
  import ChannelParamsOvercurrentThreshold
    from "@/channels/params/channel-params-overcurrent-threshold.vue";

  defineProps({channel: Object});
    const emit = defineEmits(["change"]);
</script>
