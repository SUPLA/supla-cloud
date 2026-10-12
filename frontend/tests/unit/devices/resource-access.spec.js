import {mount, flushPromises} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import ResourceAccess from '@/devices/details/resource-access.vue';
import {api} from '@/api/api';
import {permissionLabels, confirmationText} from '@/devices/details/resource-access-labels';

const accepted = {
  id: 1,
  canManage: true,
  status: 'ACCEPTED',
  revision: 1,
  permissions: 1,
  acceptedManualPermissions: 1,
  independentPermissions: 1,
  sourceChannelId: 20,
  destinationDeviceId: 10,
};
const pending = {...accepted, id: 2, status: 'PENDING', permissions: 2, canApprove: true};
const mountPage = (props = {}) =>
  mount(ResourceAccess, {
    props,
    global: {
      stubs: {
        'router-link': {name: 'RouterLink', props: ['to'], template: '<a :data-route="to.name" :data-id="to.params.id"><slot/></a>'},
        ModalConfirm: {
          name: 'ModalConfirm',
          props: ['loading'],
          emits: ['confirm', 'cancel'],
          template: '<div><slot/><button @click="$emit(\'confirm\')">confirm</button></div>',
        },
      },
    },
  });

afterEach(() => {
  vi.restoreAllMocks();
  vi.unstubAllGlobals();
  setActivePinia(undefined);
});
it('a selected accepted request stays separate and changing selection closes an old confirmation', async () => {
  vi.spyOn(api, 'get').mockImplementation((endpoint) => Promise.resolve({body: endpoint.endsWith('/2') ? pending : accepted}));
  const page = mountPage({decisionId: '2'});
  await flushPromises();
  expect(page.findAll('tbody tr')).toHaveLength(1);
  await page.find('.btn-green').trigger('click');
  expect(page.findComponent({name: 'ModalConfirm'}).exists()).toBe(true);
  await page.setProps({decisionId: '1'});
  await flushPromises();
  expect(page.findComponent({name: 'ModalConfirm'}).exists()).toBe(false);
  expect(page.findAll('tbody tr')).toHaveLength(1);
  expect(page.text()).toContain('Selected request: ID1');
  expect(page.find('.btn-green').exists()).toBe(false);
  page.unmount();
});
it('shows separate accepted and pending records with exact rights, without per-event inputs', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({body: [accepted, pending]});
  const page = mountPage();
  await flushPromises();
  expect(page.findAll('tbody tr')).toHaveLength(2);
  expect(page.text()).toContain('CONTROL (includes READ)');
  expect(page.findAll('input')).toHaveLength(0);
  expect(page.text()).toContain('offline source');
  page.unmount();
});
it('keeps device B entries when the earlier device A response arrives last', async () => {
  let resolveA, resolveB;
  const get = vi
    .spyOn(api, 'get')
    .mockReturnValueOnce(
      new Promise((resolve) => {
        resolveA = resolve;
      })
    )
    .mockReturnValueOnce(
      new Promise((resolve) => {
        resolveB = resolve;
      })
    );
  const page = mountPage({device: {id: 10}});
  await page.setProps({device: {id: 11}});
  resolveB({body: [{...pending, destinationDeviceId: 11, destinationName: 'Device B'}]});
  await flushPromises();
  resolveA({body: [{...pending, destinationName: 'Device A'}]});
  await flushPromises();
  expect(get).toHaveBeenNthCalledWith(1, 'resource-access?deviceId=10');
  expect(get).toHaveBeenNthCalledWith(2, 'resource-access?deviceId=11');
  expect(page.text()).toContain('Device B');
  expect(page.text()).not.toContain('Device A');
  page.unmount();
});
it('clears the old list and confirmation while the next device is loading', async () => {
  let resolveB;
  vi.spyOn(api, 'get')
    .mockResolvedValueOnce({body: [pending]})
    .mockReturnValueOnce(
      new Promise((resolve) => {
        resolveB = resolve;
      })
    );
  const page = mountPage({device: {id: 10}});
  await flushPromises();
  await page.find('.btn-green').trigger('click');
  expect(page.findComponent({name: 'ModalConfirm'}).exists()).toBe(true);
  await page.setProps({device: {id: 11}});
  expect(page.findAll('tbody tr')).toHaveLength(0);
  expect(page.findComponent({name: 'ModalConfirm'}).exists()).toBe(false);
  resolveB({body: []});
  await flushPromises();
  page.unmount();
});
it('does not let completion of device A mutation clear the new device B confirmation', async () => {
  let resolvePost;
  const get = vi
    .spyOn(api, 'get')
    .mockResolvedValueOnce({body: [pending]})
    .mockResolvedValueOnce({body: [{...pending, id: 3, destinationDeviceId: 11, destinationName: 'Device B'}]});
  vi.spyOn(api, 'post').mockReturnValueOnce(
    new Promise((resolve) => {
      resolvePost = resolve;
    })
  );
  const page = mountPage({device: {id: 10}});
  await flushPromises();
  await page.find('.btn-green').trigger('click');
  await page.findAll('button').at(-1).trigger('click');
  await page.setProps({device: {id: 11}});
  await flushPromises();
  await page.find('.btn-green').trigger('click');
  resolvePost({status: 204});
  await flushPromises();
  expect(get).toHaveBeenCalledTimes(2);
  expect(page.findComponent({name: 'ModalConfirm'}).text()).toContain('Device B');
  page.unmount();
});
it('submits exactly the confirmation snapshot and refreshes safely after a conflict', async () => {
  const get = vi.spyOn(api, 'get').mockResolvedValue({body: [pending]});
  const post = vi.spyOn(api, 'post').mockRejectedValue({status: 409});
  const page = mountPage();
  await flushPromises();
  await page.find('.btn-green').trigger('click');
  pending.revision = 99;
  await page.findAll('button').at(-1).trigger('click');
  await flushPromises();
  expect(post).toHaveBeenCalledWith('resource-access/2/approve', {revision: 1, status: 'PENDING', permissions: 2}, {skipErrorHandler: [409]});
  expect(get).toHaveBeenCalledTimes(2);
  expect(page.find('[role="alert"]').text()).toContain('request changed');
  pending.revision = 1;
  page.unmount();
});
it('explains remove differently for each state and ACTION covers all physical events', () => {
  expect(confirmationText('ACCEPTED', 'remove')).toContain('Independent permissions remain');
  expect(confirmationText('REJECTED', 'remove')).toContain('Remove this rejection');
  expect(confirmationText('PENDING', 'remove')).toContain('Cancel this pending');
  expect(permissionLabels(4)).toEqual(['ACTION (all supported physical events)']);
});
it('offers no mutation controls for a read-only token', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({body: [{...pending, canManage: false, canApprove: false}]});
  const page = mountPage();
  await flushPromises();
  expect(page.find('.btn-green').exists()).toBe(false);
  expect(page.find('.btn-red').exists()).toBe(false);
  expect(page.findAll('.resource-access-actions button')).toHaveLength(0);
  page.unmount();
});

it('shows existing access only when present and keeps pending permissions separate', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({body: [{...pending, acceptedManualPermissions: 1, independentPermissions: 0}]});
  const page = mountPage();
  await flushPromises();
  const row = page.find('tbody tr');
  expect(row.text()).toContain('CONTROL (includes READ)');
  expect(row.text()).toContain('Already accepted: READ');
  expect(row.text()).not.toContain('Access from configuration or policy');
  page.unmount();
});

it('uses text statuses and links to the requesting device, source device and channel in the requested column order', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({
    body: [
      {
        ...pending,
        destinationName: 'DIY',
        sourceDeviceName: 'Sensors',
        sourceDeviceId: 12,
        sourceCaption: 'Thermometer',
        sourceFunctionId: 40,
        requestedAt: '2026-10-12T08:00:00+00:00',
        lastDecisionAt: '2026-10-12T09:00:00+00:00',
      },
    ],
  });
  const page = mountPage();
  await flushPromises();
  const headings = page.findAll('thead th').map((cell) => cell.text());
  expect(headings.map((text, index) => text.startsWith(['Requesting device', 'Permissions', 'Status', 'Source device and channel', 'Actions'][index]))).toEqual(
    [true, true, true, true, true]
  );
  expect(page.find('.label').exists()).toBe(false);
  expect(page.find('tbody .text-warning').text()).toBe('Pending decision');
  expect(page.find('a[data-route="device.details"][data-id="10"]').exists()).toBe(true);
  expect(page.find('a[data-route="device.details"][data-id="12"]').exists()).toBe(true);
  expect(page.find('a[data-route="channel"][data-id="20"]').exists()).toBe(true);
  expect(page.find('time').attributes('datetime')).toBe('2026-10-12T09:00:00+00:00');
  expect(page.find('.table-responsive').exists()).toBe(false);
  page.unmount();
});
it('sorts each data column in both directions and offers sorting on narrow screens', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({
    body: [
      {...pending, id: 2, destinationName: 'Zulu', sourceCaption: 'Z source', lastDecisionAt: '2026-10-12T10:00:00Z'},
      {...accepted, id: 1, destinationName: 'Alpha', sourceCaption: 'A source', permissions: 4, status: 'REJECTED', lastDecisionAt: '2026-10-12T08:00:00Z'},
      {...accepted, id: 3, destinationName: 'Alpha', sourceCaption: 'M source', lastDecisionAt: null},
    ],
  });
  const page = mountPage();
  await flushPromises();
  const names = () => page.findAll('tbody tr').map((row) => row.find('td').text());
  await page.find('[data-sort="destination"]').trigger('click');
  expect(names()[0]).toContain('Zulu');
  const sources = () => page.findAll('tbody tr').map((row) => ['A', 'M', 'Z'].find((letter) => row.findAll('td')[3].text().includes(`${letter} source`)));
  for (const [key, ascending, descending] of [
    ['permissions', ['A', 'Z', 'M'], ['M', 'Z', 'A']],
    ['status', ['M', 'Z', 'A'], ['A', 'Z', 'M']],
    ['source', ['A', 'M', 'Z'], ['Z', 'M', 'A']],
    ['lastDecision', ['A', 'Z', 'M'], ['Z', 'A', 'M']],
  ]) {
    await page.find(`[data-sort="${key}"]`).trigger('click');
    expect(sources()).toEqual(ascending);
    await page.find(`[data-sort="${key}"]`).trigger('click');
    expect(sources()).toEqual(descending);
  }
  await page.find('.resource-access-sort select').setValue('permissions');
  expect(page.find('.resource-access-sort select').element.value).toBe('permissions');
  expect(page.findAll('.resource-access-field-label')).toHaveLength(15);
  page.unmount();
});

it('shows requests made by a device separately from requests for its channels', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({
    body: [
      {...pending, id: 1, destinationDeviceId: 10, sourceDeviceId: 12},
      {...pending, id: 2, destinationDeviceId: 12, sourceDeviceId: 10},
      {...pending, id: 3, destinationDeviceId: 13, sourceDeviceId: 14},
    ],
  });
  const page = mountPage({device: {id: 10}});
  await flushPromises();
  const sections = page.findAll('section');
  expect(sections).toHaveLength(2);
  expect(sections[0].find('h4').text()).toContain('Requests made by this device');
  expect(sections[1].find('h4').text()).toContain("Requests for this device's channels");
  expect(sections.map((section) => section.findAll('tbody tr').length)).toEqual([1, 1]);
  expect(sections[0].find('tbody tr').text()).toContain('ID10');
  expect(sections[1].find('tbody tr').text()).toContain('ID12');
  const sortIds = page.findAll('.resource-access-sort select').map((select) => select.attributes('id'));
  expect(new Set(sortIds).size).toBe(2);
  page.unmount();
});

it('shows an uncertain mutation outcome after rejected fetch and lets the user refresh before retrying', async () => {
  setActivePinia(createPinia());
  const get = vi.spyOn(api, 'get').mockResolvedValue({body: [pending]});
  const fetch = vi.fn().mockRejectedValue(new TypeError('Failed to fetch'));
  vi.stubGlobal('fetch', fetch);
  const page = mountPage();
  await flushPromises();
  await page.find('.btn-green').trigger('click');
  await page.findAll('button').at(-1).trigger('click');
  await flushPromises();
  expect(fetch).toHaveBeenCalledOnce();
  const modal = page.findComponent({name: 'ModalConfirm'});
  expect(modal.find('[role="alert"]').text()).toContain('may have reached the server');
  expect(modal.props('loading')).toBe(false);
  get.mockResolvedValueOnce({body: [{...pending, status: 'ACCEPTED', canApprove: false}]});
  await modal
    .findAll('button')
    .find((button) => button.text() === 'Refresh request')
    .trigger('click');
  await flushPromises();
  expect(page.findComponent({name: 'ModalConfirm'}).exists()).toBe(false);
  expect(page.find('[role="alert"]').exists()).toBe(false);
  expect(page.find('.btn-green').exists()).toBe(false);
  page.unmount();
});

it('does not duplicate global HTTP error feedback with a network alert', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({body: [pending]});
  vi.spyOn(api, 'post').mockRejectedValue({status: 403});
  const page = mountPage();
  await flushPromises();
  await page.find('.btn-green').trigger('click');
  await page.findAll('button').at(-1).trigger('click');
  await flushPromises();
  expect(page.find('[role="alert"]').exists()).toBe(false);
  expect(page.findComponent({name: 'ModalConfirm'}).exists()).toBe(true);
  page.unmount();
});

it('offers retry after request-list network failure and discards errors from the previous device', async () => {
  vi.spyOn(api, 'get')
    .mockRejectedValueOnce(new TypeError('Failed to fetch'))
    .mockResolvedValue({body: [pending]});
  const page = mountPage();
  await flushPromises();
  expect(page.find('[role="alert"]').text()).toContain('Could not load requests');
  await page
    .findAll('button')
    .find((button) => button.text().trim() === 'Refresh')
    .trigger('click');
  await flushPromises();
  expect(page.find('[role="alert"]').exists()).toBe(false);
  expect(page.findAll('tbody tr')).toHaveLength(1);
  page.unmount();
});

it('uses stable request cursors and direct lookup for selected decisions', async () => {
  const get = vi
    .spyOn(api, 'get')
    .mockResolvedValueOnce({body: [{...pending, id: 100}], headers: new Headers({'X-Next-Cursor': '100'})})
    .mockResolvedValueOnce({body: [{...pending, id: 50}], headers: new Headers()})
    .mockResolvedValueOnce({body: [{...pending, id: 100}], headers: new Headers({'X-Next-Cursor': '100'})});
  const page = mountPage();
  await flushPromises();
  await page
    .findAll('button')
    .find((button) => button.text() === 'Next')
    .trigger('click');
  await flushPromises();
  expect(get).toHaveBeenLastCalledWith('resource-access?beforeId=100');
  await page
    .findAll('button')
    .find((button) => button.text() === 'Previous')
    .trigger('click');
  await flushPromises();
  expect(get).toHaveBeenLastCalledWith('resource-access');
  get.mockResolvedValueOnce({body: accepted});
  await page.setProps({decisionId: '1'});
  await flushPromises();
  expect(get).toHaveBeenLastCalledWith('resource-access/1');
  expect(page.findAll('tbody tr')).toHaveLength(1);
  page.unmount();
});

it('explains that approving a covered request creates manual access independent of configuration', async () => {
  vi.spyOn(api, 'get').mockResolvedValue({body: [{...pending, permissions: 1, independentPermissions: 1}]});
  const page = mountPage();
  await flushPromises();
  expect(page.find('tbody tr').text()).toContain('Already covered by configuration or policy');
  await page.find('.btn-green').trigger('click');
  expect(page.findComponent({name: 'ModalConfirm'}).text()).toContain('even if the configuration');
  page.unmount();
});
