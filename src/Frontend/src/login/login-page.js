import Vue from "vue";
import VueRouter from "vue-router";
import LoginForm from "./login-form.vue";
import CreateAccount from "../register/create-account.vue";
import CheckEmail from "../register/check-email.vue";
import SuplaDevicesSplash from "./supla-devices-splash.vue";
import RemindPassword from "./remind-password.vue";
import VueI18N from "vue-i18n";

const routes = [
    {path: '', component: LoginForm},
    {path: '/create', component: CreateAccount},
    {path: '/check-email', component: CheckEmail},
    {path: '/devices', component: SuplaDevicesSplash},
    {path: '/remind', component: RemindPassword}
];

const router = new VueRouter({routes});

const i18n = new VueI18N({
    locale: 'SUPLA_TRANSLATIONS',
    messages: {SUPLA_TRANSLATIONS},
});

const store = {
    state: {
        createdUser: ''
    },
    setCreatedUserAction (newValue) {
        this.state.createdUser = newValue;
    }
};

new Vue({
    router: router,
    i18n,
    store,
    template: '<transition name="fade"><router-view></router-view></transition>'
}).$mount('#login-page');
