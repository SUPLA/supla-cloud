import Vue from "vue";
import EventBus from "../common/event-bus";

export class ClientAppConnectionMonitor {
    constructor() {
        this.vue = undefined;
        this.fetching = false;
        this.knownStates = {};
        this.callbacks = {};
    }

    register(clientApp, callback) {
        const clientAppId = +((clientApp && clientApp.id) || clientApp);
        if (!this.callbacks[clientAppId]) {
            this.callbacks[clientAppId] = [];
        }
        this.callbacks[clientAppId].push(callback);
        if (this.knownStates[clientAppId] !== undefined) {
            callback(this.knownStates[clientAppId]);
        } else {
            Vue.nextTick(() => this.fetchStates().then(() => callback(this.knownStates[clientAppId])));
        }
    }

    unregister(clientApp, callback) {
        const clientAppId = +((clientApp && clientApp.id) || clientApp);
        if (this.callbacks[clientAppId]) {
            const newCallbacks = this.callbacks[clientAppId].filter(c => c !== callback);
            if (newCallbacks.length) {
                this.callbacks[clientAppId] = newCallbacks;
            } else {
                delete this.callbacks[clientAppId];
            }
        }
    }

    fetchStates() {
        if (this.fetching) {
            return this.fetching;
        }
        const clientAppIds = Object.keys(this.callbacks);
        if (clientAppIds.length) {
            return this.fetching = this._updateKnownStates(clientAppIds.map((id) => +id))
                .then(() => this.knownStates)
                .finally(() => this.fetching = undefined);
        }
    }

    _updateKnownStates(itemIds) {
        return this.fetching = this.$http().get(this.getStateUrl(itemIds), {skipErrorHandler: true}).then((response) => {
            const states = Array.isArray(response.body) ? response.body : [response.body];
            for (let itemId of itemIds) {
                const state = states.find(item => item.id == itemId);
                if (state) {
                    const connected = state.connected;
                    if (this.knownStates[itemId] !== connected) {
                        this.knownStates[itemId] = connected;
                        for (let callback of (this.callbacks[+itemId] || [])) {
                            callback(connected);
                        }
                    }
                }
            }
            this.updateTotalCount(response);
        });
    }

    // eslint-disable-next-line no-unused-vars
    getStateUrl(itemsIds) {
        return 'client-apps?include=connected';
    }

    updateTotalCount(response) {
        let totalCount = response.headers.get('X-Total-Count');
        if (totalCount !== undefined) {
            if (this.totalCount !== undefined && this.totalCount !== totalCount) {
                EventBus.$emit('total-count-changed');
            }
            this.totalCount = totalCount;
        }
    }

    $http() {
        if (!this.vue) {
            setInterval(() => this.fetchStates(), 7000);
            this.vue = new Vue();
        }
        return this.vue.$http;
    }

}

const clientAppConnectionMonitor = new ClientAppConnectionMonitor();

export default clientAppConnectionMonitor;
