import {createRouter, createWebHistory} from 'vue-router'
import HomeView from '../views/HomeView.vue'
import {useFrontendConfigStore} from "@/stores/frontend-config-store.js";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView,
    },
    {
      path: '/about',
      name: 'about',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/login',
      component: () => import("@/login/login-page.vue"),
      alias: '/auth/login',
      meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'centered-form-page'},
      name: 'login'
    },
    {
      path: '/forgotten-password',
      name: 'forgotten-password',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue'),
    },
    {
      path: '/register',
      name: 'forgoftten-password',
      // route level code-splitting
      // this generates a separate chunk (About.[hash].js) for this route
      // which is lazy-loaded when the route is visited.
      component: () => import('../views/AboutView.vue'),
    },
  ],
})

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

export default router
