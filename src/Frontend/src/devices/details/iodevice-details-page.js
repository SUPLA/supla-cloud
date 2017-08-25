import Vue from "vue";
import Vuex from "vuex";
import {actions, mutations} from "./iodevice-details-store";
import EnableDisableButton from "./enable-disable-button.vue";
import {i18n} from "../../translations";

new Vue({
    el: '#iodevice-detail',
    i18n,
    store: new Vuex.Store({
        state: {
            device: window.DEVICE
        },
        mutations: mutations,
        actions: actions,
        strict: process.env.NODE_ENV !== 'production'
    }),
    components: {EnableDisableButton}
});
