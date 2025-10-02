import "./styles/styles.scss";
import "bootstrap/dist/css/bootstrap.min.css";

import {detectGuiLocale, i18n, loadLanguage} from './locale';

import {createApp} from 'vue'
import {createPinia} from 'pinia'

import App from './App.vue'
import router from './router'
import {useFrontendConfigStore} from "@/stores/frontend-config-store.js";
import {useCurrentUserStore} from "@/stores/current-user-store.js";

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(i18n)

const frontendConfigStore = useFrontendConfigStore();
const currentUserStore = useCurrentUserStore();

frontendConfigStore.fetchConfig()
  .then(() => currentUserStore.fetchUser())
  .then(() => loadLanguage('en'))
  .then(() => detectGuiLocale())

app.mount('#app')
