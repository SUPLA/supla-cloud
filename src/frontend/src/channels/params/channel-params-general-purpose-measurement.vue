<template>
    <div>
        <AccordionRoot>
            <AccordionItem title-i18n="Measurement settings"
                v-if="canDisplayAnySetting('valueMultiplier', 'valueDivider', 'valueAdded', 'valuePrecision', 'unitBeforeValue', 'unitAfterValue', 'refreshIntervalMs')">
                <ChannelParamsGeneralPurposeCommon :channel="props.channel" @change="$emit('change')"/>
            </AccordionItem>
            <AccordionItem title-i18n="History" v-if="canDisplayAnySetting('keepHistory', 'chartType')">
                <ChannelParamsMeterKeepHistoryMode v-model="props.channel.config.keepHistory" @input="$emit('change')"
                    v-if="canDisplaySetting('keepHistory')"/>
                <dl v-if="canDisplaySetting('chartType')">
                    <dd>{{ $t('Chart type') }}</dd>
                    <dt>
                        <ChannelParamsButtonSelector
                            use-dropdown
                            v-model="props.channel.config.chartType"
                            @input="$emit('change')"
                            :values="[{id: 'LINEAR', label: $t('Linear')}, {id: 'BAR', label: $t('Bar')}, {id: 'CANDLE', label: $t('Candle')}]"/>
                    </dt>
                </dl>
            </AccordionItem>
        </AccordionRoot>
    </div>
</template>

<script setup>
    import ChannelParamsButtonSelector from "./channel-params-button-selector";
    import ChannelParamsGeneralPurposeCommon from "@/channels/params/channel-params-general-purpose-common.vue";
    import ChannelParamsMeterKeepHistoryMode from "@/channels/params/channel-params-meter-keep-history-mode.vue";
    import {useDisplaySettings} from "@/channels/params/useDisplaySettings";
    import AccordionRoot from "@/common/gui/accordion/accordion-root.vue";
    import AccordionItem from "@/common/gui/accordion/accordion-item.vue";

    const props = defineProps({channel: Object});
    const {canDisplayAnySetting, canDisplaySetting} = useDisplaySettings(props.channel);
</script>
