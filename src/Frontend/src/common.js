import "expose-loader?jQuery!expose-loader?$!jquery";
import "expose-loader?moment!moment";
import "bootstrap";
import "bootstrap/dist/css/bootstrap.css";
import "./common/turn-off-tv-logout-animation";
import Vue from "vue";
import Vuex from "vuex";
import VueI18N from "vue-i18n";
import VueMoment from "vue-moment";
import VueResource from "vue-resource";
import RespnseErrorInterceptor from "./common/response-error-interceptor";
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
Vue.http.options.root = Vue.config.external.baseUrl || '';

Vue.http.interceptors.push(RespnseErrorInterceptor);

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
    ClientAppsPage: () => import("./client-apps/client-apps-page.vue"),
    DevicesListPage: () => import("./devices/list/devices-list-page.vue"),
    EnableDisableDeviceButton: () => import("./devices/details/enable-disable-device-button.vue"),
    IdleLogout: () => import("./common/idle-logout.vue"),
    ScheduleForm: () => import("./schedules/schedule-form/schedule-form.vue"),
    ScheduleList: () => import("./schedules/schedule-list/schedule-list.vue"),
    TimezonePicker: () => import("./user-account/timezone-picker.vue"),
};

$(document).ready(() => {
    const i18n = new VueI18N({
        locale: 'SUPLA_TRANSLATIONS',
        messages: {SUPLA_TRANSLATIONS},
    });
    new Vue({
        el: '.main-content',
        i18n,
        components,
    });
});
