import {ClientAppConnectionMonitor} from "../client-apps/client-app-connection-monitor";

class DeviceConnectionMonitor extends ClientAppConnectionMonitor {
    updateKnownStates(deviceIds) {
        const endpoint = deviceIds.length > 1 ? 'iodevices' : 'iodevices/' + deviceIds[0];
        return this.$http().get(endpoint + '?include=connected').then(({body: deviceStates}) => {
            if (!Array.isArray(deviceStates)) {
                deviceStates = [deviceStates];
            }
            for (let deviceId of deviceIds) {
                const state = deviceStates.find(device => device.id == deviceId);
                if (state) {
                    const connected = state.connected;
                    if (this.knownStates[deviceId] !== connected) {
                        this.knownStates[deviceId] = connected;
                        for (let callback of (this.callbacks[+deviceId] || [])) {
                            callback(connected);
                        }
                    }
                }
            }
        });
    }
}

const deviceConnectionMonitor = new DeviceConnectionMonitor();

export default deviceConnectionMonitor;
