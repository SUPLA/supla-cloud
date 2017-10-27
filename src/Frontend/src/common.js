import "expose-loader?jQuery!expose-loader?$!jquery";
import "expose-loader?moment!moment";
import "bootstrap";
import "bootstrap/dist/css/bootstrap.css";
import Vue from "vue";
import Vuex from "vuex";
import VueI18N from "vue-i18n";
import VueMoment from "vue-moment";
import VueResource from "vue-resource";
import ResponseErrorInterceptor from "./common/response-error-interceptor";
import "moment-timezone";
import "./common/common-components";
import "./common/common-directives";
import "./common/filters";
import "style-loader!css-loader!sass-loader!./styles/styles.scss";

Vue.use(Vuex);
Vue.use(VueI18N);
Vue.use(VueMoment);
Vue.use(VueResource);

Vue.config.external = window.FRONTEND_CONFIG || {};
Vue.http.options.root = (Vue.config.external.baseUrl || '') + '/web-api';
Vue.http.headers.common['X-Accept-Version'] = 'v2';

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
    AnimatedSvg: () => import("./account-details/animated-svg.vue"),
    ClientAppsPage: () => import("./client-apps/client-apps-page.vue"),
    DevicesListPage: () => import("./devices/list/devices-list-page.vue"),
    EnableDisableDeviceButton: () => import("./devices/details/enable-disable-device-button.vue"),
    IdleLogout: () => import("./common/idle-logout.vue"),
    LanguageSelector: () => import('./login/language-selector.vue'),
    ScheduleForm: () => import("./schedules/schedule-form/schedule-form.vue"),
    ScheduleList: () => import("./schedules/schedule-list/schedule-list.vue"),
    TimezonePicker: () => import("./account-details/timezone-picker.vue"),
};

$(document).ready(() => {
    if ($('.vue-container').length) {
        const i18n = new VueI18N({
            locale: 'SUPLA_TRANSLATIONS',
            messages: {SUPLA_TRANSLATIONS},
        });
        const app = new Vue({
            el: '.vue-container',
            i18n,
            components,
        });
        Vue.http.interceptors.push(ResponseErrorInterceptor(app));
    }
});
