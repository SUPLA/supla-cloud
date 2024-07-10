<template>
    <div>
        <ChannelParamsElectricityMeterEnabledPhases :channel="channel" @change="$emit('change')"/>
        <dl>
            <dd>{{ $t('Associated measured channel') }}</dd>
            <dt>
                <channels-id-dropdown params="function=POWERSWITCH,LIGHTSWITCH,STAIRCASETIMER"
                    v-model="channel.config.relatedChannelId"
                    @input="$emit('change')"/>
            </dt>
        </dl>
        <a class="d-flex accordion-header" @click="displayGroup('costs')">
            <span class="flex-grow-1">{{ $t('Costs') }}</span>
            <span>
                <fa :icon="group === 'costs' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'costs'">
                <channel-params-meter-cost :channel="channel" unit="kWh" @change="$emit('change')"/>
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
                <ChannelParamsMeterKeepHistoryMode v-model="channel.config.voltageLoggerEnabled" @input="$emit('change')"
                    label-i18n="Store voltage history"/>
                <ChannelParamsMeterKeepHistoryMode v-model="channel.config.currentLoggerEnabled" @input="$emit('change')"
                    label-i18n="Store current history"/>
                <ChannelParamsMeterKeepHistoryMode v-model="channel.config.powerActiveLoggerEnabled" @input="$emit('change')"
                    label-i18n="Store active power history"/>
                <channel-params-electricity-meter-initial-values :channel="channel" @save="$emit('save')"/>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('voltageMonitoring')">
            <span class="flex-grow-1">{{ $t('Voltage monitoring') }}</span>
            <span>
                <fa :icon="group === 'voltageMonitoring' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'voltageMonitoring'">
                <ChannelParamsElectricityMeterVoltageThresholds :channel="channel" @change="$emit('change')"/>
            </div>
        </transition-expand>
        <channel-params-meter-reset :channel="channel" class="mt-4"/>
        <a class="d-flex accordion-header" @click="displayGroup('other')">
            <span class="flex-grow-1">{{ $t('Other') }}</span>
            <span>
                <fa :icon="group === 'other' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'other'">
                <dl v-if="channel.config.availableCTTypes && channel.config.availableCTTypes.length > 0">
                    <dd>
                        {{ $t('Used current transformer type') }}
                        <!--                        <a @click="algorithmHelpShown = !algorithmHelpShown"><i class="pe-7s-help1"></i></a>-->
                    </dd>
                    <dt>
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button" data-toggle="dropdown">
                                <span v-if="channel.config.usedCTType">{{ channel.config.usedCTType.replace(/_/g, ' ') }}</span>
                                <span v-else>?</span>
                                <span class="caret ml-2"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li v-for="type in channel.config.availableCTTypes" :key="type">
                                    <a @click="channel.config.usedCTType = type; $emit('change')"
                                        v-show="type !== channel.config.usedCTType">
                                        {{ type.replace(/_/g, ' ') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </dt>
                </dl>
                <dl v-if="channel.config.availablePhaseLedTypes && channel.config.availablePhaseLedTypes.length > 0">
                    <dd>
                        {{ $t('Phase LED type') }}
                        <!--                        <a @click="algorithmHelpShown = !algorithmHelpShown"><i class="pe-7s-help1"></i></a>-->
                    </dd>
                    <dt>
                        <div class="dropdown">
                            <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button" data-toggle="dropdown">
                                <span>{{ $t(`usedPhaseLedType_${channel.config.usedPhaseLedType}`) }}</span>
                                <span class="caret ml-2"></span>
                            </button>
                            <!-- i18n:['usedCTType_', 'thermostatAlgorithm_ON_OFF_SETPOINT_AT_MOST'] -->
                            <ul class="dropdown-menu">
                                <li v-for="type in channel.config.availablePhaseLedTypes" :key="type">
                                    <a @click="channel.config.usedPhaseLedType = type; $emit('change')"
                                        v-show="type !== channel.config.usedPhaseLedType">
                                        {{ $t(`usedPhaseLedType_${type}`) }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </dt>
                </dl>
            </div>
        </transition-expand>
    </div>
</template>

<script setup>
    import ChannelParamsMeterCost from "./channel-params-meter-cost";
    import ChannelsIdDropdown from "@/devices/channels-id-dropdown";
    import ChannelParamsMeterReset from "@/channels/params/channel-params-meter-reset";
    import ChannelParamsElectricityMeterInitialValues from "@/channels/params/channel-params-electricity-meter-initial-values";
    import ChannelParamsElectricityMeterVoltageThresholds from "@/channels/params/channel-params-electricity-meter-voltage-thresholds";
    import ChannelParamsElectricityMeterEnabledPhases from "@/channels/params/channel-params-electricity-meter-enabled-phases";
    import ChannelParamsMeterKeepHistoryMode from "@/channels/params/channel-params-meter-keep-history-mode.vue";
    import {ref} from "vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    defineProps({channel: Object});

    const group = ref(undefined);

    function displayGroup(groupName) {
        if (group.value === groupName) {
            group.value = undefined;
        } else {
            group.value = groupName;
        }
    }
</script>
