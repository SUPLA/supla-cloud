import {afterEach, beforeEach, describe, expect, it, vi} from 'vitest';
import {createPinia, setActivePinia} from 'pinia';
import {energyCostApi} from '@/api/energy-cost-api';
import {useEnergyCostStore} from '@/stores/energy-cost-store';
import {starters} from './energy-cost-comparison-fixtures';

const catalog = starters.map((starter) => ({...starter, revision: 'revision-1', metadata: {id: starter.id, label: starter.label}}));

beforeEach(() => {
  setActivePinia(createPinia());
  vi.spyOn(energyCostApi, 'getPlanStarters').mockResolvedValue(catalog);
  vi.spyOn(energyCostApi, 'getPlanStarter').mockRejectedValue(new Error('Unexpected individual starter request'));
  vi.spyOn(energyCostApi, 'getPresets').mockResolvedValue([]);
});
afterEach(() => vi.restoreAllMocks());

describe('bulk starter catalog', () => {
  it('loads all presentation recipes with one collection request and reuses them for tariff selection', async () => {
    const store = useEnergyCostStore();
    await store.fetchPresentationCatalogs();
    expect(store.tariffs).toEqual(catalog);
    expect(store.tariffDetailsById).toEqual(Object.fromEntries(catalog.map((starter) => [starter.id, starter])));
    expect(await Promise.all(catalog.map((starter) => store.fetchTariff(starter.id)))).toEqual(catalog);
    await store.fetchPresentationCatalogs();
    expect(energyCostApi.getPlanStarters).toHaveBeenCalledOnce();
    expect(energyCostApi.getPlanStarter).not.toHaveBeenCalled();
  });

  it('shares an in-flight collection request with concurrent individual tariff lookups', async () => {
    let resolve;
    energyCostApi.getPlanStarters.mockReturnValueOnce(
      new Promise((done) => {
        resolve = done;
      })
    );
    const store = useEnergyCostStore();
    const presentation = store.fetchPresentationCatalogs();
    const tariffs = Promise.all(catalog.map((starter) => store.fetchTariff(starter.id)));
    expect(energyCostApi.getPlanStarters).toHaveBeenCalledOnce();
    expect(energyCostApi.getPlanStarter).not.toHaveBeenCalled();
    resolve(catalog);
    await presentation;
    expect(await tariffs).toEqual(catalog);
    expect(energyCostApi.getPlanStarter).not.toHaveBeenCalled();
  });

  it('refreshes recipe details and drops removed starters when the collection is refreshed', async () => {
    const store = useEnergyCostStore();
    await store.fetchTariffs();
    const updated = {...catalog[1], revision: 'revision-2', components: [{...catalog[1].components[0], values: {price: '0.5'}}]};
    energyCostApi.getPlanStarters.mockResolvedValueOnce([updated]);
    await store.fetchTariffs(true);
    expect(store.tariffs).toEqual([updated]);
    expect(store.tariffDetailsById).toEqual({[updated.id]: updated});
    expect(await store.fetchTariff(updated.id)).toEqual(updated);
    expect(energyCostApi.getPlanStarters).toHaveBeenCalledTimes(2);
    expect(energyCostApi.getPlanStarter).not.toHaveBeenCalled();
  });
});
