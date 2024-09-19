import {api} from "@/api/api";

export const channelsApi = {
    async getList() {
        const {body} = await api.get('channels');
        return body;
    },
    async getListWithState() {
        const {body} = await api.get('channels?include=state,connected');
        return body;
    },
    async getStates() {
        const {body} = await api.get('channels/states');
        return body;
    },
}
