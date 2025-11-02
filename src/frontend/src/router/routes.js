export default [
  {path: '/me', redirect: '/'}, // backward compat
  {path: '/old-config', redirect: '/home'}, // backward compat
  {path: '/', component: () => import('@/home/my-supla-page.vue'), name: 'me'},
  {path: '/home', component: () => import('@/home/home-page.vue'), name: 'home'},
  {
    path: '/login',
    component: () => import('@/login/login-page.vue'),
    alias: '/auth/login',
    meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'centered-form-page'},
    name: 'login',
  },
  {
    path: '/forgotten-password',
    component: () => import('@/login/remind-password.vue'),
    meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'yellow centered-form-page'},
  },
  {
    path: '/register',
    component: () => import('@/register/create-account.vue'),
    meta: {
      unrestricted: true,
      onlyUnauthenticated: true,
      unavailableInMaintenance: true,
      bodyClass: 'green darker register-slider-body',
    },
    alias: '/account/create_here',
  },
  {
    path: '/confirm/:token',
    component: () => import('@/login/confirm-account.vue'),
    meta: {unrestricted: true, onlyUnauthenticated: true},
    props: true,
  },
  {
    path: '/locations',
    component: () => import('@/locations/locations-page.vue'),
    name: 'locations',
    children: [
      {
        path: ':id',
        component: () => import('@/locations/location-details.vue'),
        name: 'location',
        props: true,
      },
    ],
  },
  {
    path: '/access-identifiers',
    component: () => import('@/access-ids/access-ids-page.vue'),
    name: 'accessIds',
    children: [
      {
        path: ':id',
        component: () => import('@/access-ids/access-id-details.vue'),
        name: 'accessId',
        props: true,
      },
    ],
  },
  {path: '/smartphones', component: () => import('@/client-apps/client-apps-page.vue')},
  {
    path: '/devices/:id',
    component: () => import('@/devices/details/device-details-page.vue'),
    name: 'device',
    props: ({params}) => ({id: Number.parseInt(params.id)}),
    children: [
      {
        path: 'channels',
        component: () => import('@/devices/details/device-channel-list-page.vue'),
        name: 'device.channels',
        props: ({params}) => ({deviceId: Number.parseInt(params.id)}),
      },
      {
        path: 'details',
        component: () => import('@/devices/details/device-details-tab.vue'),
        name: 'device.details',
      },
      {
        path: 'notifications',
        component: () => import('@/devices/details/device-managed-notifications.vue'),
        name: 'device.notifications',
        props: true,
      },
      {
        path: 'unlock',
        component: () => import('@/devices/details/device-unlock.vue'),
        name: 'device.unlock',
      },
    ],
  },
  {
    path: '/confirm-device-unlock/:deviceId/:unlockCode',
    component: () => import('@/devices/confirm-device-unlock.vue'),
    meta: {unrestricted: true},
    props: true,
  },
  {
    path: '/channels/:id',
    component: () => import('@/channels/channel-details-page.vue'),
    name: 'channel',
    props: true,
    children: [
      {
        path: 'reactions',
        component: () => import('@/channels/reactions/channel-reactions-config.vue'),
        name: 'channel.reactions',
        children: [
          {
            path: ':reactionId',
            component: () => import('@/channels/reactions/channel-reaction.vue'),
            name: 'channel.reactions.details',
            props: true,
          },
        ],
      },
      {
        path: 'direct-links',
        component: () => import('@/direct-links/direct-links-list.vue'),
        name: 'channel.directLinks',
      },
      {
        path: 'schedules',
        component: () => import('@/schedules/schedule-list/schedules-list.vue'),
        name: 'channel.schedules',
      },
      {
        path: 'channel-groups',
        component: () => import('@/channel-groups/channel-groups-list.vue'),
        name: 'channel.channelGroups',
      },
      {
        path: 'scenes',
        component: () => import('@/scenes/scenes-list.vue'),
        name: 'channel.scenes',
      },
      {
        path: 'notifications',
        component: () => import('@/channels/reactions/channel-managed-notifications.vue'),
        name: 'channel.notifications',
      },
      {
        path: 'thermostat-programs',
        component: () => import('@/channels/hvac/thermostat-programs-tab.vue'),
        name: 'channel.thermostatPrograms',
      },
      {
        path: 'ocr-settings',
        component: () => import('@/channels/ocr/ocr-settings-tab.vue'),
        name: 'channel.ocrSettings',
      },
      {
        path: 'action-triggers',
        component: () => import('@/channels/action-trigger/channel-action-triggers.vue'),
        name: 'channel.actionTriggers',
      },
      {
        path: 'measurements',
        component: () => import('@/channels/history/channel-measurements-history.vue'),
        name: 'channel.measurementsHistory',
      },
      {
        path: 'voltage-aberrations',
        component: () => import('@/channels/channel-voltage-history.vue'),
        name: 'channel.voltageAberrations',
      },
    ],
  },
  {
    path: '/channel-groups',
    component: () => import('@/channel-groups/channel-groups-page.vue'),
    name: 'channelGroups',
    children: [
      {
        path: ':id',
        component: () => import('@/channel-groups/channel-group-details.vue'),
        name: 'channelGroup',
        props: true,
      },
    ],
  },
  {
    path: '/reactions',
    component: () => import('@/channels/reactions/channel-reactions-list.vue'),
    name: 'reactions',
  },
  {
    path: '/notifications',
    component: () => import('@/notifications/notifications-list.vue'),
    name: 'notifications',
  },
  {
    path: '/scenes',
    component: () => import('@/scenes/scenes-page.vue'),
    name: 'scenes',
    children: [
      {
        path: ':id',
        component: () => import('@/scenes/scene-details.vue'),
        name: 'scene',
        props: true,
      },
    ],
  },
  {
    path: '/schedules',
    component: () => import('@/schedules/schedule-list/schedules-page.vue'),
    name: 'schedules',
    children: [
      {
        path: ':id',
        component: () => import('@/schedules/schedule-details/schedule-details-page.vue'),
        name: 'schedule',
        props: true,
      },
      {
        path: ':id/edit',
        component: () => import('@/schedules/schedule-form/schedule-form.vue'),
        name: 'schedule.edit',
        props: true,
      },
    ],
  },
  {
    path: '/direct-links',
    component: () => import('@/direct-links/direct-links-page.vue'),
    name: 'directLinks',
    children: [
      {
        path: ':id',
        component: () => import('@/direct-links/direct-link-details.vue'),
        name: 'directLink',
        props: true,
      },
    ],
  },
  {
    path: '/direct/:linkId/:slug/:action?',
    component: () => import('@/direct-links/result-page/direct-link-execution-result.vue'),
    props: ({params}) => ({...params, linkId: Number.parseInt(params.linkId)}),
    meta: {unrestricted: true},
  },
  {
    path: '/oauth-authorize',
    component: () => import('@/login/login-page-oauth.vue'),
    meta: {unrestricted: true, bodyClass: 'centered-form-page'},
  },
  {
    path: '/oauth/v2/auth',
    component: () => import('@/login/oauth-authorize-form.vue'),
    meta: {unrestricted: true, bodyClass: 'centered-form-page'},
  },
  {
    path: '/terms',
    component: () => import('@/common/pages/terms-page.vue'),
    meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'green'},
  },
  {
    path: '/privacy',
    component: () => import('@/common/pages/privacy-page.vue'),
    meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'green'},
  },
  {
    path: '/account',
    component: () => import('@/account/account-page.vue'),
    meta: {bodyClass: 'green'},
  },
  {
    path: '/account-deletion/:token',
    component: () => import('@/login/confirm-account-deletion.vue'),
    meta: {unrestricted: true, bodyClass: 'account-deletion-page warning'},
    props: true,
  },
  {
    path: '/reset-password/:token',
    component: () => import('@/login/reset-password.vue'),
    meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'yellow centered-form-page'},
    props: true,
  },
  {
    path: '/integrations',
    component: () => import('@/account/integrations/integrations-page.vue'),
    children: [
      {
        path: 'apps',
        component: () => import('@/account/integrations/oauth-apps/my-oauth-apps-page.vue'),
        name: 'integrations.myOauthApps',
        children: [
          {
            path: ':id',
            component: () => import('@/account/integrations/oauth-apps/my-oauth-app-details.vue'),
            name: 'myOauthApp',
            props: true,
          },
        ],
      },
      {
        path: 'mqtt-broker',
        component: () => import('@/account/integrations/mqtt-broker-settings.vue'),
        name: 'integrations.mqtt',
      },
      {
        path: 'data-sources',
        component: () => import('@/account/integrations/data-sources/virtual-channel-list-page.vue'),
        name: 'integrations.dataSources',
      },
    ],
  },
  {
    path: '/security',
    component: () => import('@/account/safety/safety-page.vue'),
    children: [
      {
        path: 'log',
        component: () => import('@/account/safety/security-log.vue'),
        name: 'safety.log',
      },
      {
        path: 'access-tokens',
        component: () => import('@/account/safety/security-access-tokens.vue'),
        name: 'safety.accessTokens',
      },
      {
        path: 'authorized-apps',
        component: () => import('@/account/safety/authorized-oauth-apps.vue'),
        name: 'safety.authorizedOAuthApps',
      },
      {
        path: 'personal-access-tokens',
        component: () => import('@/account/safety/personal-tokens/personal-access-tokens.vue'),
        name: 'safety.personalTokens',
      },
      {
        path: 'support-access',
        component: () => import('@/account/safety/support-access-form.vue'),
        name: 'safety.supportAccess',
      },
      {
        path: 'change-password',
        component: () => import('@/account/safety/account-password-change.vue'),
        name: 'safety.changePassword',
      },
    ],
  },
  {
    path: '/db99845855b2ecbfecca9a095062b96c3e27703f',
    component: () => import('@/login/request-account-deletion.vue'),
    meta: {unrestricted: true, bodyClass: 'account-deletion-page warning smartphone-webview'},
  },
  {
    path: '/agree-on-rules',
    component: () => import('@/common/errors/error-agree-on-rules.vue'),
    name: 'agree-on-rules',
    meta: {bodyClass: 'warning hide-cookies-warning'},
  },
  {
    path: '/update-in-progress',
    component: () => import('@/common/errors/update-in-progress.vue'),
    name: 'update-in-progress',
    meta: {bodyClass: 'warning'},
  },
  {
    path: '/apps/:id?',
    component: () => import('@/account/integrations/catalog/public-apps-catalog.vue'),
    name: 'publicApps',
    meta: {unrestricted: true},
    props: true,
  },
  {
    path: '/confirm-target-cloud-deletion/:targetCloudId/:token',
    component: () => import('@/account/integrations/confirm-target-cloud-deletion.vue'),
    meta: {unrestricted: true},
    props: true,
  },
  {
    path: '/register-cloud',
    component: () => import('@/account/integrations/register-target-cloud-form.vue'),
    meta: {unrestricted: true, unavailableInMaintenance: true, bodyClass: 'register-slider-body'},
  },
  {
    path: '/:pathMatch(.*)*',
    component: () => import('@/common/errors/error-page-status.vue'),
    meta: {bodyClass: 'red', unrestricted: true},
  },
];
