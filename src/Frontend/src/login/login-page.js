import Vue from "vue";
import VueRouter from "vue-router";
import LoginForm from "./login-form.vue";
import SuplaDevicesSplash from "./supla-devices-splash.vue";
import RemindPassword from "./remind-password.vue";

Vue.use(VueRouter);

const routes = [
    {path: '', component: LoginForm},
    {path: '/devices', component: SuplaDevicesSplash},
    {path: '/remind', component: RemindPassword}
];

const router = new VueRouter({routes});

new Vue({
    router: router,
    template: '<transition name="fade"><router-view></router-view></transition>'
}).$mount('#login-page');
