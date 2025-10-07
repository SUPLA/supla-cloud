import Vue from 'vue';
import VueRouter from 'vue-router';
import routes from './routes';

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
    Vue.prototype.$changingRoute = true;
    next();
});

router.afterEach(() => Vue.prototype.$changingRoute = false);

export default router;
