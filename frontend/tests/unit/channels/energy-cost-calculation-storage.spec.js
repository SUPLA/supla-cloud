import 'fake-indexeddb/auto';
import {describe, expect, it} from 'vitest';
import {canonicalJson, EnergyCostCalculationStorage, scenarioFingerprint} from '@/channels/energy-cost/energy-cost-calculation-storage';

describe('energy cost calculation storage', () => {
  it('uses a deterministic scenario fingerprint regardless of object key order', () => {
    expect(scenarioFingerprint({id: 1, updatedAt: '2026-01-01', configuration: {b: 2, a: 1}})).toBe(
      scenarioFingerprint({id: 1, updatedAt: '2026-01-01', configuration: {a: 1, b: 2}})
    );
    expect(canonicalJson({b: 2, a: 1})).toBe('{"a":1,"b":2}');
  });

  it('changes the fingerprint when a referenced preset revision changes', () => {
    const plan = {id: 1, updatedAt: '2026-01-01', configuration: {periods: []}};

    expect(scenarioFingerprint(plan, 'PL.G11:old')).not.toBe(scenarioFingerprint(plan, 'PL.G11:new'));
  });

  it('separates cached exact queries and invalidates a changed plan', async () => {
    const storage = new EnergyCostCalculationStorage();
    await storage.connect();
    const key = storage.key(2, 'plan-a', 10, 20);
    await storage.put({
      key,
      channelId: 2,
      scenarioId: 1,
      scenarioFingerprint: 'plan-a',
      fromTimestamp: 10,
      toTimestamp: 20,
      fetchedAt: 1,
      result: {currency: 'PLN'},
    });
    expect(await storage.get(2, 'plan-a', 10, 20)).toMatchObject({result: {currency: 'PLN'}});
    expect(await storage.get(2, 'plan-b', 10, 20)).toBeUndefined();
    await storage.invalidatePlan(1);
    expect(await storage.get(2, 'plan-a', 10, 20)).toBeUndefined();
  });
});
