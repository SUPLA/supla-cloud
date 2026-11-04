import {mount, flushPromises} from '@vue/test-utils';
import {reactive} from 'vue';
import {createTestingPinia} from '@pinia/testing';
import {api} from '@/api/api';
import DeviceDetailsTabs from '@/devices/details/device-details-tabs.vue';
import SafetyPage from '@/account/safety/safety-page.vue';
import ResourceAccessPage from '@/devices/details/resource-access-page.vue';
import ServerPermissions from '@/devices/details/server-permissions.vue';
import routes from '@/router/routes';
import {createMemoryHistory, createRouter} from 'vue-router';

const route = reactive({name: 'device.details', params: {id: 10}, query: {}});
const replace = vi.fn();
vi.mock('vue-router', async (importOriginal) => ({...(await importOriginal()), useRoute: () => route, useRouter: () => ({replace})}));
const device = {id: 10, locked: false, relationsCount: {managedNotifications: 0}};
const linkStub = {props: ['to'], template: '<a :data-route="to.name"><slot/></a>'};
const options = () => ({
  plugins: [createTestingPinia()],
  mocks: {$route: route},
  directives: {title: () => {}},
  stubs: {'router-link': linkStub, RouterView: true},
});
const probe = (permissions, requests) =>
  vi.spyOn(api, 'get').mockImplementation((endpoint) =>
    Promise.resolve({
      body: endpoint.includes('/permissions')
        ? {items: permissions}
        : endpoint.includes('status=PENDING')
          ? requests.filter((r) => r.status === 'PENDING')
          : requests,
    })
  );

afterEach(() => {
  vi.restoreAllMocks();
  replace.mockReset();
  route.name = 'device.details';
  route.params = {id: 10};
  route.query = {};
});

it.each([false, true])('hides empty Resource access for a device (locked=%s)', async (locked) => {
  probe([], []);
  const page = mount(DeviceDetailsTabs, {props: {device: {...device, locked}}, global: options()});
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(false);
  await flushPromises();
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(false);
  expect(page.find('a[data-route="device.details"]').exists()).toBe(true);
  page.unmount();
});

it.each([
  [[{id: 20}], []],
  [[], [{id: 1, status: 'REJECTED'}]],
])('shows device Resource access for existing permissions or request history', async (permissions, requests) => {
  const get = probe(permissions, requests);
  const page = mount(DeviceDetailsTabs, {props: {device}, global: options()});
  await flushPromises();
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(true);
  expect(get).toHaveBeenCalledWith('resource-access/permissions?limit=1&deviceId=10', {skipErrorHandler: true});
  expect(get).toHaveBeenCalledWith('resource-access?limit=1&deviceId=10', {skipErrorHandler: true});
  page.unmount();
});

it('ignores old presence responses after switching to another device', async () => {
  const pending = [];
  const get = vi.spyOn(api, 'get').mockImplementation(() => new Promise((resolve) => pending.push(resolve)));
  const page = mount(DeviceDetailsTabs, {props: {device}, global: options()});
  await page.setProps({device: {...device, id: 11}});
  pending[3]({body: {items: []}});
  pending[4]({body: []});
  pending[5]({body: []});
  await flushPromises();
  pending[0]({body: {items: [{id: 20}]}});
  pending[1]({body: [{id: 1}]});
  pending[2]({body: [{id: 1, status: 'PENDING'}]});
  await flushPromises();
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(false);
  expect(page.find('.requests-pending-indicator').exists()).toBe(false);
  expect(get).toHaveBeenCalledWith('resource-access?limit=1&deviceId=11', {skipErrorHandler: true});
  page.unmount();
});

it.each([false, true])('shows the Security Resource access tab only for a nonempty account (entries=%s)', async (present) => {
  const get = probe(present ? [{id: 20}] : [], []);
  const page = mount(SafetyPage, {global: options()});
  await flushPromises();
  expect(page.find('a[data-route="safety.resourceAccess"]').exists()).toBe(present);
  expect(page.find('a[data-route="safety.log"]').exists()).toBe(true);
  expect(get).toHaveBeenCalledWith('resource-access/permissions?limit=1', {skipErrorHandler: true});
  page.unmount();
});

it('shares presence with the device page and keeps known tabs when refresh fails', async () => {
  route.name = 'device.resource-access';
  const get = probe([{id: 20}], []);
  const global = options();
  global.stubs.RouterView = {props: ['device'], components: {ResourceAccessPage}, template: '<ResourceAccessPage :device="device" />'};
  const page = mount(DeviceDetailsTabs, {props: {device}, global});
  await flushPromises();
  expect(get.mock.calls.filter(([endpoint]) => endpoint === 'resource-access/permissions?limit=1&deviceId=10')).toHaveLength(1);
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(true);
  get.mockRejectedValue({status: 503});
  page.findComponent(ServerPermissions).vm.$emit('updated');
  await flushPromises();
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(true);
  expect(replace).not.toHaveBeenCalled();
  page.unmount();
});

it.each([
  [DeviceDetailsTabs, 'device.resource-access'],
  [SafetyPage, 'safety.resourceAccess'],
])('shares the pending indicator with the parent Resource access tab (%s)', async (component, target) => {
  route.name = target;
  let pending = 3;
  const get = vi.spyOn(api, 'get').mockImplementation((endpoint) => {
    if (endpoint.includes('/permissions')) return Promise.resolve({body: {items: [{id: 20}], nextCursor: null}});
    return Promise.resolve({body: endpoint.includes('status=PENDING') ? Array.from({length: pending}, (_, id) => ({id})) : [{id: 1}]});
  });
  const global = options();
  global.stubs.RouterView = {props: ['device'], components: {ResourceAccessPage}, template: '<ResourceAccessPage :device="device" />'};
  const page = mount(component, {props: component === DeviceDetailsTabs ? {device} : {}, global});
  await flushPromises();
  const link = () => page.find(`a[data-route="${target}"]`);
  expect(link().find('.requests-pending-indicator').text()).toMatch(/^3\b/);
  expect(page.findComponent(ResourceAccessPage).find('.requests-pending-indicator').text()).toMatch(/^3\b/);
  expect(get.mock.calls.filter(([endpoint]) => endpoint.includes('status=PENDING'))).toHaveLength(1);
  pending = 0;
  page.findComponent(ServerPermissions).vm.$emit('updated');
  await flushPromises();
  expect(link().exists()).toBe(true);
  expect(page.find('.requests-pending-indicator').exists()).toBe(false);
  page.unmount();
});

it('counts every pending page and retains the last complete count when a later page fails', async () => {
  route.name = 'device.resource-access';
  let failLastPage = false;
  const get = vi.spyOn(api, 'get').mockImplementation((endpoint) => {
    if (endpoint.includes('/permissions')) return Promise.resolve({body: {items: [{id: 20}], nextCursor: null}});
    if (!endpoint.includes('status=PENDING')) return Promise.resolve({body: [{id: 1}]});
    if (endpoint.includes('beforeId=100')) {
      return failLastPage ? Promise.reject({status: 503}) : Promise.resolve({body: [{id: 99}, {id: 98}]});
    }
    return Promise.resolve({body: Array.from({length: 50}, (_, i) => ({id: 150 - i})), headers: new Headers({'X-Next-Cursor': '100'})});
  });
  const global = options();
  global.stubs.RouterView = {props: ['device'], components: {ResourceAccessPage}, template: '<ResourceAccessPage :device="device" />'};
  const page = mount(DeviceDetailsTabs, {props: {device}, global});
  await flushPromises();
  const badge = () => page.find('a[data-route="device.resource-access"] .requests-pending-indicator');
  expect(badge().text()).toMatch(/^52\b/);
  expect(get).toHaveBeenCalledWith('resource-access?status=PENDING&limit=50&deviceId=10&beforeId=100', {skipErrorHandler: true});
  failLastPage = true;
  page.findComponent(ServerPermissions).vm.$emit('updated');
  await flushPromises();
  expect(badge().text()).toMatch(/^52\b/);
  page.unmount();
});

it('waits for the current Requests probe before hiding Resource access during refresh', async () => {
  route.name = 'device.resource-access';
  route.query = {view: 'permissions'};
  const get = probe([{id: 20}], []);
  const global = options();
  global.stubs.RouterView = {props: ['device'], components: {ResourceAccessPage}, template: '<ResourceAccessPage :device="device" />'};
  const page = mount(DeviceDetailsTabs, {props: {device}, global});
  await flushPromises();
  let resolveRequests;
  get.mockImplementation((endpoint) => {
    if (endpoint === 'resource-access?limit=1&deviceId=10') return new Promise((resolve) => (resolveRequests = resolve));
    return Promise.resolve({body: endpoint.includes('/permissions') ? {items: []} : []});
  });
  page.findComponent(ServerPermissions).vm.$emit('updated');
  await flushPromises();
  expect(replace).not.toHaveBeenCalled();
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(true);
  resolveRequests({body: [{id: 1, status: 'ACCEPTED', permissions: 1}]});
  await flushPromises();
  expect(replace).not.toHaveBeenCalled();
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(true);
  expect(page.findComponent(ResourceAccessPage).find('h4').text()).toBe('Requests');
  page.unmount();
});

it.each([
  [DeviceDetailsTabs, 'device.resource-access', undefined],
  [DeviceDetailsTabs, 'device.resource-access', 503],
  [SafetyPage, 'safety.resourceAccess', undefined],
  [SafetyPage, 'safety.resourceAccess', 503],
])('keeps Resource access reachable after the initial probe fails (%s, %s, status=%s)', async (component, target, status) => {
  vi.spyOn(api, 'get').mockRejectedValue({status});
  const page = mount(component, {props: component === DeviceDetailsTabs ? {device} : {}, global: options()});
  await flushPromises();
  expect(page.find(`a[data-route="${target}"]`).exists()).toBe(true);
  expect(replace).not.toHaveBeenCalled();
  page.unmount();
});

it.each([
  ['permissions', true],
  ['permissions', false],
  ['requests', true],
  ['requests', false],
])('allows retry after the first %s probe fails (entries after retry=%s)', async (view, present) => {
  route.name = 'device.resource-access';
  route.query = {view};
  let unavailable = true;
  vi.spyOn(api, 'get').mockImplementation((endpoint) => {
    const permissions = endpoint.includes('/permissions');
    if (permissions === (view === 'permissions') && unavailable) return Promise.reject({status: 503});
    const entries = present && permissions === (view === 'permissions') ? [{id: 1, status: 'ACCEPTED', permissions: 1}] : [];
    return Promise.resolve({body: permissions ? {items: entries, nextCursor: null} : entries});
  });
  const global = options();
  global.stubs.RouterView = {props: ['device'], components: {ResourceAccessPage}, template: '<ResourceAccessPage :device="device" />'};
  const page = mount(DeviceDetailsTabs, {props: {device}, global});
  await flushPromises();
  const content = page.findComponent(ResourceAccessPage);
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(true);
  expect(content.findAll('.nav-tabs a').map((link) => link.text().trim())).toEqual([view === 'permissions' ? 'Permissions' : 'Requests']);
  expect(content.find('[role="alert"]').text()).toContain('Error when communicating the server');
  expect(replace).not.toHaveBeenCalled();
  unavailable = false;
  await content
    .findAll('button')
    .find((button) => button.text().trim() === 'Refresh')
    .trigger('click');
  await flushPromises();
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(present);
  if (present) {
    expect(page.find('[role="alert"]').exists()).toBe(false);
    expect(replace).not.toHaveBeenCalled();
  } else {
    expect(replace).toHaveBeenCalledWith({name: 'device.channels', params: {id: 10}});
  }
  page.unmount();
});

it('ignores a failed old probe after a different device is confirmed empty', async () => {
  const pending = [];
  vi.spyOn(api, 'get').mockImplementation((endpoint) => {
    if (endpoint.endsWith('deviceId=10')) return new Promise((resolve, reject) => pending.push({resolve, reject}));
    return Promise.resolve({body: endpoint.includes('/permissions') ? {items: []} : []});
  });
  const page = mount(DeviceDetailsTabs, {props: {device}, global: options()});
  await page.setProps({device: {...device, id: 11}});
  await flushPromises();
  pending.forEach(({reject}) => reject({status: 503}));
  await flushPromises();
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(false);
  page.unmount();
});

it.each([false, true])('leaves an empty device Resource access page (locked=%s)', async (locked) => {
  route.name = 'device.resource-access';
  probe([], []);
  const global = options();
  global.stubs.RouterView = {props: ['device'], components: {ResourceAccessPage}, template: '<ResourceAccessPage :device="device" />'};
  const page = mount(DeviceDetailsTabs, {props: {device: {...device, locked}}, global});
  await flushPromises();
  expect(page.find('a[data-route="device.resource-access"]').exists()).toBe(false);
  expect(replace).toHaveBeenCalledWith({name: locked ? 'device.unlock' : 'device.channels', params: {id: 10}});
  page.unmount();
});

it('places the account-wide page under Security and redirects the previous URL with its selected request', async () => {
  const router = createRouter({history: createMemoryHistory(), routes});
  const resolved = router.resolve({name: 'safety.resourceAccess', query: {view: 'requests', decisionId: '42'}});
  expect(resolved.path).toBe('/security/resource-access');
  expect(resolved.matched[0].path).toBe('/security');
  await router.push('/devices/resource-access?view=requests&decisionId=42');
  expect(router.currentRoute.value.name).toBe('safety.resourceAccess');
  expect(router.currentRoute.value.query).toEqual({view: 'requests', decisionId: '42'});
});
