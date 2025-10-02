import {api} from "@/api/api";

export const subDevicesApi = {
    async getList() {
        const {body} = await api.get('subdevices');
        return body;
    },
    async identify(channel) {
        return await api.patch(`channels/${channel.id}/subdevice`, {action: 'identify'});
    },
    async restart(channel) {
        return await api.patch(`channels/${channel.id}/subdevice`, {action: 'restart'});
    },
}
