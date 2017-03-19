import Vue from "vue";

export const mutations = {
    setEnabled(state, enabled = true) {
        state.device.enabled = enabled;
    }
};

export const actions = {
    toggleEnabled({commit, state}) {
        return Vue.http.put(`iodev/${state.device.id}`, {enabled: !state.device.enabled})
            .then(() => commit('setEnabled', !state.device.enabled));
    }
};
