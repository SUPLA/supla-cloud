import Vue from "vue";
import ClientAppsPage from "./client-apps-page.vue";
import {i18n} from "../translations";
import VueMoment from "vue-moment";

Vue.use(VueMoment);

new Vue({
    el: '#client-apps',
    i18n,
    render: h => h(ClientAppsPage)
});
