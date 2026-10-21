import {mount, flushPromises} from '@vue/test-utils';
import {reactive} from 'vue';
import ServerPermissions from '@/devices/details/server-permissions.vue';
import ResourceAccessPage from '@/devices/details/resource-access-page.vue';
import ResourceAccess from '@/devices/details/resource-access.vue';
import {api} from '@/api/api';

const route = reactive({name: 'safety.resourceAccess', params: {}, query: {}});
const replace = vi.fn();
vi.mock('vue-router', async (importOriginal) => ({...(await importOriginal()), useRoute: () => route, useRouter: () => ({replace})}));
const entry = {
  associationId: '17',
  sourceDeviceId: 9,
  sourceDeviceName: 'Source',
  destinationDeviceId: 10,
  destinationName: 'Destination',
  resourceType: 1,
  resourceId: 20,
  sourceCaption: 'Temperature',
  sourceFunction: 'THERMOMETER',
  permissions: 1,
  originSummary: 'mixed',
};
const stubs = {
  'router-link': {
    props: ['to'],
    template: '<a :data-route="to.name" :data-id="to.params?.id" :data-view="to.query?.view" :data-decision="to.query?.decisionId"><slot/></a>',
  },
};
const mountList = (props) => mount(ServerPermissions, {props, global: {stubs}});
afterEach(() => {
  vi.restoreAllMocks();
  replace.mockReset();
  route.name = 'safety.resourceAccess';
  route.params = {};
  route.query = {};
});

it('defaults to Permissions and keeps Requests accessible in the same navigation', async () => {
  vi.spyOn(api, 'get').mockImplementation((endpoint) =>
    Promise.resolve({body: endpoint.includes('/permissions') ? {items: [entry], nextCursor: null} : [{id: 42, status: 'PENDING', permissions: 1}]})
  );
  const page = mount(ResourceAccessPage, {global: {stubs}});
  await flushPromises();
  expect(page.findComponent(ServerPermissions).exists()).toBe(true);
  expect(page.findComponent(ResourceAccess).exists()).toBe(false);
  route.query = {view: 'requests', decisionId: '42'};
  vi.mocked(api.get).mockResolvedValue({body: {id: 42, status: 'PENDING', permissions: 1}});
  await flushPromises();
  expect(page.findComponent(ResourceAccess).props('decisionId')).toBe('42');
  page.unmount();
});

it.each([
  ['safety.resourceAccess', {}, undefined],
  ['device.resource-access', {id: '10'}, {id: 10}],
])('returns from a removed request detail to the same Requests list (%s)', async (name, params, device) => {
  route.name = name;
  route.params = params;
  route.query = {view: 'requests', decisionId: '42'};
  vi.spyOn(api, 'get').mockImplementation((endpoint) =>
    Promise.resolve({
      body: endpoint.includes('/permissions')
        ? {items: [entry], nextCursor: null}
        : endpoint === 'resource-access/42'
          ? {id: 42, status: 'ACCEPTED', permissions: 1}
          : [{id: 42}],
    })
  );
  const page = mount(ResourceAccessPage, {props: {device}, global: {stubs}});
  await flushPromises();
  page.findComponent(ResourceAccess).vm.$emit('close-selected-request');
  expect(replace).toHaveBeenCalledWith({name, params, query: {view: 'requests'}});
  page.unmount();
});
it('renders effective rights, scope, links and offline copy without raw grant actions', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({
    body: {items: [entry, {...entry, resourceType: 2, resourceId: 9, sourceCaption: null, permissions: 2, originSummary: 'other'}], nextCursor: null},
  });
  const page = mountList();
  await flushPromises();
  expect(page.findAll('tbody tr')).toHaveLength(2);
  expect(page.text()).toContain('Mixed');
  expect(page.text()).toContain('Whole device');
  expect(page.text()).toContain('CONTROL (includes READ)');
  expect(page.text()).toContain('offline source');
  expect(page.find('a[data-route="channel"]').attributes('data-id')).toBe('20');
  expect(page.text()).not.toContain('Revoke');
  expect(page.text()).not.toContain('Approve');
  page.unmount();
});
it('pages and filters on the server, including both roles of a selected Device', async () => {
  const get = vi.spyOn(api, 'get').mockResolvedValue({body: {items: [entry], nextCursor: '17:0'}});
  const page = mountList({device: {id: 10}});
  await flushPromises();
  expect(get).toHaveBeenLastCalledWith('resource-access/permissions?limit=25&deviceId=10');
  await page.findAll('select')[0].setValue('1');
  await page.findAll('select')[1].setValue('configuration');
  await page.find('form').trigger('submit');
  await flushPromises();
  expect(get).toHaveBeenLastCalledWith('resource-access/permissions?limit=25&permission=1&origin=configuration&deviceId=10');
  await page
    .findAll('button')
    .find((b) => b.text() === 'Next')
    .trigger('click');
  await flushPromises();
  expect(get.mock.lastCall[0]).toContain('cursor=17%3A0');
  await page
    .findAll('button')
    .find((b) => b.text() === 'Previous')
    .trigger('click');
  await flushPromises();
  expect(get.mock.lastCall[0]).not.toContain('cursor=');
  page.unmount();
});
it('details enumerate config and manual contributions with the correct editor and accepted request links', async () => {
  const get = vi
    .spyOn(api, 'get')
    .mockResolvedValueOnce({body: {items: [entry], nextCursor: null}})
    .mockResolvedValueOnce({
      body: {
        ...entry,
        origins: [
          {
            originType: 1,
            originId: '25601',
            permissions: 1,
            kind: 'configuration',
            field: {label: 'Pump switch', slot: null},
            configuringChannel: {id: 100, caption: 'HVAC'},
          },
          {originType: 2, originId: '42', permissions: 2, kind: 'user-approved', decisionId: '42'},
          {originType: 9, originId: '18446744073709551615', permissions: 1, kind: 'other'},
        ],
        nextOriginCursor: '9:18446744073709551615',
      },
    });
  const page = mountList();
  await flushPromises();
  await page.find('tbody button').trigger('click');
  await flushPromises();
  expect(get).toHaveBeenLastCalledWith('resource-access/permissions/17/1/20');
  expect(page.findAll('.list-group-item')).toHaveLength(3);
  expect(page.find('.permission-details a[data-route="channel"]').attributes('data-id')).toBe('100');
  const link = page.find('.permission-details a[data-view="requests"]');
  expect(link.attributes('data-decision')).toBe('42');
  expect(page.text()).toContain('Removing one origin');
  expect(page.text()).toContain('18446744073709551615');
  get.mockResolvedValueOnce({body: {...entry, origins: [{originType: 10, originId: '1', permissions: 1, kind: 'other'}], nextOriginCursor: null}});
  await page
    .findAll('button')
    .find((b) => b.text() === 'More origins')
    .trigger('click');
  await flushPromises();
  expect(page.findAll('.list-group-item')).toHaveLength(4);
  page.unmount();
});
it('discard old device list and detail responses after the device changes', async () => {
  let resolveOld, resolveDetail;
  const get = vi
    .spyOn(api, 'get')
    .mockReturnValueOnce(new Promise((resolve) => (resolveOld = resolve)))
    .mockResolvedValueOnce({body: {items: [{...entry, sourceCaption: 'Device B'}], nextCursor: null}});
  const page = mountList({device: {id: 10}});
  await page.setProps({device: {id: 11}});
  await flushPromises();
  resolveOld({body: {items: [entry], nextCursor: null}});
  await flushPromises();
  expect(page.text()).toContain('Device B');
  expect(page.text()).not.toContain('Temperature');
  get.mockReturnValueOnce(new Promise((resolve) => (resolveDetail = resolve)));
  await page.find('tbody button').trigger('click');
  get.mockResolvedValueOnce({body: {items: [], nextCursor: null}});
  await page.setProps({device: {id: 12}});
  await flushPromises();
  resolveDetail({body: {...entry, origins: []}});
  await flushPromises();
  expect(page.find('.permission-details').exists()).toBe(false);
  expect(page.findAll('tbody tr')).toHaveLength(0);
  page.unmount();
});

it('shows retryable network failure without presenting an empty permissions result', async () => {
  const get = vi
    .spyOn(api, 'get')
    .mockRejectedValueOnce(new TypeError('Failed to fetch'))
    .mockResolvedValueOnce({body: {items: [entry], nextCursor: null}});
  const page = mountList();
  await flushPromises();
  expect(page.find('[role="alert"]').text()).toContain('Could not load permissions');
  expect(page.findComponent({name: 'EmptyListPlaceholder'}).exists()).toBe(false);
  await page
    .findAll('button')
    .find((button) => button.text() === 'Refresh')
    .trigger('click');
  await flushPromises();
  expect(get).toHaveBeenCalledTimes(2);
  expect(page.find('[role="alert"]').exists()).toBe(false);
  expect(page.findAll('tbody tr')).toHaveLength(1);
  page.unmount();
});

it('signals pending decisions in Requests and refreshes the indicator after a decision', async () => {
  let pending = true;
  const get = vi.spyOn(api, 'get').mockImplementation((endpoint) => {
    if (endpoint.includes('/permissions')) return Promise.resolve({body: {items: [entry], nextCursor: null}});
    return Promise.resolve({body: endpoint.includes('status=PENDING') ? (pending ? [{id: 1}] : []) : [{id: 1}]});
  });
  const page = mount(ResourceAccessPage, {props: {device: {id: 10}}, global: {stubs}});
  await flushPromises();
  expect(page.find('.requests-pending-indicator').exists()).toBe(true);
  expect(page.find('.requests-pending-indicator').attributes('title')).toContain('need your attention');
  expect(get).toHaveBeenCalledWith('resource-access?status=PENDING&limit=1&deviceId=10', {skipErrorHandler: true});
  pending = false;
  page.findComponent(ServerPermissions).vm.$emit('updated');
  await flushPromises();
  expect(page.find('.requests-pending-indicator').exists()).toBe(false);
  page.unmount();
});

it.each([
  [true, false, 'requests', 'permissions'],
  [false, true, 'permissions', 'requests'],
  [true, true, 'permissions', 'permissions'],
  [false, false, 'permissions', null],
])('shows only populated tabs (permissions=%s, requests=%s)', async (hasPermissions, hasRequests, selected, visible) => {
  route.query = {view: selected};
  vi.spyOn(api, 'get').mockImplementation((endpoint) => {
    if (endpoint.includes('/permissions')) return Promise.resolve({body: {items: hasPermissions ? [entry] : [], nextCursor: null}});
    return Promise.resolve({body: hasRequests ? [{id: 1, status: 'REJECTED', permissions: 1}] : []});
  });
  const page = mount(ResourceAccessPage, {global: {stubs}});
  await flushPromises();
  expect(page.find('a[data-view="permissions"]').exists()).toBe(hasPermissions);
  expect(page.find('a[data-view="requests"]').exists()).toBe(hasRequests);
  expect(page.findComponent(ServerPermissions).exists()).toBe(visible === 'permissions');
  expect(page.findComponent(ResourceAccess).exists()).toBe(visible === 'requests');
  if (visible === null) expect(replace).toHaveBeenCalledWith({name: 'safety.log'});
  page.unmount();
});

it('keeps Permissions available when a filter returns no rows', async () => {
  vi.spyOn(api, 'get').mockImplementation((endpoint) =>
    Promise.resolve({
      body: endpoint.includes('/permissions') ? {items: endpoint.includes('limit=1&') || endpoint.endsWith('limit=1') ? [entry] : [], nextCursor: null} : [],
    })
  );
  const page = mount(ResourceAccessPage, {global: {stubs}});
  await flushPromises();
  const list = page.findComponent(ServerPermissions);
  expect(list.findAll('tbody tr')).toHaveLength(0);
  list.vm.$emit('updated');
  await flushPromises();
  expect(page.find('a[data-view="permissions"]').exists()).toBe(true);
  expect(replace).not.toHaveBeenCalled();
  page.unmount();
});

it('switches to Requests after the last permission disappears and keeps history visible', async () => {
  let hasPermissions = true;
  vi.spyOn(api, 'get').mockImplementation((endpoint) =>
    Promise.resolve({
      body: endpoint.includes('/permissions') ? {items: hasPermissions ? [entry] : [], nextCursor: null} : [{id: 1, status: 'ACCEPTED', permissions: 1}],
    })
  );
  const page = mount(ResourceAccessPage, {global: {stubs}});
  await flushPromises();
  hasPermissions = false;
  page.findComponent(ServerPermissions).vm.$emit('updated');
  await flushPromises();
  expect(page.find('a[data-view="permissions"]').exists()).toBe(false);
  expect(page.find('a[data-view="requests"]').exists()).toBe(true);
  expect(page.findComponent(ResourceAccess).exists()).toBe(true);
  expect(replace).not.toHaveBeenCalled();
  page.unmount();
});

it('shows server READ authorization for virtual sources without inventing a peer association', async () => {
  const stream = {
    ...entry,
    associationId: null,
    resourceKey: 'read-42',
    sourceIsVirtual: true,
    sourceDeviceName: 'Data sources',
    authorizationKind: 'SERVER_READ',
    originSummary: 'user-approved',
  };
  const get = vi
    .spyOn(api, 'get')
    .mockResolvedValueOnce({body: {items: [stream], nextCursor: null}})
    .mockResolvedValueOnce({body: {...stream, origins: [], nextOriginCursor: null}});
  const page = mountList();
  await flushPromises();
  expect(page.text()).toContain('Virtual / integration source');
  expect(page.find('a[data-route="device.details"][data-id="9"]').exists()).toBe(false);
  await page.find('tbody button').trigger('click');
  await flushPromises();
  expect(get).toHaveBeenLastCalledWith('resource-access/permissions/read-42/1/20');
  expect(page.text()).toContain('no peer association or source provisioning is required');
  page.unmount();
});
