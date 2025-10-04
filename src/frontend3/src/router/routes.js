export default [
  {path: '/me', redirect: '/'}, // backward compat
  {path: '/old-config', redirect: '/home'}, // backward compat
  {path: '/', component: () => import("@/home/my-supla-page.vue"), name: 'me'},
  {path: '/home', component: () => import("@/home/home-page.vue"), name: 'home'},
  {
    path: '/login',
    component: () => import("@/login/login-page.vue"),
    alias: '/auth/login',
    meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'centered-form-page'},
    name: 'login'
  },
  {
    path: '/forgotten-password',
    component: () => import("@/login/remind-password.vue"),
    meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'yellow centered-form-page'}
  },
  {
    path: '/register',
    component: () => import("@/register/create-account.vue"),
    meta: {
      unrestricted: true,
      onlyUnauthenticated: true,
      unavailableInMaintenance: true,
      bodyClass: 'green darker register-slider-body',
    },
    alias: '/account/create_here'
  },
  {
    path: '/confirm/:token',
    component: () => import("@/login/confirm-account.vue"),
    meta: {unrestricted: true, onlyUnauthenticated: true},
    props: true,
  },
];
