import {defineStore} from "pinia";
import {computed, ref} from "vue";
import {channelsApi} from "@/api/channels-api";
import {useIntervalFn} from "@vueuse/core";
import {useDevicesStore} from "@/stores/devices-store";
import ChannelType from "@/common/enums/channel-type";
import ChannelFunction from "@/common/enums/channel-function";

export const useChannelsStore = defineStore('channels', () => {
    const all = ref({});
    const ids = ref([]);
    const fetchingStates = useIntervalFn(() => fetchStates(), 7777, {immediate: false});

    const fetchAll = (force = false) => {
        if (fetchAll.promise && !force) {
            return fetchAll.promise;
        } else {
            return fetchAll.promise = channelsApi.getListWithState().then((channels) => {
                const state = channels.reduce((acc, curr) => {
                    curr.connected = curr.state.connected;
                    return {
                        ids: acc.ids.concat(curr.id),
                        all: {...acc.all, [curr.id]: curr}
                    }
                }, {ids: [], all: {}});
                all.value = state.all;
                ids.value = state.ids;
                if (!fetchingStates.isActive.value) {
                    fetchingStates.resume();
                }
            })
        }
    };

    const updateChannel = (channel) => {
        all.value[channel.id] = {...all.value[channel.id], ...channel};
    }

    const fetchChannel = (channelId) => {
        return channelsApi.getOneWithState(channelId)
            .then((channel) => {
                updateChannel(channel);
                return channel;
            });
    };

    const fetchStates = () => {
        if (!fetchStates.promise) {
            fetchStates.promise = channelsApi.getStates().then((response) => {
                let refetch = false;
                const idsToFetch = [];
                const devicesStore = useDevicesStore();
                const {states: channelsStates, devicesCount} = response;
                devicesStore.updateConnectedStatuses(channelsStates);
                channelsStates.forEach((channel) => {
                    if (all.value[channel.id]) {
                        all.value[channel.id].connected = channel.state.connected;
                        all.value[channel.id].state = channel.state;
                        if (all.value[channel.id].checksum !== channel.checksum) {
                            idsToFetch.push(channel.id);
                        }
                    } else {
                        refetch = true;
                    }
                });
                refetch = refetch || channelsStates.length !== ids.value.length || devicesCount !== devicesStore.ids.length;
                if (refetch || idsToFetch.length > 5) {
                    refetchAll();
                } else if (idsToFetch.length > 0) {
                    idsToFetch.forEach((id) => fetchChannel(id));
                }
            }).finally(() => fetchStates.promise = undefined);
        }
        return fetchStates.promise;
    };

    const refetchAll = () => {
        const devicesStore = useDevicesStore();
        devicesStore.fetchAll(true);
        return fetchAll(true);
    }

    const list = computed(() => ids.value.map(id => all.value[id]));

    const filteredChannels = computed(() => ((filters) => {
        const params = typeof filters === 'object' ? filters : Object.fromEntries(new URLSearchParams(filters));
        params.deviceIds = ('' + (params.deviceIds || '')).split(',').filter(id => !!id).map((id) => +id);
        params.skipIds = ('' + (params.skipIds || '')).split(',').filter(id => !!id).map((id) => +id);
        params.type = ('' + (params.type || '')).split(',').filter(t => !!t).map(t => ChannelType[t] || +t);
        params.function = ('' + (params.function || params.fnc || '')).split(',').filter(t => !!t).map(t => ChannelFunction[t] || +t);
        if (params.hasFunction !== undefined) {
            params.hasFunction = params.hasFunction === '0' ? false : !!params.hasFunction;
        }
        return list.value
            .filter((c) => params.type.length === 0 || params.type.includes(c.typeId))
            .filter((c) => params.function.length === 0 || params.function.includes(c.functionId))
            .filter((c) => params.deviceIds.length === 0 || params.deviceIds.includes(c.iodeviceId))
            .filter((c) => params.hasFunction === undefined || c.functionId !== ChannelFunction.NONE)
            .filter((c) => params.io === undefined || c.function.output === (params.io === 'output'))
            .filter((c) => !params.skipIds.includes(c.id));
    }));

    const $reset = () => {
        fetchingStates.pause();
        all.value = {};
        ids.value = [];
        fetchAll.promise = undefined;
    };

    return {all, ids, list, filteredChannels, $reset, fetchAll, refetchAll, fetchStates, fetchChannel};
});
