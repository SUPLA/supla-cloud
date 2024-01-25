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
                            :values="[{id: 'LINEAR', label: $t('Linear')}, {id: 'BAR', label: $t('Bar')}]"/>
                    </dt>
                    <dd>{{ $t('Include value added in history') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.includeValueAddedInHistory"
                            @input="$emit('change')"></toggler>
                    </dt>
                    <dd>{{ $t('Fill missing data') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.fillMissingData"
                            @input="$emit('change')"></toggler>
                    </dt>
                    <dd>{{ $t('Allow counter reset') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.allowCounterReset"
                            @input="$emit('change')"></toggler>
                    </dt>
                    <dd>{{ $t('Always decrement') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.alwaysDecrement"
                            @input="$emit('change')"></toggler>
                    </dt>
                </dl>
            </div>
        </transition-expand>
    </div>
</template>

<script>
    import TransitionExpand from "../../common/gui/transition-expand";
    import ChannelParamsGeneralPurposeCommon from "@/channels/params/channel-params-general-purpose-common.vue";
    import ChannelParamsButtonSelector from "@/channels/params/channel-params-button-selector.vue";

    export default {
        components: {ChannelParamsButtonSelector, ChannelParamsGeneralPurposeCommon, TransitionExpand},
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
            },
        },
    };
</script>
