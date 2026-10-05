// SPDX-FileCopyrightText: AC SOFTWARE SP. Z O.O.
// SPDX-License-Identifier: GPL-2.0-or-later
import {flushPromises} from '@vue/test-utils';
import {createApp, h, nextTick} from 'vue';
import {createTestingPinia} from '@pinia/testing';
import ChargersListPage from '@/account/integrations/ocpp/chargers-list-page.vue';
import {api} from '@/api/api';

const {route, replace} = vi.hoisted(() => ({route: {query: {}}, replace: vi.fn()}));
vi.mock('@/api/api', () => ({api: {get: vi.fn()}}));
vi.mock('@/common/gui/loaders/loading-cover.vue', () => ({
  default: {
    compatConfig: {MODE: 3},
    render() {
      return h('div', this.$slots.default?.());
    },
  },
}));
vi.mock('@/account/integrations/ocpp/charger-configuration-modal.vue', () => ({
  default: {
    compatConfig: {MODE: 3},
    props: ['station'],
    emits: ['close'],
    render() {
      return h('button', {'data-testid': 'charger-modal', onClick: () => this.$emit('close')}, this.station?.id);
    },
  },
}));
vi.mock('vue-i18n', async (importOriginal) => ({...(await importOriginal()), useI18n: () => ({t: (key) => key})}));
vi.mock('vue-router', async (importOriginal) => ({
  ...(await importOriginal()),
  useRoute: () => route,
  useRouter: () => ({replace}),
}));

const station = {id: 7, iodeviceId: 8, name: 'Garage', chargePointIdentity: 'U1-ABCD'};

async function mountPage(deviceId, enabled = true) {
  route.query = {deviceId, source: 'device'};
  api.get.mockResolvedValue({body: {enabled, stations: [station]}});
  const element = document.createElement('div');
  document.body.appendChild(element);
  const app = createApp(ChargersListPage);
  app.use(createTestingPinia());
  app.config.globalProperties.$t = (key) => key;
  app.component('router-link', {compatConfig: {MODE: 3}, render: () => h('a')});
  app.mount(element);
  await flushPromises();
  await nextTick();
  return {
    element,
    unmount: () => {
      app.unmount();
      element.remove();
    },
  };
}

describe('OCPP charger deep link', () => {
  beforeEach(() => vi.clearAllMocks());

  it('opens the charger selected by device ID and clears the query when closed', async () => {
    const {element, unmount} = await mountPage('8');
    const modal = element.querySelector('[data-testid="charger-modal"]');
    expect(modal.textContent).toBe('7');

    modal.click();
    await flushPromises();
    expect(element.querySelector('[data-testid="charger-modal"]')).toBeNull();
    expect(replace).toHaveBeenCalledWith({name: 'integrations.ocpp', query: {source: 'device'}});
    unmount();
  });

  it.each([
    ['a different device', '9', true],
    ['an invalid ID', '8x', true],
    ['a disabled integration', '8', false],
  ])('does not open a charger for %s', async (_description, deviceId, enabled) => {
    const {element, unmount} = await mountPage(deviceId, enabled);
    expect(element.querySelector('[data-testid="charger-modal"]')).toBeNull();
    unmount();
  });
});
