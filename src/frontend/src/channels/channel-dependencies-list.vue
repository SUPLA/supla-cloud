<template>
    <div class="details-page-block" v-if="dependencies.length > 0">
        <h3 class="text-center">{{ $t('Dependencies') }}</h3>
        <div class="pt-3">
            <ul class="list-group m-0">
                <li v-for="dep in dependencies" :key="dep.id" class="list-group-item">
                    <router-link :to="{name: 'channel', params: {id: dep.channel.id}}">
                        {{ channelTitle(dep.channel) }}
                    </router-link>
                    {{ ' ' }}
                    <span :class="['small text-muted', {'hidden': !dependencyLabels[dep.role]}]">
                        {{ $t(dependencyLabels[dep.role] || dep.role).toLowerCase() }}
                    </span>
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
        masterThermostatChannelId: 'Master thermostat', // i18n
        mainThermometerChannelId: 'Main thermometer', // i18n
        auxThermometerChannelId: 'Aux thermometer', // i18n
        binarySensorChannelId: 'Binary sensor', // i18n
        pumpSwitchChannelId: 'Pump switch', // i18n
        openingSensorChannelId: 'Opening sensor', // i18n
        openingSensorSecondaryChannelId: 'Partial opening sensor', // i18n
        heatOrColdSourceSwitchChannelId: 'Heat or cold source switch', // i18n
    };

    const channelsStore = useChannelsStore();

    const dependencies = computed(() => {
        const deps = {};

        // own dependencies
        Object.keys(props.channel.config)
            .filter((key) => key.endsWith('ChannelId'))
            .filter((key) => props.channel.config[key] > 0)
            .map((role) => ({
                id: `ch_${role}_${props.channel.config[role]}`,
                role,
                channel: channelsStore.all[props.channel.config[role]],
            }))
            .forEach((dep) => deps[dep.id] = dep);

        // channels that refer to this one
        Object.values(channelsStore.all).forEach((channel) => {
            Object.keys(channel.config)
                .filter((key) => key.endsWith('ChannelId'))
                .filter((key) => channel.config[key] === props.channel.id)
                .map((role) => ({
                    id: `ch_${role}_${channel.id}`,
                    role,
                    channel,
                }))
                .forEach((dep) => deps[dep.id] = dep);
        });

        return Object.values(deps);
    });
</script>
