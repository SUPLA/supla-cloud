import {ClientAppConnectionMonitor} from "../client-apps/client-app-connection-monitor";

class DeviceConnectionMonitor extends ClientAppConnectionMonitor {
    updateKnownStates(deviceIds) {
        return this.$http().post('ajax/serverctrl-connstate', {devids: deviceIds}).then(({body}) => {
            if (body.states) {
                for (let deviceId of deviceIds) {
                    const state = body.states[+deviceId];
                    if (state) {
                        const connected = !!state.state;
                        if (this.knownStates[deviceId] !== connected) {
                            this.knownStates[deviceId] = connected;
                            for (let callback of (this.callbacks[+deviceId] || [])) {
                                callback(connected);
                            }
                        }
                    }
                }
            }
        });
    }
}

const deviceConnectionMonitor = new DeviceConnectionMonitor();

export default deviceConnectionMonitor;
