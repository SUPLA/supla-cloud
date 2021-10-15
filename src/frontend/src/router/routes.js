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
        meta: {unrestricted: true, onlyUnauthenticated: true, unavailableInMaintenance: true, bodyClass: 'green register-slider-body'},
        alias: '/account/create_here'
    },
    {
        path: '/apps/:id?',
        component: () => import("@/integrations/catalog/public-apps-catalog"),
        name: 'publicApps',
        meta: {unrestricted: true},
        props: true,
    },
    {path: '/devices', component: () => import("@/login/supla-devices-splash"), meta: {unrestricted: true, onlyUnauthenticated: true}},
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
        path: '/confirm-deletion/:token',
        component: () => import("@/login/confirm-account-deletion"),
        meta: {unrestricted: true},
        props: true
    },
    {
        path: '/confirm-target-cloud-deletion/:targetCloudId/:token',
        component: () => import("@/integrations/confirm-target-cloud-deletion"),
        meta: {unrestricted: true},
        props: true
    },
    {
        path: '/access-identifiers', component: () => import("@/access-ids/access-ids-page"), name: "accessIds", children: [
            {path: ':id', component: () => import("@/access-ids/access-id-details"), name: 'accessId', props: true}
        ]
    },
    {path: '/account', component: () => import("@/account-details/account-page"), meta: {bodyClass: 'green'}},
    {
        path: '/channel-groups', component: () => import("@/channel-groups/channel-groups-page"), name: 'channelGroups', children: [
            {path: ':id', component: () => import("@/channel-groups/channel-group-details"), name: 'channelGroup', props: true}
        ]
    },
    {
        path: '/channels/:id',
        component: () => import("@/channels/channel-details-page"),
        name: 'channel',
        props: true,
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
        path: '/integrations', component: () => import("@/integrations/integrations-page"), children: [
            {path: 'authorized', component: () => import("@/integrations/authorized-oauth-apps"), name: 'authorized-oauth-apps'},
            {
                path: 'apps', component: () => import("@/integrations/oauth-apps/my-oauth-apps-page"), name: 'myOauthApps', children: [
                    {
                        path: ':id',
                        component: () => import("@/integrations/oauth-apps/my-oauth-app-details"),
                        name: 'myOauthApp',
                        props: true
                    }
                ]
            },
            {path: 'tokens', component: () => import("@/integrations/personal-tokens/personal-access-tokens"), name: 'personal-tokens'},
            {path: 'mqtt-broker', component: () => import("@/integrations/mqtt-broker-settings"), name: 'mqtt-broker'},
        ]
    },
    {
        path: '/register-cloud',
        component: () => import("@/integrations/register-target-cloud-form"),
        meta: {unrestricted: true, unavailableInMaintenance: true, bodyClass: 'register-slider-body'}
    },
    {
        path: '/scenes', component: () => import("@/scenes/scenes-page"), name: 'scenes', children: [
            {path: ':id', component: () => import("@/scenes/scene-details"), name: 'scene', props: true}
        ]
    },
    {
        path: '/schedules',
        component: () => import("@/schedules/schedule-list/schedules-page"),
        name: 'schedules',
        children: [
            {
                path: ':id',
                component: () => import("@/schedules/schedule-details/schedule-details-page"),
                name: 'schedule',
                props: true,
            },
            {
                path: ':id/edit',
                component: () => import("@/schedules/schedule-form/schedule-form"),
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
