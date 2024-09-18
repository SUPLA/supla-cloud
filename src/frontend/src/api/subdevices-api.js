import {api} from "@/api/api";

export const subDevicesApi = {
    async getList() {
        const {body} = await api.get('subdevices');
        return body;
    },
    async identify(subDevice) {
        return await api.patch(`iodevices/${subDevice.ioDeviceId}/subdevices/${subDevice.id}`, {action: 'identify'});
    }
}
