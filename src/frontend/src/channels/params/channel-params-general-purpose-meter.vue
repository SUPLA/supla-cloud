<template>
    <div>
        <ChannelParamsGeneralPurposeMeasurement :channel="channel" @change="$emit('change')" @changeGroup="(g) => displayGroup(g)"
            :current-group="group"/>
        <a class="d-flex accordion-header" @click="displayGroup('other')">
            <span class="flex-grow-1">{{ $t('Other') }}</span>
            <span>
                <fa :icon="group === 'other' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'other'">
                <dl>
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
    import ChannelParamsGeneralPurposeMeasurement from "@/channels/params/channel-params-general-purpose-measurement.vue";

    export default {
        components: {ChannelParamsGeneralPurposeMeasurement, TransitionExpand},
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
