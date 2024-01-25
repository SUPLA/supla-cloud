<template>
    <div>
        <a class="d-flex accordion-header" @click="displayGroup('measurements')">
            <span class="flex-grow-1">{{ $t('Measurement settings') }}</span>
            <span>
                <fa :icon="group === 'measurements' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'measurements'">
                <ChannelParamsGeneralPurposeCommon :channel="channel" @change="$emit('change')"/>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('history')">
            <span class="flex-grow-1">{{ $t('History') }}</span>
            <span>
                <fa :icon="group === 'history' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'history'">
                <dl>
                    <dd>{{ $t('Store measurements history') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.keepHistory"
                            @input="$emit('change')"></toggler>
                    </dt>
                    <dd>{{ $t('Chart type') }}</dd>
                    <dt>
                        <ChannelParamsButtonSelector
                            v-model="channel.config.chartType"
                            @input="$emit('change')"
                            :values="[{id: 'LINEAR', label: $t('Linear')}, {id: 'BAR', label: $t('Bar')}, {id: 'CANDLE', label: $t('Candle')}]"/>
                    </dt>
                </dl>
            </div>
        </transition-expand>
    </div>
</template>

<script>
    import ChannelParamsButtonSelector from "./channel-params-button-selector";
    import TransitionExpand from "../../common/gui/transition-expand";
    import ChannelParamsGeneralPurposeCommon from "@/channels/params/channel-params-general-purpose-common.vue";

    export default {
        components: {ChannelParamsGeneralPurposeCommon, TransitionExpand, ChannelParamsButtonSelector},
        props: ['channel'],
        data() {
            return {
                group: undefined,
            };
        },
        methods: {
            displayGroup(group) {
                if (this.group === group) {
                    this.group = undefined;
                } else {
                    this.group = group;
                }
                this.$emit('changeGroup', group);
            },
        },
    };
</script>
