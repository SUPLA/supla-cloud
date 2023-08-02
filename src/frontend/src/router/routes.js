export default [
    {path: '/', component: () => import("@/home/my-supla-page"), name: 'me'},
    {path: '/me', redirect: '/'}, // backward compat
    {path: '/home', component: () => import("@/home/home-page"), name: 'home'},
    {path: '/old-config', redirect: '/home'}, // backward compat
    {
        path: '/login',
        component: () => import("@/login/login-page"),
        alias: '/auth/login',
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'centered-form-page'},
        name: 'login'
    },
    {
        path: '/oauth-authorize',
        component: () => import("@/login/login-page-oauth"),
        meta: {unrestricted: true, bodyClass: 'centered-form-page'},
        name: 'oauthAuthorize'
    },
    {
        path: '/terms',
        component: () => import("@/common/pages/terms"),
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'green'},
    },
    {
        path: '/privacy',
        component: () => import("@/common/pages/privacy"),
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'green'},
    },
    {
        path: '/register',
        component: () => import("@/register/create-account"),
        meta: {
            unrestricted: true,
            onlyUnauthenticated: true,
            unavailableInMaintenance: true,
            bodyClass: 'green darker register-slider-body',
        },
        alias: '/account/create_here'
    },
    {
        path: '/apps/:id?',
        component: () => import("@/account/integrations/catalog/public-apps-catalog"),
        name: 'publicApps',
        meta: {unrestricted: true},
        props: true,
    },
    {
        path: '/forgotten-password',
        component: () => import("@/login/remind-password"),
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'yellow centered-form-page'}
    },
    {
        path: '/reset-password/:token',
        component: () => import("@/login/reset-password"),
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'yellow centered-form-page'},
        props: true
    },
    {
        path: '/confirm/:token',
        component: () => import("@/login/confirm-account"),
        meta: {unrestricted: true, onlyUnauthenticated: true},
        props: true
    },
    {
        path: '/account-deletion/:token',
        component: () => import("@/login/confirm-account-deletion"),
        meta: {unrestricted: true, bodyClass: 'account-deletion-page warning'},
        props: true
    },
    {
        path: '/db99845855b2ecbfecca9a095062b96c3e27703f',
        component: () => import("@/login/request-account-deletion"),
        meta: {unrestricted: true, bodyClass: 'account-deletion-page warning smartphone-webview'},
    },
    {
        path: '/confirm-target-cloud-deletion/:targetCloudId/:token',
        component: () => import("@/account/integrations/confirm-target-cloud-deletion"),
        meta: {unrestricted: true},
        props: true
    },
    {
        path: '/access-identifiers', component: () => import("@/access-ids/access-ids-page"), name: "accessIds", children: [
            {path: ':id', component: () => import("@/access-ids/access-id-details"), name: 'accessId', props: true}
        ]
    },
    {
        path: '/account',
        component: () => import(/*webpackChunkName:"account-page"*/"@/account/account-page"),
        meta: {bodyClass: 'green'}
    },
    {
        path: '/channel-groups', component: () => import("@/channel-groups/channel-groups-page"), name: 'channelGroups', children: [
            {path: ':id', component: () => import("@/channel-groups/channel-group-details"), name: 'channelGroup', props: true}
        ]
    },
    {
        path: '/channels/:id',
        component: () => import(/*webpackChunkName:"channels-details-page"*/"@/channels/channel-details-page"),
        name: 'channel',
        props: true,
        children: [
            {
                path: 'reactions/:reactionId',
                component: () => import("@/channels/reactions/channel-reaction"),
                name: 'channelReaction',
                props: true
            }
        ],
    },
    {path: '/devices/:id', component: () => import("@/devices/details/device-details-page"), name: 'device', props: true},
    {
        path: '/locations', component: () => import("@/locations/locations-page"), name: 'locations', children: [
            {path: ':id', component: () => import("@/locations/location-details"), name: 'location', props: true}
        ]
    },
    {
        path: '/direct-links', component: () => import("@/direct-links/direct-links-page"), name: 'directLinks', children: [
            {path: ':id', component: () => import("@/direct-links/direct-link-details"), name: 'directLink', props: true}
        ]
    },
    {
        path: '/integrations', component: () => import("@/account/integrations/integrations-page"), children: [
            {
                path: 'apps',
                component: () => import("@/account/integrations/oauth-apps/my-oauth-apps-page"),
                name: 'integrations.myOauthApps',
                children: [
                    {
                        path: ':id',
                        component: () => import(/*webpackChunkName:"my-oauth-app-details"*/"@/account/integrations/oauth-apps/my-oauth-app-details"),
                        name: 'myOauthApp',
                        props: true
                    }
                ]
            },
            {path: 'mqtt-broker', component: () => import("@/account/integrations/mqtt-broker-settings"), name: 'integrations.mqtt'},
        ]
    },
    {
        path: '/security', component: () => import(/*webpackChunkName:"safety"*/"@/account/safety/safety-page"), children: [
            {path: 'log', component: () => import("@/account/safety/security-log"), name: 'safety.log'},
            {path: 'access-tokens', component: () => import("@/account/safety/security-access-tokens"), name: 'safety.accessTokens'},
            {
                path: 'authorized-apps',
                component: () => import("@/account/safety/authorized-oauth-apps"),
                name: 'safety.authorizedOAuthApps'
            },
            {
                path: 'personal-access-tokens',
                component: () => import("@/account/safety/personal-tokens/personal-access-tokens"),
                name: 'safety.personalTokens'
            },
            {path: 'change-password', component: () => import("@/account/safety/account-password-change"), name: 'safety.changePassword'},
        ]
    },
    {
        path: '/register-cloud',
        component: () => import("@/account/integrations/register-target-cloud-form"),
        meta: {unrestricted: true, unavailableInMaintenance: true, bodyClass: 'register-slider-body'}
    },
    {
        path: '/scenes', component: () => import("@/scenes/scenes-page"), name: 'scenes', children: [
            {path: ':id', component: () => import("@/scenes/scene-details"), name: 'scene', props: true}
        ]
    },
    {
        path: '/schedules',
        component: () => import(/*webpackChunkName:"schedules-page"*/"@/schedules/schedule-list/schedules-page"),
        name: 'schedules',
        children: [
            {
                path: ':id',
                component: () => import(/*webpackChunkName:"schedules-details-page"*/"@/schedules/schedule-details/schedule-details-page"),
                name: 'schedule',
                props: true,
            },
            {
                path: ':id/edit',
                component: () => import(/*webpackChunkName:"schedules-form"*/"@/schedules/schedule-form/schedule-form"),
                name: 'schedule.edit',
                props: true,
            },
        ]
    },
    {path: '/smartphones', component: () => import("@/client-apps/client-apps-page")},
    {
        path: "/agree-on-rules",
        component: () => import("@/common/errors/error-agree-on-rules"),
        name: 'agree-on-rules',
        meta: {bodyClass: 'warning hide-cookies-warning'}
    },
    {
        path: "/update-in-progress",
        component: () => import("@/common/errors/update-in-progress"),
        name: 'update-in-progress',
        meta: {bodyClass: 'warning'}
    },
    {path: "*", component: () => import("@/common/errors/error-404"), meta: {bodyClass: 'red', unrestricted: true}}
];
