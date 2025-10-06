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
  {
    path: '/locations', component: () => import("@/locations/locations-page.vue"), name: 'locations', children: [
      {path: ':id', component: () => import("@/locations/location-details.vue"), name: 'location', props: true}
    ]
  },
  {
    path: '/access-identifiers', component: () => import("@/access-ids/access-ids-page.vue"), name: "accessIds", children: [
      {path: ':id', component: () => import("@/access-ids/access-id-details.vue"), name: 'accessId', props: true}
    ]
  },
  {path: '/smartphones', component: () => import("@/client-apps/client-apps-page.vue")},
  {
    path: "/:pathMatch(.*)*",
    component: () => import("@/common/errors/error-404.vue"),
    meta: {bodyClass: 'red', unrestricted: true}
  }
];
