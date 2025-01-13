import Vue from 'vue';
import VueRouter from 'vue-router';
import routes from './routes';
import {useCurrentUserStore} from "@/stores/current-user-store";
import {useFrontendConfigStore} from "@/stores/frontend-config-store";

Vue.use(VueRouter);

const router = new VueRouter({
    routes,
    base: '/',
    linkActiveClass: 'active',
    mode: 'history',
    scrollBehavior(to, from, savedPosition) {
        if (to.path === from.path || to.name?.indexOf('.') > 0) {
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
    const currentUser = useCurrentUserStore();
    const frontendConfig = useFrontendConfigStore();
    if (frontendConfig.config.regulationsAcceptRequired) {
        if (currentUser.username && !currentUser.userData.agreements.rules && to.name != 'agree-on-rules') {
            next({name: 'agree-on-rules'});
            return;
        }
    }
    next();
});

router.beforeEach((to, from, next) => {
    const currentUser = useCurrentUserStore();
    const frontendConfig = useFrontendConfigStore();
    if (!currentUser.username && !to.meta.unrestricted) {
        next({name: 'login', query: {target: (to.fullPath?.length > 2 ? to.fullPath : undefined)}});
    } else if (currentUser.username && to.meta.onlyUnauthenticated) {
        next(to.query?.target || '/');
    } else if (frontendConfig.config.maintenanceMode && to.meta.unavailableInMaintenance) {
        next('/');
    } else {
        next();
    }
});

router.beforeEach((to, from, next) => {
    const frontendConfig = useFrontendConfigStore();
    if (to.meta.requireBackendAndFrontendVersionMatches && !frontendConfig.backendAndFrontendVersionMatches) {
        next({name: 'update-in-progress'});
    } else {
        next();
    }
});

router.afterEach((to) => {
    const frontendConfig = useFrontendConfigStore();
    let cssClass = to.meta.bodyClass || '';
    if (frontendConfig.config.maintenanceMode) {
        cssClass += ' maintenance-mode';
    }
    if (cssClass) {
        document.body.setAttribute('class', cssClass);
    } else {
        document.body.removeAttribute('class');
    }
});

router.beforeEach((to, from, next) => {
    Vue.prototype.$changingRoute = true;
    next();
});

router.afterEach(() => Vue.prototype.$changingRoute = false);

export default router;
