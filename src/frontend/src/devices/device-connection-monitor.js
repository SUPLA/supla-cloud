import {ClientAppConnectionMonitor} from "../client-apps/client-app-connection-monitor";

class DeviceConnectionMonitor extends ClientAppConnectionMonitor {
    getStateUrl(itemsIds) {
        const endpoint = itemsIds.length > 1 ? 'iodevices' : 'iodevices/' + itemsIds[0];
        return endpoint + '?include=connected';
    }
}

const deviceConnectionMonitor = new DeviceConnectionMonitor();

export default deviceConnectionMonitor;
