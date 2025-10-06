<template>
    <div class="details-page-block" v-if="dependencies.length > 0">
        <h3 class="text-center">{{ $t('Dependencies') }}</h3>
        <div class="pt-3">
            <ul class="list-group m-0">
                <li v-for="dep in dependencies" :key="dep.id" class="list-group-item">
                    <div class="small text-muted">{{ $t(dependencyLabel(dep)) }}</div>
                    <router-link :to="{name: 'channel', params: {id: dep.channelId}}">
                        {{ channelTitle(channels[dep.channelId]) }}
                    </router-link>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
  import {useChannelsStore} from "@/stores/channels-store";
  import {computed} from "vue";
  import {channelTitle} from "@/common/filters";
  import {useChannelsDependenciesStore} from "@/stores/channels-dependencies-store";
  import {storeToRefs} from "pinia";
  import ChannelType from "@/common/enums/channel-type";

  const props = defineProps({
        channel: Object,
    });

    const depsStore = useChannelsDependenciesStore();
    const {all: channels} = storeToRefs(useChannelsStore());
    const dependencies = computed(() => depsStore.forChannel(props.channel.id));

    const dependencyLabels = {
        masterThermostatChannelId_src: 'Master thermostat', // i18n
        masterThermostatChannelId_dest: 'Master thermostat for channel', // i18n
        mainThermometerChannelId_src: 'Main thermometer', // i18n
        mainThermometerChannelId_dest: 'Main thermometer for channel', // i18n
        auxThermometerChannelId_src: 'Aux thermometer', // i18n
        auxThermometerChannelId_dest: 'Aux thermometer for channel', // i18n
        binarySensorChannelId_src: 'Binary sensor', // i18n
        binarySensorChannelId_dest: 'Binary sensor for channel', // i18n
        pumpSwitchChannelId_src: 'Pump switch', // i18n
        pumpSwitchChannelId_dest: 'Pump switch for channel', // i18n
        openingSensorChannelId_src: 'Opening sensor', // i18n
        openingSensorChannelId_dest: 'Opening sensor for channel', // i18n
        openingSensorSecondaryChannelId_src: 'Partial opening sensor', // i18n
        openingSensorSecondaryChannelId_dest: 'Partially opened sensor for channel', // i18n
        heatOrColdSourceSwitchChannelId_src: 'Heat or cold source switch', // i18n
        heatOrColdSourceSwitchChannelId_dest: 'Heat or cold source switch for channel', // i18n
        relatedRelayChannelId_src: 'Associated measured channel', // i18n
        relatedRelayChannelId_dest: 'Associated measurement channel', // i18n
        relatedChannelId_src: 'Action trigger', // i18n
        relatedChannelId_dest: 'Action trigger for channel', // i18n
        floodSensorChannelIds_src: 'Flood sensor', // i18n
        floodSensorChannelIds_dest: 'Flood sensor for valve', // i18n
        levelSensorChannelIds_src: 'Level sensor', // i18n
        levelSensorChannelIds_dest: 'Level sensor for container', // i18n
    };

    const dependencyLabel = (dep) => {
        let dir = 'src';
        if ([ChannelType.SENSORNO, ChannelType.THERMOMETER, ChannelType.HUMIDITYANDTEMPSENSOR, ChannelType.ACTION_TRIGGER].includes(props.channel.typeId)) {
            dir = 'dest';
        }
        if (['masterThermostatChannelId', 'pumpSwitchChannelId', 'heatOrColdSourceSwitchChannelId'].includes(dep.role) && props.channel.config[dep.role] !== dep.channelId) {
            dir = 'dest';
        }
        if ('relatedRelayChannelId' === dep.role && [ChannelType.RELAY, ChannelType.RELAY2XG5LA1A].includes(props.channel.typeId)) {
            dir = 'dest';
        }
        return dependencyLabels[`${dep.role}_${dir}`] || dependencyLabels[dep.role] || '';
    };
</script>
