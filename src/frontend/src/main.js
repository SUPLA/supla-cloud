import 'bootstrap/js/dropdown';
import 'bootstrap/js/tooltip';
import "pixeden-stroke-7-icon/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css";
import "bootstrap/dist/css/bootstrap.min.css";
import Vue from "vue";
import {i18n, setGuiLocale} from './locale';
import router from './router';
import VueResource from "vue-resource";
import ResponseErrorInterceptor from "./common/http/response-error-interceptor";
import * as requestTransformers from "./common/http/transformers";
import "./common/common-components";
import "./common/common-directives";
import "./common/filters";
import "./styles/styles.scss";
import "./polyfills";
import {CurrentUser} from "./login/current-user";
import {LocalStorageWithMemoryFallback} from "./common/local-storage";
import App from "./App";
import EventBus from "./common/event-bus";
import {DateTime, Settings} from 'luxon';
import './hello';
import './styles/fontawesome';
import FloatingVue from 'floating-vue';
import 'floating-vue/dist/style.css'
import {createApp} from 'vue-demi'
import {pinia} from "@/stores";
import {useFrontendConfigStore} from "@/stores/frontend-config-store";
import {PiniaVuePlugin} from "pinia";

Vue.use(VueResource);
Vue.use(FloatingVue);
Vue.use(PiniaVuePlugin);

Vue.prototype.$frontendVersion = FRONTEND_VERSION; // eslint-disable-line no-undef
Vue.config.productionTip = false;
Vue.http.headers.common['X-Accept-Version'] = '3';
Vue.http.headers.common['X-Client-Version'] = Vue.prototype.$frontendVersion;

Vue.prototype.$localStorage = new LocalStorageWithMemoryFallback();
Vue.prototype.$changingRoute = false;
Vue.http.options.root = '/api';
Vue.prototype.compareFrontendAndBackendVersion = (backendVersion) => {
    Vue.prototype.$backendVersion = backendVersion;
    const frontendUnknown = Vue.prototype.$frontendVersion === 'UNKNOWN_VERSION';
    const frontendAndBackendMatches = Vue.prototype.$frontendVersion.indexOf(Vue.prototype.$backendVersion) === 0;
    const isDev = ['dev', 'e2e'].includes(Vue.prototype.$appEnv);
    Vue.prototype.$backendAndFrontendVersionMatches = frontendAndBackendMatches || frontendUnknown || isDev;
    EventBus.$emit('backend-version-updated');
};

const appContainer = document.getElementById('vue-container');
if (!appContainer) {
    // eslint-disable-next-line no-console
    console.warn('App container #vue-container could not be found.');
}

const appConfig = {
    router,
    components: {
        OauthAuthorizeForm: () => import("./login/oauth-authorize-form"),
        ErrorPage: () => import("./common/errors/error-page"),
        DirectLinkExecutionResult: () => import("./direct-links/result-page/direct-link-execution-result"),
        PageFooter: () => import("./common/gui/page-footer"),
    },
    mounted() {
        document.getElementById('page-preloader').remove();
        document.getElementById('vue-container')?.classList.remove('hidden');
    },
};
if (!appContainer.children.length) {
    appConfig.render = h => h(App);
}
const app = createApp(appConfig);
app.use(i18n);
app.use(pinia);

const renderStart = new Date();
const frontendConfigStore = useFrontendConfigStore();
frontendConfigStore.fetchConfig()
    .then(() => {
        Vue.config.external = frontendConfigStore.config;
        Vue.prototype.$appEnv = frontendConfigStore.env || 'prod';
        Vue.prototype.$frontendConfig = Vue.config.external;
        Vue.prototype.compareFrontendAndBackendVersion(frontendConfigStore.cloudVersion);
        if (!Vue.config.external.baseUrl) {
            Vue.config.external.baseUrl = '';
        }
        const serverTime = DateTime.fromISO(frontendConfigStore.time).toJSDate();
        const offset = serverTime.getTime() - renderStart.getTime();
        Settings.now = function () {
            return Date.now() + offset;
        };
        Vue.prototype.$user = new CurrentUser();
    })
    .then(() => Vue.prototype.$user.fetchUser())
    .then((userData) => setGuiLocale(userData))
    .then(() => {
        app.mount(appContainer);
        Vue.http.interceptors.push(ResponseErrorInterceptor());
        for (const transformer in requestTransformers) {
            Vue.http.interceptors.push(requestTransformers[transformer]);
        }
    })
    // eslint-disable-next-line no-console
    .catch((error) => console.warn(error));
