import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {shallowMount} from '@vue/test-utils';
import {createPinia, setActivePinia} from 'pinia';
import ElectricityMeterParams from '@/channels/params/channel-params-electricity-meter.vue';
import AccordionItem from '@/common/gui/accordion/accordion-item.vue';
import ChannelDetailsPage from '@/channels/channel-details-page.vue';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {energyCostApi} from '@/api/energy-cost-api';
import EventBus from '@/common/event-bus';

beforeEach(() => setActivePinia(createPinia()));
afterEach(() => vi.restoreAllMocks());

describe('electricity meter basic cost settings', () => {
  it('hides the whole Costs section and restores it without changing the saved price', async () => {
    const channel = {config: {simpleCostSettingsAvailable: true, pricePerUnit: 0.75, currency: 'PLN'}};
    const wrapper = shallowMount(ElectricityMeterParams, {props: {channel}, global: {renderStubDefaultSlot: true}});
    const sections = () => wrapper.findAllComponents(AccordionItem).map((item) => item.props('titleI18n'));
    expect(sections()).toContain('Costs');
    await wrapper.setProps({channel: {...channel, config: {...channel.config, simpleCostSettingsAvailable: false}}});
    expect(sections()).not.toContain('Costs');
    expect(sections()).toContain('History');
    await wrapper.setProps({channel});
    expect(sections()).toContain('Costs');
    expect(channel.config.pricePerUnit).toBe(0.75);
    expect(channel.config.currency).toBe('PLN');
    wrapper.unmount();
  });

  it('refreshes only availability, preserving pending edits and conflict state', async () => {
    const config = {simpleCostSettingsAvailable: true, pricePerUnit: 1.25, currency: 'EUR', voltageLoggerEnabled: true};
    const configBefore = {...config, pricePerUnit: 0.75, currency: 'PLN'};
    const page = {
      channel: {id: 3, config, configBefore},
      channelsStore: {fetchChannel: vi.fn().mockResolvedValue({config: {...configBefore, simpleCostSettingsAvailable: false}})},
      hasPendingChanges: true,
      configConflictDetected: true,
    };
    await ChannelDetailsPage.methods.refreshChannelConfig.call(page, false, ['simpleCostSettingsAvailable']);
    expect(page.channel.config).toEqual({...config, simpleCostSettingsAvailable: false});
    expect(page.channel.configBefore).toEqual({...configBefore, simpleCostSettingsAvailable: false});
    expect(page.channel.config.pricePerUnit).toBe(1.25);
    expect(page.channel.configBefore.pricePerUnit).toBe(0.75);
    expect(page.hasPendingChanges).toBe(true);
    expect(page.configConflictDetected).toBe(true);
  });

  it('targets refresh events to the affected meter', () => {
    const page = {id: '3', fetchChannel: vi.fn(), refreshChannelConfig: vi.fn()};
    ChannelDetailsPage.mounted.call(page);
    try {
      EventBus.$emit('channel-cost-settings-changed', [4]);
      expect(page.refreshChannelConfig).not.toHaveBeenCalled();
      EventBus.$emit('channel-cost-settings-changed', [3]);
      expect(page.refreshChannelConfig).toHaveBeenCalledWith(false, ['simpleCostSettingsAvailable']);
      EventBus.$emit('channel-updated');
      expect(page.refreshChannelConfig).toHaveBeenLastCalledWith(false);
    } finally {
      ChannelDetailsPage.beforeUnmount.call(page);
    }
  });

  it('notifies availability changes after assigning, removing, and deleting tariffs', async () => {
    const store = useEnergyCostStore();
    const notify = vi.spyOn(EventBus, '$emit');
    vi.spyOn(energyCostApi, 'assignPlan').mockResolvedValue({channelId: 3, planId: 10});
    vi.spyOn(energyCostApi, 'unassignPlan').mockResolvedValue();
    vi.spyOn(energyCostApi, 'assignStarter').mockResolvedValue({plan: {id: 10}, assignment: {channelId: 3, planId: 10}});
    vi.spyOn(energyCostApi, 'deletePlan').mockResolvedValue();
    vi.spyOn(energyCostApi, 'saveAssignedConfiguration').mockResolvedValue({
      plan: {id: 10},
      assignment: {channelId: 3, planId: 10},
      affectedAssignments: [
        {channelId: 3, planId: 10},
        {channelId: 4, planId: 10},
      ],
    });
    const update = [3];

    await store.assignPlan(3, 10);
    expect(notify).toHaveBeenLastCalledWith('channel-cost-settings-changed', update);
    await store.unassignPlan(3);
    expect(store.assignmentsByChannelId[3]).toBeNull();
    expect(notify).toHaveBeenLastCalledWith('channel-cost-settings-changed', update);
    await store.assignStarter(3, 'starter', {});
    expect(notify).toHaveBeenLastCalledWith('channel-cost-settings-changed', update);
    await store.saveAssignedConfiguration(3, {}, 'allMeters', 10, [3, 4]);
    expect(notify).toHaveBeenLastCalledWith('channel-cost-settings-changed', [3, 4]);
    store.assignmentsByChannelId[5] = {channelId: 5, planId: 20};
    await store.deletePlan(10);
    expect(notify).toHaveBeenLastCalledWith('channel-cost-settings-changed', [3, 4]);
    expect(store.assignmentsByChannelId[3]).toBeNull();
    expect(store.assignmentsByChannelId[4]).toBeNull();
    expect(store.assignmentsByChannelId[5].planId).toBe(20);
    expect(notify).toHaveBeenCalledTimes(5);
  });

  it('does not refresh availability when an assignment request fails', async () => {
    const notify = vi.spyOn(EventBus, '$emit');
    vi.spyOn(energyCostApi, 'assignPlan').mockRejectedValue(new Error('Assignment failed'));
    await expect(useEnergyCostStore().assignPlan(3, 10)).rejects.toThrow('Assignment failed');
    expect(notify).not.toHaveBeenCalled();
  });
});
