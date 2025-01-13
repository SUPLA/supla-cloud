import 'bootstrap/js/dropdown';
import 'bootstrap/js/tooltip';
import "pixeden-stroke-7-icon/pe-icon-7-stroke/dist/pe-icon-7-stroke.min.css";
import "bootstrap/dist/css/bootstrap.min.css";
import {detectGuiLocale, i18n, loadLanguage} from './locale';
import Vue, {createApp} from "vue";
import router from './router';
import VueResource from "vue-resource";
import ResponseErrorInterceptor from "./common/http/response-error-interceptor";
import * as requestTransformers from "./common/http/transformers";
import "./common/common-components";
import "./common/common-directives";
import "./common/filters";
import "./styles/styles.scss";
import "./polyfills";
import {LocalStorageWithMemoryFallback} from "./common/local-storage";
import App from "./App";
import './hello';
import './styles/fontawesome';
import FloatingVue from 'floating-vue';
import 'floating-vue/dist/style.css'
import {pinia} from "@/stores";
import {useFrontendConfigStore} from "@/stores/frontend-config-store";
import {PiniaVuePlugin} from "pinia";
import {useCurrentUserStore} from "@/stores/current-user-store";

Vue.use(VueResource);
Vue.use(FloatingVue);
Vue.use(PiniaVuePlugin);

Vue.config.productionTip = false;
Vue.http.headers.common['X-Accept-Version'] = '3';
Vue.http.headers.common['X-Client-Version'] = FRONTEND_VERSION; // eslint-disable-line no-undef

Vue.prototype.$localStorage = new LocalStorageWithMemoryFallback();
Vue.prototype.$changingRoute = false;
Vue.http.options.root = '/api';

const appContainer = document.getElementById('vue-container');
if (!appContainer) {
    // eslint-disable-next-line no-console
    console.error('App container #vue-container could not be found.');
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
app.use(router);

const frontendConfigStore = useFrontendConfigStore();
const currentUserStore = useCurrentUserStore();

frontendConfigStore.fetchConfig()
    .then(() => currentUserStore.fetchUser())
    .then(() => loadLanguage('en'))
    .then(() => detectGuiLocale())
    .then(() => {
        app.mount(appContainer);
        Vue.http.interceptors.push(ResponseErrorInterceptor());
        for (const transformer in requestTransformers) {
            Vue.http.interceptors.push(requestTransformers[transformer]);
        }
    });

