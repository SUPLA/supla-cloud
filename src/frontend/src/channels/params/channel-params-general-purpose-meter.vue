<template>
    <div>
        <a class="d-flex accordion-header" @click="displayConfigGroup('measurements')">
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
        <a class="d-flex accordion-header" @click="displayConfigGroup('history')">
            <span class="flex-grow-1">{{ $t('History') }}</span>
            <span>
                <fa :icon="configGroupChevron('history')"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="configGroup === 'history'">
                <ChannelParamsMeterKeepHistoryMode v-model="props.channel.config.keepHistory" @input="$emit('change')"/>
                <dl>
                    <dd>{{ $t('Chart type') }}</dd>
                    <dt>
                        <ChannelParamsButtonSelector
                            use-dropdown
                            v-model="props.channel.config.chartType"
                            @input="$emit('change')"
                            :values="[{id: 'LINEAR', label: $t('Linear')}, {id: 'BAR', label: $t('Bar')}]"/>
                    </dt>
                    <dd>{{ $t('Counter type') }}</dd>
                    <dt class="text-center">
                        <ChannelParamsButtonSelector
                            v-model="props.channel.config.counterType"
                            use-dropdown
                            @input="$emit('change')"
                            :values="[{id: 'ALWAYS_INCREMENT', label: $t('Always increment')}, {id: 'ALWAYS_DECREMENT', label: $t('Always decrement')}, {id: 'INCREMENT_AND_DECREMENT', label: $t('Increment and decrement')}]"/>
                    </dt>
                    <dd>{{ $t('Fill missing data') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="props.channel.config.fillMissingData"
                            @input="$emit('change')"></toggler>
                    </dt>
                </dl>
            </div>
        </transition-expand>
        <ChannelParamsMeterReset :channel="channel"/>
    </div>
</template>

<script setup>
    import TransitionExpand from "../../common/gui/transition-expand";
    import ChannelParamsGeneralPurposeCommon from "@/channels/params/channel-params-general-purpose-common.vue";
    import ChannelParamsButtonSelector from "@/channels/params/channel-params-button-selector.vue";
    import {useConfigGroups} from "@/channels/params/useConfigGroups";
    import ChannelParamsMeterReset from "@/channels/params/channel-params-meter-reset.vue";
    import ChannelParamsMeterKeepHistoryMode from "@/channels/params/channel-params-meter-keep-history-mode.vue";

    const props = defineProps({channel: Object});
    const {configGroup, displayConfigGroup, configGroupChevron} = useConfigGroups();
</script>
