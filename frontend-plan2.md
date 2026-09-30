You are working in `SUPLA/supla-cloud` on the current `tariffs` branch.

Read and follow `AGENTS.md` first.

This task introduces the first real energy-cost visualization/exploration UI.

Do not remove or regress the existing cost-plan creation, editing or channel-assignment flows.

## Product goal

Turn the electricity-meter Energy Cost tab into a fast cost explorer.

The user must be able to:

1. inspect energy costs for a selected time range;
2. see both totals and time-based aggregation;
3. understand where the cost came from;
4. compare two time periods;
5. compare the current cost plan against:
    - another saved CostPlan;
    - another standard tariff from the CostPlanStarter catalogue;
6. do all of this without changing the current channel assignment;
7. get fast repeated navigation using client-side caching.

Do not expose calculator implementation concepts unnecessarily.

A CostPlanStarter remains user-facing **Tariff** terminology.

---

# 1. Understand the CalculationResult contract first

The backend returns the calculator's `CalculationResult`.

Relevant fields are:

```text
currency
range

usage

costs.net
costs.gross
costs.taxes

periodicCharges
billingContext
billingPeriods

processedDeltaCount

charges
intervals
```

Use them according to their intended semantics.

## Summary costs

The main result contains:

```js
result.costs.gross.usageBased.total
result.costs.gross.usageBased.byComponent
result.costs.gross.usageBased.byZone

result.costs.gross.periodic.total
result.costs.gross.periodic.byComponent

result.costs.gross.total

result.costs.net.*
result.costs.taxes.total
result.costs.taxes.byTax
```

These are authoritative server totals.

Do not recalculate top-level financial totals from chart data.

## Usage

Raw meter totals are available under:

```js
result.usage.ACTIVE_ENERGY_IMPORT
result.usage.ACTIVE_ENERGY_EXPORT
```

Use these for import/export summary values.

## Time series

`charges[]` is the authoritative usage-based cost fact series.

Every charge may contain information such as:

```js
{
  componentId,
  kind,
  category,
  from,
  to,
  quantity,
  selection,
  pricing,
  amounts: {
    net,
    taxes,
    taxTotal,
    gross
  }
}
```

Use `charges[]` as the source for cost charts.

Do **not** build cost time series from `intervals[]`.

This is critical because temporally netted components may produce one hourly charge from four 15-minute meter intervals. The calculator intentionally does not spread such a cost back over the individual meter intervals.

## Billing periods

`billingPeriods[]` contains:

```text
from
to
fullyCovered
transitional
billingCycle
usage
costs
```

Use this for billing-period views and periodic-charge presentation.

## Partial billing periods

When periodic charges exist and the requested range does not contain complete billing periods:

```js
result.costs.gross.total === null
```

and corresponding full net/tax totals may also be null.

Never convert this to zero.

Never present:

```text
usageBased.total
```

as if it were the complete bill.

The UI should explicitly distinguish:

```text
Usage-based cost
```

from:

```text
Total cost
```

when fixed/periodic costs cannot be calculated for the selected range.

---

# 2. Keep the existing plan-assignment controls

`channel-energy-costs.vue` currently owns:

- fetching CostPlans;
- channel assignment;
- changing the assignment;
- unassigning;
- create/edit CostPlan dialogs.

Keep this behavior.

The Energy Cost page should become approximately:

```text
--------------------------------------------------
Cost plan: [ Home / PGE G12 ]    Edit   Create
--------------------------------------------------

[ time range controls ]                 [ Compare ]

[ KPI cards ]

[ main cost chart ]

[ cost breakdown ]
```

Do not make visualization state change the assigned CostPlan.

---

# 3. Introduce an EnergyCostDashboard

Create a dedicated component rather than expanding
`channel-energy-costs.vue` into a giant component.

Suggested structure:

```text
energy-cost/
  channel-energy-costs.vue

  energy-cost-dashboard.vue
  energy-cost-range-selector.vue
  energy-cost-summary.vue
  energy-cost-chart.vue
  energy-cost-breakdown.vue
  energy-cost-comparison.vue

  energy-cost-calculation-storage.js
  energy-cost-result-utils.js
```

Names may vary, but keep responsibilities separated.

`channel-energy-costs.vue` should remain the page/controller component.

---

# 4. Time-range UX

The user should be able to switch ranges quickly.

Provide shortcuts such as:

```text
Today
Yesterday
This month
Previous month
This year
Previous year
Custom
```

Use Luxon and the CostPlan timezone, normally `Europe/Warsaw`.

Prefer day-aligned ranges for the normal UI.

Use:

```text
[from, to)
```

semantics internally.

For example a September selection is:

```text
2026-09-01T00:00 Europe/Warsaw
->
2026-10-01T00:00 Europe/Warsaw
```

This also naturally aligns hourly netting windows.

Reuse concepts from:

```text
frontend/src/channels/history/channel-measurements-predefined-time-ranges.vue
frontend/src/activity/date-range-picker.vue
```

but do not tightly couple the cost dashboard to measurement-history storage.

Remember the selected range per channel in `sessionStorage`.

---

# 5. Main summary

Add compact KPI cards.

At minimum show:

```text
Total gross cost
Net cost
Taxes
Energy imported
Energy exported
```

Use server values directly.

When `costs.gross.total` is null, replace the first card with:

```text
Usage-based cost
```

using:

```js
costs.gross.usageBased.total
```

and display a visible explanation that the selected range does not contain complete billing periods and therefore fixed charges are not included in a complete total.

Also show periodic charges separately where available.

Do not hide that distinction in a tooltip only.

---

# 6. First chart: gross cost over time

Use ApexCharts, which is already used by Cloud.

Follow the established implementation style from:

```text
frontend/src/channels/history/channel-measurements-history-chart.vue
```

Reuse the same concepts:

- localized ApexCharts locales;
- datetime x-axis;
- tooltips;
- no expensive animations;
- download/zoom toolbar where useful;
- responsive handling.

Do not reuse `CHART_TYPES`; energy costs need their own chart model.

## Default visualization

Use a stacked column/bar chart.

Aggregate `charges[]` by time bucket and component.

Example:

```text
          Energy purchase
          Distribution
          Other usage costs
day 1     ███████
day 2     █████
day 3     █████████
```

The user should be able to immediately see both:

- how much was spent;
- what generated the cost.

Use user-friendly component labels.

Resolve labels from referenced preset/component metadata where possible and fall back to translated `kind` labels.

Do not display raw component IDs unless no better label exists.

---

# 7. Chart aggregation

Implement frontend aggregation of `charges[]`.

Supported initial granularities:

```text
hour
day
month
```

Choose an automatic default based on visible duration.

A reasonable starting policy:

```text
<= 48 hours  -> hour
<= 120 days  -> day
otherwise    -> month
```

Allow the user to override it.

Both automatic and manual aggregation must operate in the CostPlan timezone, not browser-local UTC boundaries.

Create a pure utility such as:

```js
aggregateCharges(charges, granularity, timezone)
```

that produces a chart-oriented structure.

Aggregate at least:

```text
gross
net
taxes
byComponent
byZone
```

Do not mutate the API response.

---

# 8. Decimal handling

The calculator intentionally returns financial values as decimal strings.

Keep authoritative summary values as strings.

For aggregation, avoid accumulating thousands of monetary values using naïve floating-point addition if it can make chart totals visibly disagree with server totals.

Implement a small exact decimal-string addition helper, or an equivalent deterministic approach, for aggregation.

Only convert the final bucket values to JS numbers when constructing ApexCharts series.

No multiplication or billing logic belongs in the frontend.

---

# 9. Periodic costs on charts

`charges[]` deliberately does not contain periodic/fixed fees.

Do not distribute a monthly fixed fee over individual days.

For hour/day charts:

- chart usage-based charges;
- show fixed/periodic costs separately in summary/breakdown.

For billing-period/month aggregation, `billingPeriods[]` may be used to add a separate:

```text
Fixed charges
```

series when the billing period is fully covered.

Do not invent daily shares of monthly fees.

---

# 10. Cost breakdown

Below or beside the main chart, provide breakdowns from authoritative result aggregates.

Initial sections:

### By component

Use:

```js
costs.gross.usageBased.byComponent
costs.gross.periodic.byComponent
```

### By tariff zone

Use:

```js
costs.gross.usageBased.byZone
```

Hide this section when empty.

### Taxes

Use:

```js
costs.taxes.byTax
```

Present at least:

```text
VAT
EXCISE
```

when present.

A compact horizontal bar, donut or table is acceptable.

Do not make three large charts in the first iteration. The main time-series chart should remain the visual focus.

---

# 11. Comparison is one feature with two modes

Add one main action:

```text
Compare
```

It opens comparison configuration.

Support:

```text
Compare period
Compare tariff / cost plan
```

Do not create unrelated separate simulation UX.

---

# 12. Compare two periods

Period comparison means:

```text
same CostPlan
range A vs range B
```

Fetch two independent CalculationResults.

Show:

- gross/net/tax values for A and B;
- absolute difference;
- percentage difference when meaningful;
- imported/exported energy difference;
- component cost differences.

For the first version, do not force arbitrary calendar ranges onto one misleading datetime x-axis.

Prefer:

- two vertically aligned charts;
- same aggregation;
- same Y-axis scale;
- clearly labelled ranges.

If later both ranges have identical bucket structure, a normalized overlay can be added.

Correctness is more important than visual cleverness here.

---

# 13. Compare another saved CostPlan

Scenario comparison means:

```text
same channel
same time range
scenario A vs scenario B
```

Scenario A is normally the assigned plan.

For another saved CostPlan, use its stored configuration and call:

```text
POST /channels/{channel}/energy-cost-calculation
```

Do not assign that plan to the channel.

Do not update channel state.

The comparison exists only in the dashboard.

Because both scenarios use the same time axis, render grouped series on the same chart.

Example:

```text
             Current       Alternative
Sep 1        ███████       █████
Sep 2        ██████        ███████
```

Also show a prominent difference:

```text
Alternative: -42.18 PLN (-7.4%)
```

Do not describe positive/negative differences as savings unless the sign actually supports it.

---

# 14. Compare a standard tariff

The alternative scenario may also be selected from the CostPlanStarter catalogue.

In the GUI call these:

```text
Tariffs
```

never starters.

Use the existing CostPlanStarter APIs.

Fetch the selected tariff recipe, build the same temporary CostPlan format used by plan creation, and send it to:

```text
POST /channels/{channel}/energy-cost-calculation
```

Do not save it.

Do not assign it.

Do not store a starter ID inside the CostPlan configuration.

Use the existing frontend CostPlan-building helpers rather than reimplementing starter semantics inside the dashboard.

---

# 15. Saved-plan and tariff picker

In scenario comparison, present one selector with logical groups:

```text
Saved cost plans
  Home
  Dynamic contract
  ...

Tariffs
  PGE Dystrybucja - G11
  PGE Dystrybucja - G12
  TAURON Dystrybucja - G12
  ...
```

The currently active plan should be obvious.

Do not mix individual component tariff presets into this selector.

---

# 16. Handle unavailable tariff history honestly

A standard tariff/preset may only cover a particular date range.

If the user selects a tariff that cannot calculate the chosen historical range:

- do not silently clamp the requested dates;
- do not extrapolate prices;
- display the backend's safe error;
- optionally explain that the tariff is not available for the whole selected period.

The same rule applies to saved plans whose CostPlan periods do not cover the requested range.

---

# 17. Create a CalculationResult adapter

Do not scatter direct deep accesses like:

```js
result.costs.gross.usageBased.byComponent
```

through every Vue component.

Create a focused adapter/helper layer in:

```text
energy-cost-result-utils.js
```

It should expose functions conceptually like:

```js
summaryFromResult(result)
aggregateCharges(result, granularity, timezone)
componentBreakdown(result)
zoneBreakdown(result)
taxBreakdown(result)
comparisonDelta(a, b)
```

Keep these functions pure and heavily unit-tested.

Vue components should mostly render view models.

---

# 18. IndexedDB calculation cache

Introduce:

```text
frontend/src/channels/energy-cost/energy-cost-calculation-storage.js
```

Use the already installed:

```js
import {openDB} from 'idb';
```

Follow the robustness pattern from:

```text
frontend/src/channels/history/channel-measurements-storage.js
```

including graceful fallback when IndexedDB is unavailable.

The energy-cost dashboard must still work without IndexedDB.

IndexedDB improves speed; it must not be mandatory for correctness.

---

# 19. Cache raw CalculationResult responses

Cache successful API calculation responses, not rendered ApexCharts data.

This allows different visualizations and aggregation levels to reuse the same server calculation.

A cached record should conceptually contain:

```js
{
  key,
  channelId,

  scenarioType,
  scenarioId,
  scenarioFingerprint,

  fromTimestamp,
  toTimestamp,

  fetchedAt,

  result
}
```

Do not cache failed calculations.

---

# 20. Cost cache keys must include the pricing scenario

Historical meter logs may be immutable, but historical **costs are not independent of the CostPlan**.

Never key the cache only by:

```text
channel + from + to
```

The key must include a scenario fingerprint.

For a saved CostPlan include at least:

```text
plan id
plan updatedAt
configuration hash
```

For an ad-hoc tariff include at least:

```text
tariff/starter revision
temporary configuration hash
```

Where referenced tariff-preset revisions are already available, include them in the fingerprint as well.

Use deterministic/canonical JSON serialization before hashing.

---

# 21. Cache invalidation

Immediately invalidate relevant cache records after:

```text
updatePlan
deletePlan
```

A changed channel assignment changes which scenario is primary but does not require deleting valid cached calculations for the individual plans.

Use a cache schema/version constant so incompatible frontend releases can invalidate previous records.

Do not treat cached calculations as permanently immutable because calculator code, preset defaults and tax profiles may change independently of measurement history.

Use stale-while-revalidate.

For example:

- show a historical cached result immediately;
- if it is older than the freshness window, refresh it in the background;
- replace the UI result if the refreshed calculation differs.

Use a much shorter freshness period for a range that reaches recent/current data because new meter logs may still arrive.

---

# 22. Do not compose arbitrary server results

Do not implement:

```text
calculation(A..B) + calculation(B..C)
```

as a general substitute for:

```text
calculation(A..C)
```

Temporal netting, billing periods and periodic charges make arbitrary result composition unsafe.

The initial cache should cache exact calculation requests.

More sophisticated canonical billing-period chunk caching may be considered later, but is out of scope for the first visualization implementation.

---

# 23. Recommended small backend optimization

The current backend calls:

```php
new CalculationOptions(
    includeIntervals: true,
    includeCharges: true,
)
```

for every energy-cost calculation.

The dashboard requires `charges[]` but does not normally require `intervals[]`.

If backend changes are allowed in this task, add backward-compatible calculation-detail options so the frontend can request:

```text
includeCharges=true
includeIntervals=false
```

for chart calculations.

Keep existing defaults compatible.

Do not remove either field from the existing API contract.

If backend work is explicitly out of scope, leave this optimization for a follow-up and implement the frontend against the current response.

Do not block the visualization implementation on it.

---

# 24. ApexCharts implementation

Use the existing `apexcharts` dependency.

Do not add another charting library.

Follow Cloud conventions from:

```text
channel-measurements-history-chart.vue
```

including:

- locale configuration;
- responsive behavior;
- datetime axes;
- tooltip formatting;
- chart cleanup on unmount;
- disabled expensive animations.

Create a cost-specific chart component rather than coupling to measurement `CHART_TYPES`.

Primary chart type:

```text
stacked bar/column
```

for a single scenario.

Comparison of pricing scenarios:

```text
grouped columns
```

for the two scenario totals.

---

# 25. Loading behavior

Do not blank the whole dashboard every time the range changes.

When possible:

1. keep the previous visualization visible;
2. indicate loading on the chart;
3. replace it when the new data arrives.

When IndexedDB has a cache hit:

1. render cached data immediately;
2. optionally show a subtle refreshing state;
3. refresh in the background when stale.

Avoid UI jumps.

---

# 26. Race-condition protection

Range and comparison controls can produce requests quickly.

Use an incrementing request token, AbortController where supported by the API wrapper, or an equivalent mechanism.

A slower response for an older selection must never overwrite a newer dashboard state.

This is especially important when dragging through date ranges or switching comparison scenarios.

---

# 27. Empty states and errors

Handle separately:

```text
No assigned cost plan
No meter data in this range
Incomplete billing period
Calculation error
Tariff unavailable for selected period
Missing reference-price data
```

Do not collapse all of these into:

```text
Could not calculate costs.
```

Use the safe backend message where appropriate.

The existing create/assign CostPlan call to action remains the empty state when the channel has no plan.

Simulation/comparison with another tariff may still be available without assigning it, if the product flow permits it.

---

# 28. First implementation milestone

Do not implement every possible visualization before delivering value.

The first shippable milestone should contain:

1. current assigned CostPlan calculation;
2. quick + custom range selection;
3. summary cards;
4. gross usage-based stacked cost chart;
5. automatic hour/day/month aggregation;
6. component breakdown;
7. correct partial-billing-period handling;
8. exact-query IndexedDB cache;
9. existing CostPlan assignment/editing unchanged.

This alone should already feel like a complete useful feature.

---

# 29. Second milestone — period comparison

Add:

1. comparison mode;
2. second time range;
3. second calculation;
4. side-by-side KPI values and deltas;
5. synchronized charts using a common Y scale;
6. component deltas.

Do not implement tariff simulation in the same commit if that makes the feature difficult to review.

---

# 30. Third milestone — plan/tariff simulation

Add:

1. saved CostPlan alternatives;
2. Tariff alternatives from CostPlanStarter catalogue;
3. POST temporary calculations;
4. grouped same-range chart;
5. cost differences;
6. caching by scenario fingerprint.

No plan or assignment must be mutated by this flow.

---

# 31. Later enhancements, explicitly out of the first iteration

Leave room for:

- zone-specific visualization;
- tax-focused visualization;
- billing-period-only mode;
- effective PLN/kWh metrics;
- download/export;
- comparison of more than two scenarios;
- normalized overlay of arbitrary comparison periods;
- cost annotations on the existing energy-history chart;
- canonical monthly cache chunks/prefetching.

Do not implement these prematurely.

---

# 32. Tests

Add unit tests for result transformation and aggregation.

At minimum cover:

1. gross/net/tax summary extraction;
2. `null` complete totals for partial billing periods;
3. usage import/export extraction;
4. hourly charge aggregation;
5. daily aggregation;
6. monthly aggregation;
7. component aggregation;
8. zone aggregation;
9. charge tax aggregation;
10. timezone/DST bucket boundaries;
11. temporal-netting charge remains one atomic fact;
12. periodic cost is not spread over daily buckets;
13. exact decimal aggregation;
14. scenario comparison deltas;
15. zero baseline percentage handling;
16. deterministic scenario fingerprint;
17. cache hit;
18. cache miss after CostPlan update;
19. cache separation between two CostPlans;
20. cache separation between two tariff revisions;
21. no IndexedDB support falls back to network;
22. stale cache renders before revalidation;
23. stale response cannot overwrite a newer selection.

Add component tests for:

- range switching;
- compare mode;
- partial-total warning;
- plan/tariff simulation not changing assignment.

Use `fake-indexeddb` for IndexedDB tests, as existing measurement-storage tests already do.

---

# 33. Regression requirements

Before finishing verify that:

- CostPlans can still be created;
- CostPlans can still be edited;
- plans can still be assigned to a channel;
- changing the selected plan changes the assignment;
- unassign still works;
- visualization changing ranges does not alter the assignment;
- period comparison does not alter the assignment;
- tariff comparison does not create a CostPlan;
- tariff comparison does not alter the assignment;
- saved-plan comparison does not alter the assignment.

---

# 34. Verification

Follow `AGENTS.md`.

At minimum run:

```bash
cd frontend
npm run test:unit
npm run lint
npm run format:check
npm run build
```

If a backend calculation-options change is included, also run the relevant backend integration tests.

Do not run translation collection unless explicitly requested.

At the end report:

- files added/changed;
- final dashboard UX;
- aggregation rules;
- comparison behavior;
- cache key/invalidation strategy;
- ApexCharts implementation;
- tests added;
- commands run and their results;
- deferred follow-up work.
