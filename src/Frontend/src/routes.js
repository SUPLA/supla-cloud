export default [
    {path: '/', component: () => import("./home/home-page")},
    {
        path: '/access-identifiers', component: () => import("./access-ids/access-ids-page"), name: "accessIds", children: [
            {path: ':id', component: () => import("./access-ids/access-id-details"), name: 'accessId', props: true}
        ]
    },
    {path: '/account', component: () => import("./account-details/account-page"), meta: {bodyClass: 'green'}},
    {path: '/api', component: () => import("./account-details/api-settings-page"), meta: {bodyClass: 'green'}},
    {
        path: '/channel-groups', component: () => import("./channel-groups/channel-groups-page"), name: 'channelGroups', children: [
            {path: ':id', component: () => import("./channel-groups/channel-group-details"), name: 'channelGroup', props: true}
        ]
    },
    {path: '/channels/:id', component: () => import("./channels/channel-details-page"), name: 'channel', props: true},
    {path: '/devices/:id', component: () => import("./devices/details/device-details-page"), name: 'device', props: true},
    {
        path: '/locations', component: () => import("./locations/locations-page"), name: 'locations', children: [
            {path: ':id', component: () => import("./locations/location-details"), name: 'location', props: true}
        ]
    },
    {path: '/me', component: () => import("./home/my-supla-page")},
    {path: '/schedules', component: () => import("./schedules/schedule-list/schedule-list-page"), name: 'schedules'},
    {path: '/schedules/new', component: () => import("./schedules/schedule-form/schedule-form"), name: 'schedule.new'},
    {path: '/schedules/:id', component: () => import("./schedules/schedule-details/schedule-details-page"), name: 'schedule', props: true},
    {path: '/schedules/edit/:id', component: () => import("./schedules/schedule-form/schedule-form"), name: 'schedule.edit', props: true},
    {path: '/smartphones', component: () => import("./client-apps/client-apps-page")},
    {path: "*", component: () => import("./common/pages/error-404"), meta: {bodyClass: 'red'}}
];
