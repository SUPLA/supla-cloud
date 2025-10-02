import {createRouter, createWebHistory} from 'vue-router'
import {useFrontendConfigStore} from "@/stores/frontend-config-store.js";
import routes from './routes';
import {useCurrentUserStore} from "@/stores/current-user-store.js";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes,
})

router.beforeEach((to) => {
  const currentUser = useCurrentUserStore();
  const frontendConfig = useFrontendConfigStore();
  if (!currentUser.username && !to.meta.unrestricted) {
    return {name: 'login', query: {target: (to.fullPath?.length > 2 ? to.fullPath : undefined)}};
  } else if (currentUser.username && to.meta.onlyUnauthenticated) {
    return to.query?.target || '/';
  } else if (frontendConfig.config.maintenanceMode && to.meta.unavailableInMaintenance) {
    return '/';
  } else {
    return true;
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

export default router
