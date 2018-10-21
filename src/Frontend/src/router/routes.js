export default [
    {path: '/', component: () => import("@/home/home-page")},
    {
        path: '/login',
        component: () => import("@/login/login-page"),
        alias: '/auth/login',
        meta: {unrestricted: true, onlyUnauthenticated: true},
        name: 'login'
    },
    {path: '/oauth-authorize', component: () => import("@/login/login-page-oauth"), meta: {unrestricted: true}, name: 'oauthAuthorize'},
    {
        path: '/terms',
        component: () => import("@/common/pages/terms"),
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'green'},
    },
    {
        path: '/register',
        component: () => import("@/register/create-account"),
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'green'},
        alias: '/account/create_here'
    },
    {
        path: '/apps/:id?',
        component: () => import("@/oauth/catalog/public-apps-catalog"),
        name: 'publicApps',
        meta: {unrestricted: true},
        props: true,
        // children: [
        //     {path: ':id', component: () => import("@/oauth/catalog/public-app-details"), name: 'publicApp', props: true}
        // ]
    },
    {path: '/devices', component: () => import("@/login/supla-devices-splash"), meta: {unrestricted: true, onlyUnauthenticated: true}},
    {
        path: '/forgotten-password',
        component: () => import("@/login/remind-password"),
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'yellow'}
    },
    {
        path: '/reset-password/:token',
        component: () => import("@/login/reset-password"),
        meta: {unrestricted: true, onlyUnauthenticated: true, bodyClass: 'yellow'},
        props: true
    },
    {
        path: '/confirm/:token',
        component: () => import("@/login/confirm-account"),
        meta: {unrestricted: true, onlyUnauthenticated: true},
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
    {path: '/channels/:id', component: () => import("@/channels/channel-details-page"), name: 'channel', props: true},
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
        path: '/integrations', component: () => import("@/oauth/integrations-page"), children: [
            {path: 'authorized', component: () => import("@/oauth/authorized-oauth-apps"), name: 'authorized-oauth-apps'},
            {
                path: 'apps', component: () => import("@/oauth/oauth-apps/my-oauth-apps-page"), name: 'myOauthApps', children: [
                    {
                        path: ':id',
                        component: () => import("@/oauth/oauth-apps/my-oauth-app-details"),
                        name: 'myOauthApp',
                        props: true
                    }
                ]
            },
            {path: 'tokens', component: () => import("@/oauth/personal-tokens/personal-access-tokens"), name: 'personal-tokens'},
        ]
    },
    {path: '/me', component: () => import("@/home/my-supla-page")},
    {path: '/register-cloud', component: () => import("@/oauth/register-target-cloud-form"), meta: {unrestricted: true}},
    {path: '/schedules', component: () => import("@/schedules/schedule-list/schedule-list-page"), name: 'schedules'},
    {path: '/schedules/new', component: () => import("@/schedules/schedule-form/schedule-form"), name: 'schedule.new'},
    {path: '/schedules/:id', component: () => import("@/schedules/schedule-details/schedule-details-page"), name: 'schedule', props: true},
    {path: '/schedules/edit/:id', component: () => import("@/schedules/schedule-form/schedule-form"), name: 'schedule.edit', props: true},
    {path: '/smartphones', component: () => import("@/client-apps/client-apps-page")},
    {
        path: "/agree-on-rules",
        component: () => import("@/common/errors/error-agree-on-rules"),
        name: 'agree-on-rules',
        meta: {bodyClass: 'warning hide-cookies-warning'}
    },
    {path: "*", component: () => import("@/common/errors/error-404"), meta: {bodyClass: 'red', unrestricted: true}}
];
