import "bootstrap-only-css/dist/bootstrap.min.css";
import "./styles/styles.scss";

import {detectGuiLocale, i18n, loadLanguage} from './locale';

import {createApp} from 'vue'
import {createPinia} from 'pinia'

import App from './App.vue'
import router from './router'
import {useFrontendConfigStore} from "@/stores/frontend-config-store.js";
import {useCurrentUserStore} from "@/stores/current-user-store.js";
import {registerDirectives} from "@/common/directives.js";
import {registerFontAwesome} from "@/styles/fontawesome.js";

const app = createApp(App)

app.use(createPinia())
app.use(i18n)

registerDirectives(app)
registerFontAwesome(app)

const frontendConfigStore = useFrontendConfigStore();
const currentUserStore = useCurrentUserStore();

frontendConfigStore.fetchConfig()
  .then(() => currentUserStore.fetchUser())
  .then(() => loadLanguage('en'))
  .then(() => detectGuiLocale())
  .then(() => app.use(router))
  .then(() => app.mount('#vue-container'));


