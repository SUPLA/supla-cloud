<template>
  <div>
    <ChannelParamsElectricityMeterEnabledPhases :channel="channel" @change="$emit('change')" />
    <dl>
      <dd>{{ $t('Associated measured channel') }}</dd>
      <dt>
        <channels-id-dropdown
          v-model="channel.config.relatedRelayChannelId"
          params="function=POWERSWITCH,LIGHTSWITCH,STAIRCASETIMER"
          @input="$emit('change')"
        />
      </dt>
    </dl>
    <AccordionRoot>
      <AccordionItem title-i18n="Costs">
        <channel-params-meter-cost :channel="channel" unit="kWh" @change="$emit('change')" />
      </AccordionItem>
      <AccordionItem title-i18n="History">
        <ChannelParamsMeterKeepHistoryMode v-model="channel.config.voltageLoggerEnabled" label-i18n="Store voltage history" @input="$emit('change')" />
        <ChannelParamsMeterKeepHistoryMode v-model="channel.config.currentLoggerEnabled" label-i18n="Store current history" @input="$emit('change')" />
        <ChannelParamsMeterKeepHistoryMode v-model="channel.config.powerActiveLoggerEnabled" label-i18n="Store active power history" @input="$emit('change')" />
        <channel-params-electricity-meter-initial-values :channel="channel" @save="$emit('save')" />
      </AccordionItem>
      <AccordionItem title-i18n="Voltage monitoring">
        <ChannelParamsElectricityMeterVoltageThresholds :channel="channel" @change="$emit('change')" />
      </AccordionItem>
      <AccordionItem v-if="displayOthersTab" title-i18n="Other">
        <ChannelParamsElectricityMeterOtherSettings :channel="channel" @change="$emit('change')" />
      </AccordionItem>
    </AccordionRoot>

    <channel-params-meter-reset :channel="channel" class="mt-4" />
  </div>
</template>

<script setup>
  import ChannelParamsMeterCost from './channel-params-meter-cost.vue';
  import ChannelsIdDropdown from '@/devices/channels-id-dropdown.vue';
  import ChannelParamsMeterReset from '@/channels/params/channel-params-meter-reset.vue';
  import ChannelParamsElectricityMeterInitialValues from '@/channels/params/channel-params-electricity-meter-initial-values.vue';
  import ChannelParamsElectricityMeterVoltageThresholds from '@/channels/params/channel-params-electricity-meter-voltage-thresholds.vue';
  import ChannelParamsElectricityMeterEnabledPhases from '@/channels/params/channel-params-electricity-meter-enabled-phases.vue';
  import ChannelParamsMeterKeepHistoryMode from '@/channels/params/channel-params-meter-keep-history-mode.vue';
  import {computed} from 'vue';
  import ChannelParamsElectricityMeterOtherSettings from '@/channels/params/channel-params-electricity-meter-other-settings.vue';
  import AccordionRoot from '@/common/gui/accordion/accordion-root.vue';
  import AccordionItem from '@/common/gui/accordion/accordion-item.vue';

  const props = defineProps({channel: Object});

  const displayOthersTab = computed(() => {
    const hasPhaseLedTypes = props.channel.config.availablePhaseLedTypes && props.channel.config.availablePhaseLedTypes.length > 0;
    const hasCTTypes = props.channel.config.availableCTTypes && props.channel.config.availableCTTypes.length > 0;
    return hasPhaseLedTypes || hasCTTypes;
  });
</script>
