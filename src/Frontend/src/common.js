import "expose-loader?jQuery!expose-loader?$!jquery";
import "expose-loader?moment!moment";
import "bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import Vue from "vue";
import Vuex from "vuex";
import VueI18N from "vue-i18n";
import VueLocalStorage from 'vue-localstorage';
import VueMoment from "vue-moment";
import VueResource from "vue-resource";
import ResponseErrorInterceptor from "./common/http/response-error-interceptor";
import * as requestTransformers from "./common/http/transformers";
import "moment-timezone";
import "./common/common-components";
import "./common/common-directives";
import "./common/filters";
import "style-loader!css-loader!sass-loader!./styles/styles.scss";

Vue.use(Vuex);
Vue.use(VueI18N);
Vue.use(VueMoment);
Vue.use(VueResource);
Vue.use(VueLocalStorage);

Vue.config.external = window.FRONTEND_CONFIG || {};
if (!Vue.config.external.baseUrl) {
    Vue.config.external.baseUrl = '';
}
Vue.http.options.root = Vue.config.external.baseUrl + '/web-api';
Vue.http.headers.common['X-Accept-Version'] = '2.2.0';

moment.locale(Vue.config.external.locale);
moment.tz.setDefault(Vue.config.external.timezone);

// synchronize browser time with server's
(function () {
    const serverTime = new Date(window.FRONTEND_CONFIG.serverTime);
    const renderStart = window.FRONTEND_CONFIG.renderStart;
    const offset = serverTime.getTime() - renderStart.getTime();
    moment.now = function () {
        return Date.now() + offset;
    };
})();

const components = {
    AccountPage: () => import("./account-details/account-page"),
    AccessIdsPage: () => import("./access-ids/access-ids-page.vue"),
    ApiSettingsPage: () => import("./account-details/api-settings-page"),
    ChannelDetailsPage: () => import("./channels/channel-details-page.vue"),
    ChannelGroupsPage: () => import("./channel-groups/channel-groups-page.vue"),
    ClientAppsPage: () => import("./client-apps/client-apps-page.vue"),
    MySuplaPage: () => import("./home/my-supla-page"),
    DeviceDetailsPage: () => import("./devices/details/device-details-page"),
    IdleLogout: () => import("./common/idle-logout.vue"),
    LanguageSelector: () => import('./login/language-selector.vue'),
    LocationsPage: () => import("./locations/locations-page.vue"),
    ScheduleForm: () => import("./schedules/schedule-form/schedule-form.vue"),
    ScheduleDetailsPage: () => import("./schedules/schedule-details/schedule-details-page"),
    ScheduleListPage: () => import("./schedules/schedule-list/schedule-list-page.vue"),
};

$(document).ready(() => {
    if ($('.vue-container').length) {
        // console.log()
        const i18n = new VueI18N({
            locale: 'SUPLA_TRANSLATIONS',
            messages: {SUPLA_TRANSLATIONS}
        });
        const app = new Vue({
            el: '.vue-container',
            i18n,
            components,
        });
        Vue.http.interceptors.push(ResponseErrorInterceptor(app));
        for (let transformer in requestTransformers) {
            Vue.http.interceptors.push(requestTransformers[transformer]);
        }
    }
});
