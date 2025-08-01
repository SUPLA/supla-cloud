import {api} from "@/api/api";

export const devicesApi = {
    async getList() {
        const {body} = await api.get('iodevices?include=connected');
        return body;
    },
    async getOne(deviceId) {
        const {body} = await api.get(`iodevices/${deviceId}?include=connected`);
        return body;
    },
    async remove(deviceId, safe = true) {
        return await api.delete_(`iodevices/${deviceId}?safe=${safe ? '1' : '0'}`, {skipErrorHandler: [409]})
    },
    async otaCheckUpdates(deviceId) {
        return await api.patch(`iodevices/${deviceId}`, {action: 'otaCheckUpdates'});
    },
    async otaPerformUpdate(deviceId) {
        return await api.patch(`iodevices/${deviceId}`, {action: 'otaPerformUpdate'});
    },
}
