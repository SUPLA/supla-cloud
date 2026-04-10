<template>
  <div>
    <AccordionRoot>
      <AccordionItem title-i18n="Measurement settings">
        <ChannelParamsGeneralPurposeCommon :channel="props.channel" @change="$emit('change')" />
      </AccordionItem>
      <AccordionItem title-i18n="History">
        <ChannelParamsMeterKeepHistoryMode v-model="props.channel.config.keepHistory" @input="$emit('change')" />
        <dl>
          <dd>{{ $t('Chart type') }}</dd>
          <dt>
            <ChannelParamsButtonSelector
              v-model="props.channel.config.chartType"
              use-dropdown
              :values="[
                {id: 'LINEAR', label: $t('Linear')},
                {id: 'BAR', label: $t('Bar')},
              ]"
              @input="$emit('change')"
            />
          </dt>
          <dd>{{ $t('Counter type') }}</dd>
          <dt class="text-center">
            <ChannelParamsButtonSelector
              v-model="props.channel.config.counterType"
              use-dropdown
              :values="[
                {id: 'ALWAYS_INCREMENT', label: $t('Always increment')},
                {id: 'ALWAYS_DECREMENT', label: $t('Always decrement')},
                {id: 'INCREMENT_AND_DECREMENT', label: $t('Increment and decrement')},
              ]"
              @input="$emit('change')"
            />
          </dt>
          <dd>{{ $t('Fill missing data') }}</dd>
          <dt class="text-center">
            <toggler v-model="props.channel.config.fillMissingData" @update:model-value="$emit('change')"></toggler>
          </dt>
        </dl>
      </AccordionItem>
    </AccordionRoot>

    <ChannelParamsMeterReset :channel="channel" />
  </div>
</template>

<script setup>
  import ChannelParamsGeneralPurposeCommon from '@/channels/params/channel-params-general-purpose-common.vue';
  import ChannelParamsButtonSelector from '@/channels/params/channel-params-button-selector.vue';
  import ChannelParamsMeterReset from '@/channels/params/channel-params-meter-reset.vue';
  import ChannelParamsMeterKeepHistoryMode from '@/channels/params/channel-params-meter-keep-history-mode.vue';
  import AccordionItem from '@/common/gui/accordion/accordion-item.vue';
  import AccordionRoot from '@/common/gui/accordion/accordion-root.vue';

  const props = defineProps({channel: Object});
</script>
