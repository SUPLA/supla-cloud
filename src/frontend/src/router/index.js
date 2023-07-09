import Vue from 'vue';
import VueRouter from 'vue-router';
import routes from './routes';
import $ from 'jquery';

Vue.use(VueRouter);

const router = new VueRouter({
    routes,
    base: '/',
    linkActiveClass: 'active',
    mode: 'history',
    scrollBehavior(to, from, savedPosition) {
        if (to.path === from.path || ['channelReaction', 'channel'].includes(to.name)) {
            return savedPosition;
        }
        return new Promise((resolve) => {
            setTimeout(() => {
                resolve({x: 0, y: 0})
            }, 500)
        });
    },
});

router.beforeEach((to, from, next) => {
    if (Vue.config.external.regulationsAcceptRequired) {
        if (Vue.prototype.$user.username && !Vue.prototype.$user.userData.agreements.rules && to.name != 'agree-on-rules') {
            next({name: 'agree-on-rules'});
            return;
        }
    }
    next();
});

router.beforeEach((to, from, next) => {
    if (!Vue.prototype.$user.username && !to.meta.unrestricted) {
        next({name: 'login', query: {target: (to.fullPath?.length > 2 ? to.fullPath : undefined)}});
    } else if (Vue.prototype.$user.username && to.meta.onlyUnauthenticated) {
        next('/');
    } else if (Vue.config.external.maintenanceMode && to.meta.unavailableInMaintenance) {
        next('/');
    } else {
        next();
    }
});

router.beforeEach((to, from, next) => {
    if (to.meta.requireBackendAndFrontendVersionMatches && !Vue.prototype.$backendAndFrontendVersionMatches) {
        next({name: 'update-in-progress'});
    } else {
        next();
    }
});

router.afterEach((to) => {
    let cssClass = to.meta.bodyClass || '';
    if (Vue.config.external.maintenanceMode) {
        cssClass += ' maintenance-mode';
    }
    if (cssClass) {
        document.body.setAttribute('class', cssClass);
    } else {
        document.body.removeAttribute('class');
    }
});

router.afterEach(() => {
    $(".navbar-toggle:visible:not('.collapsed')").click();
});

export default router;
