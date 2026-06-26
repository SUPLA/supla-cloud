import {DateTime} from 'luxon';
import {formatDate} from '@/common/filters-date';
import {i18n} from '@/locale.js';

export const billingPeriodUnits = ['day', 'week', 'month', 'year'];

export const componentOptions = [
  {value: 'FORWARD_ACTIVE_ENERGY', label: 'FORWARD_ACTIVE_ENERGY'},
  {value: 'DISTRIBUTION_VARIABLE', label: 'DISTRIBUTION_VARIABLE'},
  {value: 'DISTRIBUTION_FIXED', label: 'DISTRIBUTION_FIXED'},
  {value: 'FEE_VARIABLE', label: 'FEE_VARIABLE'},
  {value: 'FEE_FIXED', label: 'FEE_FIXED'},
];

export const componentUnitMap = {
  FORWARD_ACTIVE_ENERGY: ['kWh'],
  DISTRIBUTION_VARIABLE: ['kWh'],
  DISTRIBUTION_FIXED: ['day', 'week', 'month', 'period'],
  FEE_VARIABLE: ['day', 'week', 'month', 'period'],
  FEE_FIXED: ['day', 'week', 'month', 'period'],
};

export function createKey() {
  return globalThis.crypto?.randomUUID?.() || `${Date.now()}-${Math.random().toString(16).slice(2)}`;
}

export function createEmptyProfile() {
  return {
    id: null,
    name: i18n.global.t('My Tariff Profile'),
    tariffPeriods: [createTariffPeriod()],
  };
}

export function createTariffPeriod(overrides = {}) {
  return {
    _key: createKey(),
    id: null,
    tariffId: null,
    validFrom: null,
    validTo: null,
    pricePeriods: [createPricePeriod({validFrom: null})],
    ...overrides,
  };
}

export function createPricePeriod(overrides = {}) {
  return {
    _key: createKey(),
    id: null,
    billingPeriodLength: 1,
    billingPeriodUnit: 'month',
    currency: 'PLN',
    validFrom: null,
    validTo: null,
    items: [createItem()],
    ...overrides,
  };
}

export function createItem(overrides = {}) {
  return {
    _key: createKey(),
    id: null,
    componentCode: 'FORWARD_ACTIVE_ENERGY',
    zoneCode: null,
    amount: 0,
    unit: 'kWh',
    ...overrides,
  };
}

export function normalizeProfile(profile) {
  const tariffPeriods = normalizeCollection(profile.tariffPeriods);
  return {
    id: profile.id,
    name: profile.name,
    tariffPeriods: tariffPeriods.map((tariffPeriod) => ({
      _key: createKey(),
      id: tariffPeriod.id || null,
      tariffId: tariffPeriod.tariffId || tariffPeriod.tariff?.id || null,
      validFrom: tariffPeriod.validFrom || null,
      validTo: tariffPeriod.validTo || null,
      pricePeriods: normalizeCollection(tariffPeriod.pricePeriods).map((pricePeriod) => ({
        _key: createKey(),
        id: pricePeriod.id || null,
        billingPeriodLength: pricePeriod.billingPeriodLength || 1,
        billingPeriodUnit: pricePeriod.billingPeriodUnit || 'month',
        currency: pricePeriod.currency || 'PLN',
        validFrom: pricePeriod.validFrom || tariffPeriod.validFrom || null,
        validTo: pricePeriod.validTo || null,
        items: normalizeCollection(pricePeriod.items).map((item) => ({
          _key: createKey(),
          id: item.id || null,
          componentCode: item.componentCode || '',
          zoneCode: item.zoneCode ?? null,
          amount: item.amount ?? 0,
          unit: item.unit || 'kWh',
        })),
      })),
    })),
  };
}

function normalizeCollection(collection) {
  if (Array.isArray(collection)) {
    return collection;
  }

  if (!collection || typeof collection !== 'object') {
    return [];
  }

  return Object.values(collection);
}

export function cloneProfile(profile) {
  return normalizeProfile(JSON.parse(JSON.stringify(profile)));
}

export function countPricePeriods(profile) {
  return (profile.tariffPeriods || []).reduce((sum, tariffPeriod) => sum + (tariffPeriod.pricePeriods?.length || 0), 0);
}

export function selectedTariff(tariffs, tariffId) {
  return tariffs.find((tariff) => tariff.id === tariffId);
}

export function tariffZones(tariffs, tariffPeriod) {
  return selectedTariff(tariffs, tariffPeriod.tariffId)?.config?.zones || [];
}

export function tariffZoneSummary(tariff) {
  const zones = tariff?.config?.zones || [];
  return zones.length ? zones.map((zone) => zone.name || zone.code).join(', ') : '—';
}

export function profileTariffSummary(profile, tariffs) {
  const names = [...new Set(profile.tariffPeriods.map((period) => selectedTariff(tariffs, period.tariffId)?.code).filter(Boolean))];
  return names.length ? names.join(', ') : '—';
}

export function tariffPeriodSummary(tariffPeriod, tariffs) {
  const tariff = selectedTariff(tariffs, tariffPeriod.tariffId);
  return `${tariff?.name || '—'} · ${formatRange(tariffPeriod.validFrom, tariffPeriod.validTo)}`;
}

export function formatRange(validFrom, validTo) {
  if (!validFrom && !validTo) {
    return 'Always';
  }
  if (!validFrom) {
    return `Until ${formatRangeDate(validTo, true)}`;
  }
  if (!validTo) {
    return `From ${formatRangeDate(validFrom)}`;
  }
  return `${formatRangeDate(validFrom)} – ${formatRangeDate(validTo, true)}`;
}

function formatRangeDate(value, isEnd = false) {
  const parsed = DateTime.fromISO(value);
  if (!parsed.isValid) {
    return '—';
  }

  const displayValue = isEnd ? parsed.minus({days: 1}) : parsed;
  return formatDate(displayValue, DateTime.DATE_MED);
}

export function unitOptionsForItem(item) {
  return componentUnitMap[item.componentCode] || ['kWh', 'day', 'week', 'month', 'period'];
}

export function syncItemUnit(item) {
  const allowedUnits = unitOptionsForItem(item);
  if (!allowedUnits.includes(item.unit)) {
    item.unit = allowedUnits[0];
  }
}

export function handleTariffChange(tariffPeriod, tariffs) {
  tariffPeriod.pricePeriods.forEach((pricePeriod) => {
    pricePeriod.items.forEach((item) => {
      if (item.zoneCode && !tariffZones(tariffs, tariffPeriod).some((zone) => zone.code === item.zoneCode)) {
        item.zoneCode = null;
      }
    });
  });
}

export function hasTariffDefaults(tariffs, tariffPeriod) {
  return extractTariffDefaults(selectedTariff(tariffs, tariffPeriod.tariffId)).items.length > 0;
}

export function prefillPricePeriodFromTariff(pricePeriod, tariffPeriod, tariffs) {
  const tariff = selectedTariff(tariffs, tariffPeriod.tariffId);
  const defaults = extractTariffDefaults(tariff);
  if (defaults.items.length) {
    pricePeriod.items = defaults.items.map((item) => createItem(item));
    if (defaults.currency) {
      pricePeriod.currency = defaults.currency;
    }
    if (defaults.billingPeriodLength) {
      pricePeriod.billingPeriodLength = defaults.billingPeriodLength;
    }
    if (defaults.billingPeriodUnit) {
      pricePeriod.billingPeriodUnit = defaults.billingPeriodUnit;
    }
    return;
  }

  const zones = tariffZones(tariffs, tariffPeriod);
  pricePeriod.items = zones.length ? zones.map((zone) => createItem({zoneCode: zone.code})) : [createItem()];
}

export function extractTariffDefaults(tariff) {
  const config = tariff?.config || {};
  const defaults =
    [config.defaultPrices, config.profileDefaults, config.priceDefaults, config.defaults].find(
      (candidate) => candidate && !Array.isArray(candidate) && typeof candidate === 'object'
    ) || {};
  const itemSource =
    [
      defaults.items,
      defaults.priceItems,
      Array.isArray(config.defaultPrices?.items) ? config.defaultPrices.items : null,
      Array.isArray(config.defaultPriceItems) ? config.defaultPriceItems : null,
      Array.isArray(config.suggestedPriceItems) ? config.suggestedPriceItems : null,
    ].find(Array.isArray) || [];

  return {
    currency: defaults.currency || 'PLN',
    billingPeriodLength: Number(defaults.billingPeriodLength || 1),
    billingPeriodUnit: defaults.billingPeriodUnit || 'month',
    items: itemSource
      .map((item) => ({
        componentCode: item.componentCode || item.component || '',
        zoneCode: item.zoneCode ?? item.zone ?? null,
        amount: Number(item.amount || 0),
        unit: item.unit || 'kWh',
      }))
      .filter((item) => item.componentCode),
  };
}

export function syncPricePeriodsToTariffRange(tariffPeriod) {
  if (!tariffPeriod.pricePeriods.length) {
    tariffPeriod.pricePeriods = [createPricePeriod({validFrom: tariffPeriod.validFrom, validTo: tariffPeriod.validTo})];
    return;
  }

  tariffPeriod.pricePeriods.forEach((pricePeriod, index) => {
    if (index === 0) {
      pricePeriod.validFrom = tariffPeriod.validFrom;
    } else {
      pricePeriod.validFrom = tariffPeriod.pricePeriods[index - 1].validTo;
    }

    if (index === tariffPeriod.pricePeriods.length - 1) {
      pricePeriod.validTo = tariffPeriod.validTo;
    }
  });
}

export function toPayload(profile) {
  return {
    name: profile.name,
    tariffPeriods: profile.tariffPeriods.map((tariffPeriod) => ({
      tariffId: tariffPeriod.tariffId,
      validFrom: tariffPeriod.validFrom,
      validTo: tariffPeriod.validTo,
      pricePeriods: tariffPeriod.pricePeriods.map((pricePeriod) => ({
        billingPeriodLength: Number(pricePeriod.billingPeriodLength),
        billingPeriodUnit: pricePeriod.billingPeriodUnit,
        currency: pricePeriod.currency,
        validFrom: pricePeriod.validFrom,
        validTo: pricePeriod.validTo,
        items: pricePeriod.items.map((item) => ({
          componentCode: item.componentCode,
          zoneCode: item.zoneCode,
          amount: Number(item.amount),
          unit: item.unit,
        })),
      })),
    })),
  };
}

export function validateProfile(profile, availableTariffs) {
  const result = {
    errors: [],
    warnings: [],
    tariffPeriods: {},
    tariffPeriodWarnings: {},
    pricePeriods: {},
    pricePeriodWarnings: {},
    items: {},
    itemWarnings: {},
  };

  if (!profile.name?.trim()) {
    result.errors.push('Profile name is required.');
  }
  if (!profile.tariffPeriods.length) {
    result.errors.push('Add at least one tariff period.');
    return result;
  }

  const sortedTariffPeriods = [...profile.tariffPeriods].sort(compareByStart);
  let previousTariffPeriod = null;

  sortedTariffPeriods.forEach((tariffPeriod) => {
    const tariff = availableTariffs.find((entry) => entry.id === tariffPeriod.tariffId);
    const tariffErrors = [];
    const tariffWarnings = [];
    const periodStart = parseDateTime(tariffPeriod.validFrom);
    const periodEnd = parseDateTime(tariffPeriod.validTo);

    if (!tariffPeriod.tariffId) {
      tariffErrors.push('Choose a tariff definition.');
    }
    if (periodStart && periodEnd && periodEnd <= periodStart) {
      tariffErrors.push('Tariff period end must be later than start.');
    }
    if (previousTariffPeriod) {
      if (compareEndToStart(previousTariffPeriod.end, periodStart) > 0) {
        tariffErrors.push(
          previousTariffPeriod.end
            ? `Overlaps with the previous tariff period ending ${formatRangeDate(previousTariffPeriod.end.toISO(), true)}.`
            : 'Open-start tariff periods must be the first and cannot be followed by another tariff period.'
        );
      } else if (previousTariffPeriod.end && periodStart && periodStart > previousTariffPeriod.end) {
        tariffWarnings.push(
          `No tariff profile coverage between ${formatRangeDate(previousTariffPeriod.end.toISO(), true)} and ${formatRangeDate(periodStart.toISO())}.`
        );
      }
    }

    validatePricePeriods(tariffPeriod, tariff, periodStart, periodEnd, result);

    if (tariffErrors.length) {
      result.tariffPeriods[tariffPeriod._key] = tariffErrors;
      result.errors.push(...tariffErrors.map((message) => `${profile.name || 'Profile'}: ${message}`));
    }
    if (tariffWarnings.length) {
      result.tariffPeriodWarnings[tariffPeriod._key] = tariffWarnings;
      result.warnings.push(...tariffWarnings);
    }

    previousTariffPeriod = {end: periodEnd};
  });

  result.errors = [...new Set(result.errors)];
  result.warnings = [...new Set(result.warnings)];
  return result;
}

function validatePricePeriods(tariffPeriod, tariff, tariffStart, tariffEnd, result) {
  const zones = tariff?.config?.zones?.map((zone) => zone.code) || [];
  if (!tariffPeriod.pricePeriods.length) {
    pushIssue(result.pricePeriods, tariffPeriod._key, 'Add at least one price period.');
    result.errors.push('Each tariff period needs at least one price period.');
    return;
  }

  const sortedPricePeriods = [...tariffPeriod.pricePeriods].sort(compareByStart);
  let previousPricePeriod = null;

  sortedPricePeriods.forEach((pricePeriod, pricePeriodIndex) => {
    const errors = [];
    const warnings = [];
    const priceStart = parseDateTime(pricePeriod.validFrom);
    const priceEnd = parseDateTime(pricePeriod.validTo);

    if (!Number.isInteger(Number(pricePeriod.billingPeriodLength)) || Number(pricePeriod.billingPeriodLength) <= 0) {
      errors.push('Billing period length must be greater than 0.');
    }
    if (!billingPeriodUnits.includes(pricePeriod.billingPeriodUnit)) {
      errors.push('Choose a valid billing period unit.');
    }
    if (!/^[A-Z]{3}$/.test(pricePeriod.currency || '')) {
      errors.push('Currency must use a 3-letter ISO code.');
    }
    if (priceStart && priceEnd && priceEnd <= priceStart) {
      errors.push('Price period end must be later than start.');
    }
    if (tariffStart && priceStart && priceStart < tariffStart) {
      errors.push('Price period cannot start before its tariff period.');
    }
    if (tariffEnd && !priceEnd) {
      errors.push('Price period must end inside the tariff period.');
    }
    if (tariffEnd && priceEnd && priceEnd > tariffEnd) {
      errors.push('Price period cannot end after its tariff period.');
    }
    if (pricePeriodIndex === 0 && compareByStartValue(pricePeriod.validFrom, tariffPeriod.validFrom) !== 0) {
      errors.push('Price periods must cover the full tariff period start.');
    }
    if (previousPricePeriod) {
      const comparison = compareEndToStart(previousPricePeriod.end, priceStart);
      if (comparison < 0) {
        errors.push('Price periods leave an uncovered gap.');
      } else if (comparison > 0) {
        errors.push('Price periods overlap.');
      }
    }

    if (!pricePeriod.items.length) {
      errors.push('Add at least one price component.');
    }

    const duplicateSignatures = new Set();
    pricePeriod.items.forEach((item) => {
      const signature = `${item.componentCode}::${item.zoneCode || 'none'}`;
      if (duplicateSignatures.has(signature)) {
        warnings.push(`Repeated component/zone pair: ${signature}.`);
      }
      duplicateSignatures.add(signature);
      validateItem(item, zones, result);
    });

    if (errors.length) {
      result.pricePeriods[pricePeriod._key] = errors;
      result.errors.push(...errors);
    }
    if (warnings.length) {
      result.pricePeriodWarnings[pricePeriod._key] = warnings;
      result.warnings.push(...warnings);
    }
    previousPricePeriod = {end: priceEnd};
  });

  const lastPricePeriod = sortedPricePeriods.at(-1);
  const lastPricePeriodEnd = parseDateTime(lastPricePeriod?.validTo);
  if (tariffEnd && lastPricePeriodEnd && lastPricePeriodEnd < tariffEnd) {
    pushIssue(result.pricePeriods, lastPricePeriod._key, 'Price periods do not cover the tariff period end.');
    result.errors.push('Price periods do not cover the tariff period end.');
  }
  if (!tariffEnd && lastPricePeriod && lastPricePeriod.validTo) {
    pushIssue(result.pricePeriods, lastPricePeriod._key, 'Last price period should stay open-ended because the tariff period is open-ended.');
    result.errors.push('Open-ended tariff periods require an open-ended last price period.');
  }
}

function validateItem(item, zones, result) {
  const errors = [];
  const warnings = [];
  const allowedUnits = componentUnitMap[item.componentCode];

  if (!item.componentCode || !allowedUnits) {
    errors.push('Choose a valid component.');
  }
  if (item.amount === null || item.amount === undefined || Number.isNaN(Number(item.amount)) || Number(item.amount) < 0) {
    errors.push('Amount must be 0 or greater.');
  }
  if (!allowedUnits?.includes(item.unit)) {
    errors.push('Selected unit does not match the component.');
  }
  if (item.zoneCode && zones.length && !zones.includes(item.zoneCode)) {
    errors.push('Selected zone does not exist in the tariff.');
  }
  if (!item.zoneCode && zones.length > 1 && item.unit === 'kWh') {
    warnings.push('Consider selecting a zone for kWh-based pricing on multi-zone tariffs.');
  }

  if (errors.length) {
    result.items[item._key] = errors;
    result.errors.push(...errors);
  }
  if (warnings.length) {
    result.itemWarnings[item._key] = warnings;
    result.warnings.push(...warnings);
  }
}

function pushIssue(collection, key, message) {
  collection[key] = [...(collection[key] || []), message];
}

function compareByStart(left, right) {
  const leftStart = parseDateTime(left.validFrom)?.toMillis() ?? Number.NEGATIVE_INFINITY;
  const rightStart = parseDateTime(right.validFrom)?.toMillis() ?? Number.NEGATIVE_INFINITY;
  return leftStart - rightStart;
}

function compareByStartValue(left, right) {
  const leftStart = parseDateTime(left)?.toMillis() ?? Number.NEGATIVE_INFINITY;
  const rightStart = parseDateTime(right)?.toMillis() ?? Number.NEGATIVE_INFINITY;
  return leftStart - rightStart;
}

function compareEndToStart(leftEnd, rightStart) {
  if (leftEnd === null) {
    return 1;
  }
  if (rightStart === null) {
    return 1;
  }

  return leftEnd.toMillis() - rightStart.toMillis();
}

function parseDateTime(value) {
  if (!value) {
    return null;
  }
  const parsed = DateTime.fromISO(value);
  return parsed.isValid ? parsed : null;
}
