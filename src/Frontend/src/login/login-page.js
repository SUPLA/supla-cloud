import Vue from "vue";
import VueRouter from "vue-router";
import LoginForm from "./login-form.vue";
import SuplaDevicesSplash from "./supla-devices-splash.vue";
import RemindPassword from "./remind-password.vue";
import VueI18N from "vue-i18n";

Vue.use(VueRouter);

const routes = [
    {path: '', component: LoginForm},
    {path: '/devices', component: SuplaDevicesSplash},
    {path: '/remind', component: RemindPassword}
];

const router = new VueRouter({routes});

const i18n = new VueI18N({
    locale: 'SUPLA_TRANSLATIONS',
    messages: {SUPLA_TRANSLATIONS},
});

new Vue({
    router: router,
    i18n,
    template: '<transition name="fade"><router-view></router-view></transition>'
}).$mount('#login-page');
