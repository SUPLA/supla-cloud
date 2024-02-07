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
                <dl>
                    <dd>{{ $t('Store measurements history') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="props.channel.config.keepHistory"
                            @input="$emit('change')"></toggler>
                    </dt>
                    <dd>{{ $t('Chart type') }}</dd>
                    <dt>
                        <ChannelParamsButtonSelector
                            v-model="props.channel.config.chartType"
                            @input="$emit('change')"
                            :values="[{id: 'LINEAR', label: $t('Linear')}, {id: 'BAR', label: $t('Bar')}]"/>
                    </dt>
                    <dd>{{ $t('Fill missing data') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="props.channel.config.fillMissingData"
                            @input="$emit('change')"></toggler>
                    </dt>
                    <dd>{{ $t('Allow counter reset') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="props.channel.config.allowCounterReset"
                            @input="$emit('change')"></toggler>
                    </dt>
                    <dd>{{ $t('Always decrement') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="props.channel.config.alwaysDecrement"
                            @input="$emit('change')"></toggler>
                    </dt>
                </dl>
            </div>
        </transition-expand>
    </div>
</template>

<script setup>
    import TransitionExpand from "../../common/gui/transition-expand";
    import ChannelParamsGeneralPurposeCommon from "@/channels/params/channel-params-general-purpose-common.vue";
    import ChannelParamsButtonSelector from "@/channels/params/channel-params-button-selector.vue";
    import {useConfigGroups} from "@/channels/params/useConfigGroups";

    const props = defineProps({channel: Object});
    const {configGroup, displayConfigGroup, configGroupChevron} = useConfigGroups();
</script>
