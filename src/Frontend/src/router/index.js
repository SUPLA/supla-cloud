import Vue from 'vue';
import VueRouter from 'vue-router';
import routes from './routes';

Vue.use(VueRouter);

let config = window.FRONTEND_CONFIG || {};
if (!config.baseUrl) {
    config.baseUrl = '';
}

const router = new VueRouter({
    routes,
    base: config.baseUrl + '/',
    linkActiveClass: 'active',
    mode: 'history',
});

if (config.regulationsAcceptRequired) {
    router.beforeEach((to, from, next) => {
        if (Vue.prototype.$user.username && !Vue.prototype.$user.userData.agreements.rules && to.name != 'agree-on-rules') {
            next({name: 'agree-on-rules'});
        } else {
            next();
        }
    });
}

router.beforeEach((to, from, next) => {
    if (!Vue.prototype.$user.username && !to.meta.unrestricted) {
        next({name: 'login', query: {target: to.path}});
    } else if (Vue.prototype.$user.username && to.meta.onlyUnauthenticated) {
        next('/');
    } else {
        next();
    }
});

router.afterEach((to) => {
    if (to.meta.bodyClass) {
        document.body.setAttribute('class', to.meta.bodyClass);
    } else {
        document.body.removeAttribute('class');
    }
});

router.afterEach(() => {
    $(".navbar-toggle:visible:not('.collapsed')").click();
});

export default router;
