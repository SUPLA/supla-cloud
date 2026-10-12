import {mount, flushPromises} from '@vue/test-utils';
import {reactive} from 'vue';
import ServerPermissions from '@/devices/details/server-permissions.vue';
import ResourceAccessPage from '@/devices/details/resource-access-page.vue';
import ResourceAccess from '@/devices/details/resource-access.vue';
import {api} from '@/api/api';

const route = reactive({name: 'devices.resource-access', params: {}, query: {}});
vi.mock('vue-router', async (importOriginal) => ({...(await importOriginal()), useRoute: () => route}));
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
  route.query = {};
});

it('defaults to Permissions and keeps Requests accessible in the same navigation', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({body: {items: [], nextCursor: null}});
  const page = mount(ResourceAccessPage, {global: {stubs}});
  await flushPromises();
  expect(page.findComponent(ServerPermissions).exists()).toBe(true);
  expect(page.findComponent(ResourceAccess).exists()).toBe(false);
  route.query = {view: 'requests', decisionId: '42'};
  vi.mocked(api.get).mockResolvedValue({body: []});
  await flushPromises();
  expect(page.findComponent(ResourceAccess).props('decisionId')).toBe('42');
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
  const get = vi
    .spyOn(api, 'get')
    .mockImplementation((endpoint) =>
      Promise.resolve({body: endpoint.includes('status=PENDING') ? (pending ? [{id: 1}] : []) : {items: [], nextCursor: null}})
    );
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
