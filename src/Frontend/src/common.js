import "expose-loader?jQuery!expose-loader?$!jquery";
import "expose-loader?moment!moment";
import "bootstrap";
import "pixeden-stroke-7-icon/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css";
import "bootstrap/dist/css/bootstrap.min.css";
import Vue from "vue";
import Vuex from "vuex";
import VueI18N from "vue-i18n";
import VueRouter from "vue-router";
import VueLocalStorage from 'vue-localstorage';
import VueMoment from "vue-moment";
import VueResource from "vue-resource";
import vMediaQuery from 'v-media-query';
import ResponseErrorInterceptor from "./common/http/response-error-interceptor";
import * as requestTransformers from "./common/http/transformers";
import "moment-timezone";
import "./common/common-components";
import "./common/common-directives";
import "./common/filters";
import "style-loader!css-loader!sass-loader!./styles/styles.scss";
import routes from "./routes";
import "./polyfills";
import {CurrentUser} from "./login/current-user";

Vue.use(Vuex);
Vue.use(VueI18N);
Vue.use(VueMoment);
Vue.use(VueResource);
Vue.use(VueRouter);
Vue.use(VueLocalStorage);
Vue.use(vMediaQuery, {variables: {xs: 768}});

Vue.config.external = window.FRONTEND_CONFIG || {};
if (!Vue.config.external.baseUrl) {
    Vue.config.external.baseUrl = '';
}
Vue.http.options.root = Vue.config.external.baseUrl + '/api';
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

Vue.http.headers.common['Authorization'] = 'Bearer ' + localStorage.getItem('id_token');
Vue.prototype.$user = new CurrentUser();
Vue.prototype.$user.refreshUser().then(() => {
    $(document).ready(() => {
        if ($('.vue-container').length) {
            const router = new VueRouter({
                routes,
                base: Vue.config.external.baseUrl + '/',
                linkActiveClass: 'active',
                mode: 'history',
            });

            if (Vue.prototype.$user.username && !Vue.prototype.$user.userData.agreements.rules) {
                router.beforeEach((to, from, next) => {
                    if (!Vue.prototype.$user.userData.agreements.rules && to.name != 'agree-on-rules') {
                        next({name: 'agree-on-rules'});
                    } else {
                        next();
                    }
                });
            }

            router.beforeEach((to, from, next) => {
                if (!Vue.prototype.$user.username && !to.meta.unrestricted) {
                    next({name: 'login'});
                } else if (Vue.prototype.$user.username && to.meta.onlyUnauthenticated) {
                    next('/');
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

            const app = new Vue({
                data: {changingRoute: false},
                i18n,
                router,
                mounted() {
                    document.getElementById('page-preloader').remove();
                }
            }).$mount('.vue-container');

            router.beforeEach((to, from, next) => {
                app.changingRoute = true;
                next();
            });
            router.afterEach(() => app.changingRoute = false);

            Vue.http.interceptors.push(ResponseErrorInterceptor(app));
            for (let transformer in requestTransformers) {
                Vue.http.interceptors.push(requestTransformers[transformer]);
            }
        }
    });
});
