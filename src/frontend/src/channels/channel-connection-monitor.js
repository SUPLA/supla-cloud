import {ClientAppConnectionMonitor} from "../client-apps/client-app-connection-monitor";

class ChannelConnectionMonitor extends ClientAppConnectionMonitor {
    updateKnownStates(channelIds) {
        const endpoint = channelIds.length > 1 ? 'channels' : 'channels/' + channelIds[0];
        return this.$http().get(endpoint + '?include=connected', {skipErrorHandler: true}).then(response => {
            let channelStates = response.body;
            if (!Array.isArray(channelStates)) {
                channelStates = [channelStates];
            }
            for (let channelId of channelIds) {
                const state = channelStates.find(channel => channel.id == channelId);
                if (state) {
                    const connected = state.connected;
                    if (this.knownStates[channelId] !== connected) {
                        this.knownStates[channelId] = connected;
                        for (let callback of (this.callbacks[+channelId] || [])) {
                            callback(connected);
                        }
                    }
                }
            }
        });
    }
}

const channelConnectionMonitor = new ChannelConnectionMonitor();

export default channelConnectionMonitor;
