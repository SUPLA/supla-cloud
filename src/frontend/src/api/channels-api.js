import {api} from "@/api/api";
import {deepCopy} from "@/common/utils";

export const channelsApi = {
    async getList() {
        const {body} = await api.get('channels');
        return body;
    },
    async getListWithState() {
        const {body: channels} = await api.get('channels?include=state,connected');
        channels.forEach(channel => {
            if (channel.config) {
                channel.configBefore = deepCopy(channel.config);
            }
        });
        return channels;
    },
    async getOneWithState(id) {
        const {body: channel} = await api.get(`channels/${id}?include=state,connected`);
        if (channel.config) {
            channel.configBefore = deepCopy(channel.config);
        }
        return channel;
    },
    async getStates() {
        const {body} = await api.get('channels/states');
        return body;
    },
    async muteAlarm(channel) {
        return await api.patch(`channels/${channel.id}/settings`, {action: 'muteAlarm'});
    },
}
