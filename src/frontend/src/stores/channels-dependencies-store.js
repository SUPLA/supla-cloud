import {defineStore} from "pinia";
import {useChannelsStore} from "@/stores/channels-store";
import {computed} from "vue";

const hiddenRelations = ['relatedMeterChannelId'];

export const useChannelsDependenciesStore = defineStore('channelsDependencies', () => {
    const channelsStore = useChannelsStore();

    const dependencies = computed(() => {
        return Object.values(channelsStore.list.reduce((acc, channel) => {
            Object.keys(channel.config)
                .filter((key) => key.endsWith('ChannelId'))
                .filter((key) => !hiddenRelations.includes(key))
                .filter((key) => channel.config[key] > 0)
                .map((role) => ({
                    id: `ch_${role}_${Math.min(channel.config[role], channel.id)}_${Math.max(channel.config[role], channel.id)}`,
                    role,
                    channel1Id: channel.id,
                    channel2Id: channel.config[role],
                }))
                .forEach((dep) => acc[dep.id] = dep);
            Object.keys(channel.config)
                .filter((key) => key.endsWith('ChannelIds'))
                .filter((key) => !hiddenRelations.includes(key))
                .filter((key) => channel.config[key]?.length > 0)
                .map((role) => channel.config[role].map((channelId) => ({
                    id: `ch_${role}_${Math.min(channelId, channel.id)}_${Math.max(channelId, channel.id)}`,
                    role,
                    channel1Id: channel.id,
                    channel2Id: channelId,
                })))
                .forEach((depList) => depList.forEach((dep) => acc[dep.id] = dep));
            return acc;
        }, {}));
    });

    const forChannel = computed(() => ((channelId) => dependencies.value
            .filter((dep) => dep.channel1Id === channelId || dep.channel2Id === channelId)
            .map((dep) => ({
                id: dep.id,
                role: dep.role,
                channelId: dep.channel1Id === channelId ? dep.channel2Id : dep.channel1Id,
            }))
    ))

    const $reset = () => {
    };

    return {dependencies, forChannel, $reset};
});
