<template>
  <div>
    <AccordionRoot>
      <AccordionItem
        v-if="canDisplayAnySetting('valueMultiplier', 'valueDivider', 'valueAdded', 'valuePrecision', 'unitBeforeValue', 'unitAfterValue', 'refreshIntervalMs')"
        title-i18n="Measurement settings"
      >
        <ChannelParamsGeneralPurposeCommon :channel="props.channel" @change="$emit('change')" />
      </AccordionItem>
      <AccordionItem v-if="canDisplayAnySetting('keepHistory', 'chartType')" title-i18n="History">
        <ChannelParamsMeterKeepHistoryMode v-if="canDisplaySetting('keepHistory')" v-model="props.channel.config.keepHistory" @input="$emit('change')" />
        <dl v-if="canDisplaySetting('chartType')">
          <dd>{{ $t('Chart type') }}</dd>
          <dt>
            <ChannelParamsButtonSelector
              v-model="props.channel.config.chartType"
              use-dropdown
              :values="[
                {id: 'LINEAR', label: $t('Linear')},
                {id: 'BAR', label: $t('Bar')},
                {id: 'CANDLE', label: $t('Candle')},
              ]"
              @input="$emit('change')"
            />
          </dt>
        </dl>
      </AccordionItem>
    </AccordionRoot>
  </div>
</template>

<script setup>
  import ChannelParamsButtonSelector from './channel-params-button-selector.vue';
  import ChannelParamsGeneralPurposeCommon from '@/channels/params/channel-params-general-purpose-common.vue';
  import ChannelParamsMeterKeepHistoryMode from '@/channels/params/channel-params-meter-keep-history-mode.vue';
  import {useDisplaySettings} from '@/channels/params/useDisplaySettings';
  import AccordionRoot from '@/common/gui/accordion/accordion-root.vue';
  import AccordionItem from '@/common/gui/accordion/accordion-item.vue';

  const props = defineProps({channel: Object});
  const {canDisplayAnySetting, canDisplaySetting} = useDisplaySettings(props.channel);
</script>
