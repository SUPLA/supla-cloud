export default [

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
        path: '/apps/:id?',
        component: () => import("@/account/integrations/catalog/public-apps-catalog"),
        name: 'publicApps',
        meta: {unrestricted: true},
        props: true,
    },
    {
        path: '/reset-password/:token',
        component: () => import("@/login/reset-password"),
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'yellow centered-form-page'},
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
        path: '/confirm-device-unlock/:deviceId/:unlockCode',
        component: () => import("@/devices/confirm-device-unlock.vue"),
        meta: {unrestricted: true},
        props: true
    },
    {
        path: '/account',
        component: () => import(/*webpackChunkName:"account-page"*/"@/account/account-page"),
        meta: {bodyClass: 'green'}
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
            {
                path: 'data-sources',
                component: () => import("@/account/integrations/data-sources/virtual-channel-list-page.vue"),
                name: 'integrations.dataSources'
            },
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

];
