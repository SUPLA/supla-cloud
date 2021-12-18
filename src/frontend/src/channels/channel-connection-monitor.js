import {ClientAppConnectionMonitor} from "../client-apps/client-app-connection-monitor";

class ChannelConnectionMonitor extends ClientAppConnectionMonitor {
    getStateUrl(itemsIds) {
        const endpoint = itemsIds.length > 1 ? 'channels' : 'channels/' + itemsIds[0];
        return endpoint + '?include=connected';
    }
}

const channelConnectionMonitor = new ChannelConnectionMonitor();

export default channelConnectionMonitor;
