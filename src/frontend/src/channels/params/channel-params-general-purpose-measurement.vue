<template>
    <div>
        <a class="d-flex accordion-header" @click="displayConfigGroup('measurements')"
            v-if="canDisplayAnySetting('valueMultiplier', 'valueDivider', 'valueAdded', 'valuePrecision', 'unitBeforeValue', 'unitAfterValue', 'refreshIntervalMs')">
            <span class="flex-grow-1">{{ $t('Measurement settings') }}</span>
            <span>
                <fa :icon="configGroupChevron('measurements')"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="configGroup === 'measurements'">
                <ChannelParamsGeneralPurposeCommon :channel="props.channel" @change="$emit('change')"/>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayConfigGroup('history')" v-if="canDisplayAnySetting('keepHistory', 'chartType')">
            <span class="flex-grow-1">{{ $t('History') }}</span>
            <span>
                <fa :icon="configGroupChevron('history')"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="configGroup === 'history'" v-if="canDisplayAnySetting('keepHistory', 'chartType')">
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
            </div>
        </transition-expand>
    </div>
</template>

<script setup>
    import ChannelParamsButtonSelector from "./channel-params-button-selector";
    import TransitionExpand from "../../common/gui/transition-expand";
    import ChannelParamsGeneralPurposeCommon from "@/channels/params/channel-params-general-purpose-common.vue";
    import {useConfigGroups} from "@/channels/params/useConfigGroups";
    import ChannelParamsMeterKeepHistoryMode from "@/channels/params/channel-params-meter-keep-history-mode.vue";
    import {useDisplaySettings} from "@/channels/params/useDisplaySettings";

    const props = defineProps({channel: Object});
    const {configGroup, displayConfigGroup, configGroupChevron} = useConfigGroups();
    const {canDisplayAnySetting, canDisplaySetting} = useDisplaySettings(props.channel);
</script>
