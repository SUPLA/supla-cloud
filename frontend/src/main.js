import 'bootstrap-only-css/dist/bootstrap.min.css';
import './styles/styles.scss';
import './hello';

import {detectGuiLocale, i18n, loadLanguage} from './locale';

import {createApp} from 'vue';
import {createPinia} from 'pinia';

import VueTippy from 'vue-tippy';

import App from './App.vue';
import router from './router';
import {useFrontendConfigStore} from '@/stores/frontend-config-store.js';
import {useCurrentUserStore} from '@/stores/current-user-store.js';
import {registerDirectives} from '@/common/directives.js';
import {registerFontAwesome} from '@/styles/fontawesome.js';
import {registerNotifier} from '@/common/notifier.js';

const app = createApp(App);

app.use(createPinia());
app.use(i18n);
app.use(VueTippy, {directive: 'tooltip'});

registerDirectives(app);
registerFontAwesome(app);
registerNotifier(app);

const frontendConfigStore = useFrontendConfigStore();
const currentUserStore = useCurrentUserStore();

frontendConfigStore
  .fetchConfig()
  .then(() => currentUserStore.fetchUser())
  .then(() => loadLanguage('en'))
  .then(() => detectGuiLocale())
  .then(() => app.use(router))
  .then(() => app.mount('#vue-container'));
