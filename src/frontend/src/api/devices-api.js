import {api} from "@/api/api";

export const devicesApi = {
    async getList() {
        const {body} = await api.get('iodevices?include=connected');
        return body;
    },
}
