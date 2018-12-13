import "expose-loader?jQuery!expose-loader?$!jquery";
import "expose-loader?moment!moment";
import "bootstrap";
import "pixeden-stroke-7-icon/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css";
import "bootstrap/dist/css/bootstrap.min.css";
import Vue from "vue";
import Vuex from "vuex";
import {i18n, setGuiLocale} from './locale';
import router from './router';
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
import "./polyfills";
import {CurrentUser} from "./login/current-user";

Vue.use(Vuex);
Vue.use(VueMoment);
Vue.use(VueResource);
Vue.use(VueLocalStorage);
Vue.use(vMediaQuery, {variables: {xs: 768}});

Vue.config.external = window.FRONTEND_CONFIG || {};
if (!Vue.config.external.baseUrl) {
    Vue.config.external.baseUrl = '';
}
Vue.http.options.root = Vue.config.external.baseUrl + '/api';
Vue.http.headers.common['X-Accept-Version'] = '2.3.0';

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
Vue.prototype.$user.fetchUser()
    .then((userData) => setGuiLocale(userData))
    .then(() => {
        $(document).ready(() => {
            if ($('.vue-container').length) {

                const app = new Vue({
                    data: {changingRoute: false},
                    i18n,
                    router,
                    components: {
                        OauthAuthorizeForm: () => import("./login/oauth-authorize-form"),
                        ErrorPage: () => import("./common/errors/error-page"),
                    },
                    mounted() {
                        document.getElementById('page-preloader').remove();
                        $('.vue-container').removeClass('invisible');
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
