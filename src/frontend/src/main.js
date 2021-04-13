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
import App from "./App";

Vue.use(VueMoment, {moment});
Vue.use(VueResource);
Vue.use(vMediaQuery, {variables: {xs: 768}});

Vue.config.productionTip = false;
Vue.http.headers.common['X-Accept-Version'] = '2.4.0';
Vue.http.headers.common['X-Client-Version'] = VERSION; // eslint-disable-line no-undef

Vue.prototype.$localStorage = new LocalStorageWithMemoryFallback();
Vue.prototype.$changingRoute = false;
Vue.http.options.root = '/api';

const renderStart = new Date();
Vue.http.get('server-info')
    .then(({body: info}) => {
        Vue.config.external = info.config;
        Vue.prototype.$frontendConfig = Vue.config.external;
        if (!Vue.config.external.baseUrl) {
            Vue.config.external.baseUrl = '';
        }
        const serverTime = moment(info.time).toDate();
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
            if ($('#vue-container').length) {
                const appConfig = {
                    i18n,
                    router,
                    components: {
                        OauthAuthorizeForm: () => import("./login/oauth-authorize-form"),
                        ErrorPage: () => import("./common/errors/error-page"),
                        DirectLinkExecutionResult: () => import("./direct-links/result-page/direct-link-execution-result"),
                        PageFooter: () => import("./common/gui/page-footer"),
                    },
                    mounted() {
                        document.getElementById('page-preloader').remove();
                        $('#vue-container').removeClass('invisible');
                    },
                };
                if (!$("#vue-container").children().length) {
                    appConfig.render = h => h(App);
                }
                const app = new Vue(appConfig).$mount('#vue-container');

                router.beforeEach((to, from, next) => {
                    Vue.prototype.$changingRoute = true;
                    next();
                });
                router.afterEach(() => Vue.prototype.$changingRoute = false);

                Vue.http.interceptors.push(ResponseErrorInterceptor(app));
                for (const transformer in requestTransformers) {
                    Vue.http.interceptors.push(requestTransformers[transformer]);
                }
            }
        });
    });
