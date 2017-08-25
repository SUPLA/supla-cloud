import Vue from "vue";
import DevicesListPage from "./devices-list-page.vue";
import {i18n} from "src/translations";
import VueMoment from "vue-moment";

Vue.use(VueMoment);

new Vue({
    el: '#devices-list',
    i18n,
    render: h => h(DevicesListPage)
});
