import $ from "jquery";
import moment from "moment";
import "bootstrap";
import "pixeden-stroke-7-icon/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css";
import "bootstrap/dist/css/bootstrap.min.css";
import Vue from "vue";
import {i18n, setGuiLocale} from './locale';
import router from './router';
import VueMoment from "vue-moment";
import VueResource from "vue-resource";
import vMediaQuery from 'v-media-query';
import ResponseErrorInterceptor from "./common/http/response-error-interceptor";
import * as requestTransformers from "./common/http/transformers";
import "moment-timezone";
import "./common/common-components";
import "./common/common-directives";
import "./common/filters";
import "./styles/styles.scss";
import "./polyfills";
import {CurrentUser} from "./login/current-user";
import {LocalStorageWithMemoryFallback} from "./common/local-storage";

Vue.use(VueMoment, {moment});
Vue.use(VueResource);
Vue.use(vMediaQuery, {variables: {xs: 768}});

Vue.config.productionTip = false;
Vue.http.headers.common['X-Accept-Version'] = '2.4.0';
Vue.http.headers.common['X-Client-Version'] = '2.4.0';//VERSION; // eslint-disable-line no-undef

Vue.prototype.$localStorage = new LocalStorageWithMemoryFallback();
Vue.http.options.root = '/api';

const renderStart = new Date();
Vue.http.get('server-info')
    .then(({body: info}) => {
        Vue.config.external = info.config;
        Vue.prototype.$frontendConfig = Vue.config.external;
        if (!Vue.config.external.baseUrl) {
            Vue.config.external.baseUrl = '';
        }
        const serverTime = new Date(Vue.config.external.serverTime);
        const offset = serverTime.getTime() - renderStart.getTime();
        moment.now = function () {
            return Date.now() + offset;
        };
        Vue.prototype.$user = new CurrentUser();
    })
    .then(() => Vue.prototype.$user.fetchUser())
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
                        DirectLinkExecutionResult: () => import("./direct-links/result-page/direct-link-execution-result"),
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
