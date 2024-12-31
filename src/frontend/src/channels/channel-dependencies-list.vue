<template>
    <div class="details-page-block" v-if="dependencies.length > 0">
        <h3 class="text-center">{{ $t('Dependencies') }}</h3>
        <div class="pt-3">
            <ul class="list-group m-0">
                <li v-for="dep in dependencies" :key="dep.id" class="list-group-item">
                    <div class="small text-muted">{{ $t(dep.label) }}</div>
                    <router-link :to="{name: 'channel', params: {id: dep.channel.id}}">
                        {{ channelTitle(dep.channel) }}
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

    const props = defineProps({
        channel: Object,
    });

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
        heatOrColdSourceSwitchChannelId: 'Heat or cold source switch', // i18n
        relatedRelayChannelId_src: 'Associated measured channel', // i18n
        relatedRelayChannelId_dest: 'Associated measurement channel', // i18n
        relatedChannelId_src: 'Action trigger', // i18n
        relatedChannelId_dest: 'Action trigger for channel', // i18n
        levelSensor_src: 'Level sensor', // i18n
        levelSensor_dest: 'Level sensor for container', // i18n
    };

    const hiddenRelations = ['relatedMeterChannelId'];

    const dependencyLabel = (role, direction) => dependencyLabels[`${role}_${direction}`] || dependencyLabels[role] || '';

    const channelsStore = useChannelsStore();

    const dependencies = computed(() => {
        const deps = {};

        // own dependencies
        Object.keys(props.channel.config)
            .filter((key) => key.endsWith('ChannelId'))
            .filter((key) => !hiddenRelations.includes(key))
            .filter((key) => props.channel.config[key] > 0)
            .map((role) => ({
                id: `ch_${role}_${props.channel.config[role]}`,
                role,
                label: dependencyLabel(role, 'src'),
                channel: channelsStore.all[props.channel.config[role]],
            }))
            .forEach((dep) => deps[dep.id] = dep);
        Object.keys(props.channel.config.sensors || {})
            .map((channelId) => ({
                id: `ch_levelSensor_${channelId}`,
                role: 'levelSensor',
                label: dependencyLabel('levelSensor', 'src'),
                channel: channelsStore.all[channelId],
            }))
            .forEach((dep) => deps[dep.id] = dep);

        // channels that refer to this one
        Object.values(channelsStore.all).forEach((channel) => {
            Object.keys(channel.config)
                .filter((key) => key.endsWith('ChannelId'))
                .filter((key) => !hiddenRelations.includes(key))
                .filter((key) => channel.config[key] === props.channel.id)
                .map((role) => ({
                    id: `ch_${role}_${channel.id}`,
                    role,
                    label: dependencyLabel(role, 'dest'),
                    channel,
                }))
                .forEach((dep) => deps[dep.id] = dep);
            Object.keys(channel.config.sensors || {})
                .filter((key) => key == props.channel.id)
                .map(() => ({
                    id: `ch_levelSensor_${channel.id}`,
                    role: 'levelSensor',
                    label: dependencyLabel('levelSensor', 'dest'),
                    channel: channel,
                }))
                .forEach((dep) => deps[dep.id] = dep);
        });

        return Object.values(deps);
    });
</script>
