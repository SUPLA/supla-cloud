import "expose-loader?jQuery!expose-loader?$!jquery";
import "expose-loader?moment!moment";
import "bootstrap";
import "bootstrap/dist/css/bootstrap.min.css";
import Vue from "vue";
import Vuex from "vuex";
import VueI18N from "vue-i18n";
import VueRouter from "vue-router";
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
import routes from "./routes";

Vue.use(Vuex);
Vue.use(VueI18N);
Vue.use(VueMoment);
Vue.use(VueResource);
Vue.use(VueRouter);
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

$(document).ready(() => {
    if ($('.vue-container').length) {
        const router = new VueRouter({
            routes,
            base: Vue.config.external.baseUrl + '/',
            linkActiveClass: 'active',
            mode: 'history',
        });

        router.beforeEach((to, from, next) => {
            if (to.name != 'agree-on-rules') {
                next({name: 'agree-on-rules'});
            } else {
                next();
            }
        });

        router.afterEach((to) => {
            if (to.meta.bodyClass) {
                document.body.setAttribute('class', to.meta.bodyClass);
            } else {
                document.body.removeAttribute('class');
            }
        });

        router.afterEach(() => {
            $(".navbar-toggle:visible:not('.collapsed')").click();
        });

        const i18n = new VueI18N({
            locale: 'SUPLA_TRANSLATIONS',
            messages: {SUPLA_TRANSLATIONS}
        });
        Vue.prototype.$user = Vue.config.external.user;
        const app = new Vue({
            el: '.vue-container',
            i18n,
            router,
        });
        Vue.http.interceptors.push(ResponseErrorInterceptor(app));
        for (let transformer in requestTransformers) {
            Vue.http.interceptors.push(requestTransformers[transformer]);
        }
    }
});
