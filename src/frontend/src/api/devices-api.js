import {api} from "@/api/api";

export const devicesApi = {
    async getList() {
        const {body} = await api.get('iodevices?include=connected');
        return body;
    },
    async remove(deviceId, safe = true) {
        return await api.delete_(`iodevices/${deviceId}?safe=${safe ? '1' : '0'}`, {skipErrorHandler: [409]})
    },
}
