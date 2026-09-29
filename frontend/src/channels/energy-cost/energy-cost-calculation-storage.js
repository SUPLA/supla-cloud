import {openDB} from 'idb';

const SCHEMA_VERSION = 1;

export function canonicalJson(value) {
  if (Array.isArray(value)) return `[${value.map(canonicalJson).join(',')}]`;
  if (value && typeof value === 'object')
    return `{${Object.keys(value)
      .sort()
      .map((key) => `${JSON.stringify(key)}:${canonicalJson(value[key])}`)
      .join(',')}}`;
  return JSON.stringify(value);
}

export function scenarioFingerprint(plan) {
  const source = `${plan?.id ?? 'assigned'}:${plan?.updatedAt ?? ''}:${canonicalJson(plan?.configuration ?? {})}`;
  let hash = 2166136261;
  for (let index = 0; index < source.length; index += 1) hash = Math.imul(hash ^ source.charCodeAt(index), 16777619);
  return (hash >>> 0).toString(36);
}

export class EnergyCostCalculationStorage {
  async connect() {
    if (!globalThis.indexedDB) return;
    try {
      this.db = await openDB('energy_cost_calculations', SCHEMA_VERSION, {
        upgrade(db) {
          if (!db.objectStoreNames.contains('calculations')) db.createObjectStore('calculations', {keyPath: 'key'});
        },
      });
    } catch (error) {
      console.warn(error);
    }
  }

  key(channelId, fingerprint, fromTimestamp, toTimestamp) {
    return `${SCHEMA_VERSION}:${channelId}:${fingerprint}:${fromTimestamp}:${toTimestamp}`;
  }

  async get(channelId, fingerprint, fromTimestamp, toTimestamp) {
    if (!this.db) return null;
    try {
      return await this.db.get('calculations', this.key(channelId, fingerprint, fromTimestamp, toTimestamp));
    } catch {
      return null;
    }
  }

  async put(record) {
    if (!this.db) return;
    try {
      await this.db.put('calculations', record);
    } catch {
      // IndexedDB is an optional performance optimization.
    }
  }

  async invalidatePlan(planId) {
    if (!this.db) return;
    const records = await this.db.getAll('calculations');
    await Promise.all(records.filter((record) => record.scenarioId === planId).map((record) => this.db.delete('calculations', record.key)));
  }
}

export const energyCostCalculationStorage = new EnergyCostCalculationStorage();
