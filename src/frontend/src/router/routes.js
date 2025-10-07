export default [



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
        path: '/register-cloud',
        component: () => import("@/account/integrations/register-target-cloud-form"),
        meta: {unrestricted: true, unavailableInMaintenance: true, bodyClass: 'register-slider-body'}
    },



];
