import "expose-loader?jQuery!expose-loader?$!jquery";
import "expose-loader?moment!moment";
import Vue from "vue";
import Vuex from "vuex";
import VueI18N from "vue-i18n";
import VueResource from "vue-resource";
import "moment-timezone";
import "style-loader!css-loader!sass-loader!./styles/styles.scss";
import "./common/common-components";
import "./common/filters";

Vue.use(Vuex);
Vue.use(VueI18N);
Vue.use(VueResource);

Vue.config.external = window.FRONTEND_CONFIG || {};
Vue.http.options.root = Vue.config.external.baseUrl || '';

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
