import {api} from "@/api/api";

export const devicesApi = {
    async getList() {
        const {body} = await api.get('iodevices?include=connected');
        return body;
    },
    async getOne(deviceId, include = 'connected', config = {}) {
        const {body} = await api.get(`iodevices/${deviceId}?include=${include}`, config);
        return body;
    },
    async remove(deviceId, safe = true) {
        return await api.delete_(`iodevices/${deviceId}?safe=${safe ? '1' : '0'}`, {skipErrorHandler: [409]})
    },
    async update(deviceId, newSettings, safe = true) {
        return await api.put(`iodevices/${deviceId}?safe=${safe ? '1' : '0'}`, newSettings, {skipErrorHandler: [409]})
    },
    async otaCheckUpdates(deviceId) {
        return await api.patch(`iodevices/${deviceId}`, {action: 'otaCheckUpdates'});
    },
    async otaPerformUpdate(deviceId) {
        return await api.patch(`iodevices/${deviceId}`, {action: 'otaPerformUpdate'});
    },
    async factoryReset(deviceId) {
        return await api.patch(`iodevices/${deviceId}`, {action: 'factoryReset'});
    },
    async setCfgModePassword(deviceId, password) {
        return await api.patch(`iodevices/${deviceId}`, {action: 'setCfgModePassword', password});
    },
    async pairSubdevice(deviceId) {
        return await api.patch(`iodevices/${deviceId}`, {action: 'pairSubdevice'});
    },
}
