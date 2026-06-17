<template>
  <div class="container channel-costs-tab">
    <div class="costs-hero">
      <div>
        <div class="eyebrow">{{ $t('Electricity costs') }}</div>
        <h2>{{ $t('Energy spending cockpit') }}</h2>
        <p>{{ $t('Explore usage, blended price and cost structure across billing periods, months, weeks and days.') }}</p>
      </div>
      <div class="hero-actions">
        <div class="btn-group wrapped-mode-group">
          <button
            v-for="mode in periodModes"
            :key="mode.value"
            :class="['btn', periodMode === mode.value ? 'btn-orange' : 'btn-default']"
            type="button"
            @click="changePeriodMode(mode.value)"
          >
            {{ $t(mode.label) }}
          </button>
        </div>
        <div class="btn-group wrapped-mode-group mt-2">
          <button
            v-for="mode in visualModes"
            :key="mode.value"
            :class="['btn', visualMode === mode.value ? 'btn-white' : 'btn-default']"
            type="button"
            @click="visualMode = mode.value"
          >
            {{ $t(mode.label) }}
          </button>
        </div>
        <div class="btn-group wrapped-mode-group mt-2">
          <button
            v-for="mode in compareModes"
            :key="mode.value"
            :class="['btn', compareMode === mode.value ? 'btn-white' : 'btn-default']"
            type="button"
            @click="changeCompareMode(mode.value)"
          >
            {{ $t(mode.label) }}
          </button>
        </div>
      </div>
    </div>

    <div class="details-page-block filter-block">
      <div class="row align-items-end">
        <div class="col-lg-7">
          <DateRangePicker :value="dateRange" @input="(value) => (dateRange = value)" />
        </div>
        <div class="col-lg-5">
          <div class="quick-range-grid">
            <button v-for="range in quickRanges" :key="range.label" class="btn btn-default btn-sm" type="button" @click="applyQuickRange(range)">
              {{ $t(range.label) }}
            </button>
            <button class="btn btn-white btn-sm" type="button" :disabled="loading" @click="refresh()">
              {{ loading ? $t('Loading') : $t('Refresh') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <loading-cover :loading="loading">
      <div v-if="!rows.length" class="details-page-block empty-panel">
        <h3>{{ $t('No cost data for the selected period.') }}</h3>
        <p>{{ $t('Assign a tariff and a price list first, then come back here to explore the results.') }}</p>
      </div>

      <template v-else>
        <div class="row stat-cards-row">
          <div class="col-sm-6 col-xl-3" v-for="card in summaryCards" :key="card.label">
            <div class="summary-card">
              <div class="summary-card__label">{{ $t(card.label) }}</div>
              <div class="summary-card__value">{{ card.value }}</div>
              <div class="summary-card__note" v-if="card.note">{{ card.note }}</div>
            </div>
          </div>
        </div>

        <div class="row mt-4">
          <div class="col-xl-8">
            <div class="details-page-block chart-panel">
              <div class="panel-toolbar">
                <div>
                  <h3 class="m-0">{{ $t('Trend') }}</h3>
                  <div class="text-muted small">{{ $t('Stacked bars show money spent per component. The line shows used energy in kWh.') }}</div>
                </div>
              </div>
              <div v-show="visualMode !== 'table'" ref="trendChart"></div>
            </div>
          </div>
          <div class="col-xl-4">
            <div class="details-page-block chart-panel">
              <div class="panel-toolbar panel-toolbar--compact">
                <div>
                  <h3 class="m-0">{{ $t('Cost structure') }}</h3>
                  <div class="text-muted small mb-3">{{ $t('See where the bill comes from.') }}</div>
                </div>
                <div class="btn-group wrapped-mode-group">
                  <button
                    v-for="mode in breakdownModes"
                    :key="mode.value"
                    :class="['btn', breakdownMode === mode.value ? 'btn-white' : 'btn-default']"
                    type="button"
                    @click="changeBreakdownMode(mode.value)"
                  >
                    {{ $t(mode.label) }}
                  </button>
                </div>
              </div>
              <div ref="donutChart"></div>
              <div class="breakdown-list mt-3">
                <div v-for="entry in topBreakdownEntries" :key="entry.key" class="breakdown-list__item">
                  <span>{{ entry.label }}</span>
                  <strong>{{ formatCurrency(entry.value) }}</strong>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="visualMode !== 'chart'" class="details-page-block mt-4">
          <div class="panel-toolbar">
            <div>
              <h3 class="m-0">{{ $t('Detailed periods') }}</h3>
              <div class="text-muted small">{{ $t('Each row is ready to be used in tables, cards and dashboards.') }}</div>
            </div>
            <div class="btn-group wrapped-mode-group">
              <button class="btn btn-default" type="button" @click="exportRows('csv')">
                {{ $t('Export CSV') }}
              </button>
              <button class="btn btn-white" type="button" @click="exportRows('xlsx')">
                {{ $t('Export XLSX') }}
              </button>
            </div>
          </div>

          <div class="costs-table d-none d-md-block">
            <table class="table">
              <thead>
                <tr>
                  <th>{{ $t(periodHeaderLabel) }}</th>
                  <th>{{ $t('Usage') }}</th>
                  <th>{{ $t('Average price') }}</th>
                  <th>{{ $t('Cost') }}</th>
                  <th>{{ $t('Top zone') }}</th>
                  <th>{{ $t('Top component') }}</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="row in rows" :key="row.key">
                  <td>
                    <div class="fw-semibold">{{ row.label }}</div>
                    <div class="small text-muted">{{ row.periodHint }}</div>
                  </td>
                  <td>{{ formatUsage(row.usageKwh) }}</td>
                  <td>{{ formatAverage(row.averagePrice) }}</td>
                  <td class="cost-cell">{{ formatCurrency(row.costTotal) }}</td>
                  <td>
                    <span class="pill" v-if="row.topZone">{{ row.topZone }}</span>
                    <span class="text-muted" v-else>{{ $t('N/A') }}</span>
                  </td>
                  <td>
                    <span class="pill pill-muted" v-if="row.topComponent">{{ row.topComponent }}</span>
                    <span class="text-muted" v-else>{{ $t('N/A') }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="d-md-none mobile-cards">
            <div v-for="row in rows" :key="row.key" class="mobile-period-card">
              <div class="mobile-period-card__header">
                <strong>{{ row.label }}</strong>
                <span class="cost-cell">{{ formatCurrency(row.costTotal) }}</span>
              </div>
              <div class="mobile-period-card__hint">{{ row.periodHint }}</div>
              <div class="mobile-period-card__grid">
                <div>
                  <span>{{ $t('Usage') }}</span>
                  <strong>{{ formatUsage(row.usageKwh) }}</strong>
                </div>
                <div>
                  <span>{{ $t('Average price') }}</span>
                  <strong>{{ formatAverage(row.averagePrice) }}</strong>
                </div>
                <div>
                  <span>{{ $t('Top zone') }}</span>
                  <strong>{{ row.topZone || $t('N/A') }}</strong>
                </div>
                <div>
                  <span>{{ $t('Top component') }}</span>
                  <strong>{{ row.topComponent || $t('N/A') }}</strong>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>
    </loading-cover>
  </div>
</template>

<script>
  import ApexCharts from 'apexcharts';
  import XLSX from 'xlsx';
  import {DateTime} from 'luxon';
  import DateRangePicker from '@/activity/date-range-picker.vue';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import {energyTariffsApi} from '@/api/energy-tariffs-api';
  import {formatDateTime} from '@/common/filters-date';
  import latinize from 'latinize';

  export default {
    components: {DateRangePicker, LoadingCover},
    props: {
      channel: {type: Object, required: true},
    },
    data() {
      return {
        loading: true,
        visualMode: 'hybrid',
        compareMode: 'none',
        breakdownMode: 'component',
        periodMode: 'month',
        rawCostLogs: [],
        rawBillingSummaries: [],
        comparisonRows: [],
        rows: [],
        dateRange: {
          dateStart: DateTime.now().minus({months: 12}).startOf('day').toISO(),
          dateEnd: DateTime.now().endOf('day').toISO(),
        },
        trendChartInstance: undefined,
        donutChartInstance: undefined,
        periodModes: [
          {value: 'billing', label: 'Billing periods'},
          {value: 'month', label: 'Months'},
          {value: 'week', label: 'Weeks'},
          {value: 'day', label: 'Days'},
        ],
        visualModes: [
          {value: 'chart', label: 'Chart only'},
          {value: 'hybrid', label: 'Dashboard'},
          {value: 'table', label: 'Table only'},
        ],
        compareModes: [
          {value: 'none', label: 'No comparison'},
          {value: 'previousPeriod', label: 'Previous period'},
          {value: 'previousYear', label: 'Previous year'},
        ],
        breakdownModes: [
          {value: 'component', label: 'Components'},
          {value: 'zone', label: 'Zones'},
        ],
        quickRanges: [
          {label: 'Last 30 days', days: 30},
          {label: 'Last 90 days', days: 90},
          {label: 'Last 180 days', days: 180},
          {label: 'Last 365 days', days: 365},
        ],
      };
    },
    computed: {
      summaryCards() {
        const totalUsage = this.rows.reduce((sum, row) => sum + row.usageKwh, 0);
        const totalCost = this.rows.reduce((sum, row) => sum + row.costTotal, 0);
        const averagePrice = totalUsage > 0 ? totalCost / totalUsage : 0;
        const highestCost = this.rows.reduce((top, row) => (row.costTotal > (top?.costTotal || 0) ? row : top), null);
        const previousTotalCost = this.comparisonRows.reduce((sum, row) => sum + row.costTotal, 0);
        const delta = totalCost - previousTotalCost;
        return [
          {label: 'Total cost', value: this.formatCurrency(totalCost), note: this.rows.length ? `${this.rows.length} ${this.$t('periods')}` : ''},
          {label: 'Total usage', value: this.formatUsage(totalUsage), note: this.$t('Forward active energy')},
          {label: 'Average price', value: this.formatAverage(averagePrice), note: this.$t('Blended cost per kWh')},
          {
            label: this.compareMode === 'none' ? 'Most expensive period' : 'Cost delta',
            value: this.compareMode === 'none' ? (highestCost ? highestCost.label : '-') : this.formatSignedCurrency(delta),
            note: this.compareMode === 'none' ? (highestCost ? this.formatCurrency(highestCost.costTotal) : '') : this.compareModeLabel,
          },
        ];
      },
      compareModeLabel() {
        return {
          none: '',
          previousPeriod: this.$t('Compared with previous period'),
          previousYear: this.$t('Compared with previous year'),
        }[this.compareMode];
      },
      topBreakdownEntries() {
        const breakdown = this.rows.reduce((acc, row) => {
          const source = this.breakdownMode === 'zone' ? row.byZone || {} : row.byComponent || {};
          Object.entries(source).forEach(([key, value]) => {
            acc[key] = (acc[key] || 0) + value;
          });
          return acc;
        }, {});
        return Object.entries(breakdown)
          .map(([key, value]) => ({key, label: key.replaceAll('_', ' '), value}))
          .sort((a, b) => b.value - a.value)
          .slice(0, 5);
      },
      periodHeaderLabel() {
        return {
          billing: 'Billing period',
          month: 'Month',
          week: 'Week',
          day: 'Day',
        }[this.periodMode];
      },
      exportFilename() {
        return [
          latinize((this.channel.caption || this.channel.function?.caption || 'energy-costs').toLowerCase().trim())
            .replace(/[^a-z0-9]/g, '-')
            .replace(/-+/g, '-'),
          `ID${this.channel.id}`,
          this.periodMode,
          this.compareMode,
          DateTime.now().toFormat('yyyy-MM-dd_HH-mm-ss'),
        ]
          .filter((part) => !!part)
          .join('_');
      },
      exportHeaders() {
        return [
          {field: 'period_label', label: this.periodHeaderLabel},
          {field: 'period_hint', label: 'Period details'},
          {field: 'usage_kwh', label: 'Usage [kWh]'},
          {field: 'average_price_pln_per_kwh', label: 'Average price [PLN/kWh]'},
          {field: 'cost_total_pln', label: 'Cost [PLN]'},
          {field: 'top_zone', label: 'Top zone'},
          {field: 'top_component', label: 'Top component'},
          {field: 'by_zone', label: 'Zone breakdown'},
          {field: 'by_component', label: 'Component breakdown'},
          {field: 'compare_label', label: 'Comparison period'},
          {field: 'compare_usage_kwh', label: 'Comparison usage [kWh]'},
          {field: 'compare_average_price_pln_per_kwh', label: 'Comparison average [PLN/kWh]'},
          {field: 'compare_cost_total_pln', label: 'Comparison cost [PLN]'},
          {field: 'compare_cost_delta_pln', label: 'Cost delta [PLN]'},
        ];
      },
      trendComponents() {
        const totals = this.rows.reduce((acc, row) => {
          Object.entries(row.byComponent || {}).forEach(([component, value]) => {
            acc[component] = (acc[component] || 0) + value;
          });
          return acc;
        }, {});
        return Object.keys(totals).sort((a, b) => totals[b] - totals[a]);
      },
    },
    async mounted() {
      await this.refresh();
    },
    beforeUnmount() {
      this.trendChartInstance?.destroy();
      this.donutChartInstance?.destroy();
    },
    methods: {
      formatDateTime,
      async refresh() {
        this.loading = true;
        try {
          const afterTimestamp = Math.floor(DateTime.fromISO(this.dateRange.dateStart).toSeconds());
          const beforeTimestamp = Math.floor(DateTime.fromISO(this.dateRange.dateEnd).toSeconds());
          this.rawBillingSummaries = await energyTariffsApi.getEnergyCostSummaries(this.channel.id, {afterTimestamp, beforeTimestamp});
          this.rawCostLogs = await this.fetchAllCostLogs(afterTimestamp, beforeTimestamp);
          this.comparisonRows = await this.loadComparisonRows(afterTimestamp, beforeTimestamp);
          this.rebuildRows();
        } finally {
          this.loading = false;
        }
      },
      async fetchAllCostLogs(afterTimestamp, beforeTimestamp) {
        const all = [];
        let offset = 0;
        const limit = 5000;
        while (true) {
          const chunk = await energyTariffsApi.getEnergyCostLogs(this.channel.id, {
            afterTimestamp,
            beforeTimestamp,
            order: 'ASC',
            limit,
            offset,
          });
          all.push(...chunk);
          if (chunk.length < limit) {
            break;
          }
          offset += limit;
        }
        return all;
      },
      applyQuickRange(range) {
        this.dateRange = {
          dateStart: DateTime.now().minus({days: range.days}).startOf('day').toISO(),
          dateEnd: DateTime.now().endOf('day').toISO(),
        };
        this.refresh();
      },
      changePeriodMode(mode) {
        this.periodMode = mode;
        this.rebuildRows();
      },
      changeCompareMode(mode) {
        this.compareMode = mode;
        this.refresh();
      },
      changeBreakdownMode(mode) {
        this.breakdownMode = mode;
        this.renderCharts();
      },
      rebuildRows() {
        this.rows = this.periodMode === 'billing' ? this.buildBillingRows() : this.buildAggregatedRows(this.periodMode);
        this.renderCharts();
      },
      async loadComparisonRows(afterTimestamp, beforeTimestamp) {
        if (this.compareMode === 'none') {
          return [];
        }

        const start = DateTime.fromSeconds(afterTimestamp);
        const end = DateTime.fromSeconds(beforeTimestamp);
        const shifted =
          this.compareMode === 'previousYear'
            ? {start: start.minus({year: 1}), end: end.minus({year: 1})}
            : {start: start.minus({seconds: end.diff(start, 'seconds').seconds}), end: start};

        if (this.periodMode === 'billing') {
          return this.buildBillingRowsFromData(
            await energyTariffsApi.getEnergyCostSummaries(this.channel.id, {
              afterTimestamp: Math.floor(shifted.start.toSeconds()),
              beforeTimestamp: Math.floor(shifted.end.toSeconds()),
            })
          );
        }

        return this.buildAggregatedRows(
          this.periodMode,
          await this.fetchAllCostLogs(Math.floor(shifted.start.toSeconds()), Math.floor(shifted.end.toSeconds()))
        );
      },
      buildBillingRowsFromData(sourceRows) {
        return sourceRows.map((row) => {
          const usageKwh = row.usage?.totalKwh || 0;
          const costTotal = row.costs?.total || 0;
          return {
            key: `${row.periodStart}-${row.timezone}`,
            label: `${this.formatBillingBoundary(row.periodStart, row.timezone)} - ${this.formatBillingBoundary(row.periodEnd, row.timezone)}`,
            periodHint: this.$t('Timezone') + `: ${row.timezone}`,
            usageKwh,
            costTotal,
            averagePrice: usageKwh > 0 ? costTotal / usageKwh : 0,
            byComponent: row.costs?.byComponent || {},
            byZone: row.costs?.byZone || {},
            byPhase: row.costs?.byPhase || {},
            topZone: this.topKey(row.costs?.byZone || {}),
            topComponent: this.topKey(row.costs?.byComponent || {}),
            timestamp: DateTime.fromISO(row.periodStart).toMillis(),
          };
        });
      },
      buildBillingRows() {
        return this.buildBillingRowsFromData(this.rawBillingSummaries);
      },
      buildAggregatedRows(mode, sourceLogs = this.rawCostLogs) {
        const groups = {};
        sourceLogs.forEach((row) => {
          const slot = DateTime.fromSeconds(row.slotStartTimestamp || row.dateTimestamp);
          const periodStart = slot.startOf(mode === 'week' ? 'week' : mode);
          const key = periodStart.toISO();
          if (!groups[key]) {
            groups[key] = {
              key,
              timestamp: periodStart.toMillis(),
              label: this.formatPeriodLabel(periodStart, mode),
              periodHint: this.formatPeriodHint(periodStart, mode),
              usageKwh: 0,
              costTotal: 0,
              averagePrice: 0,
              byComponent: {},
              byZone: {},
              byPhase: {phase1: 0, phase2: 0, phase3: 0},
            };
          }
          groups[key].usageKwh += row.usage?.totalKwh || 0;
          if (row.costs) {
            groups[key].costTotal += row.costs.total || 0;
            Object.entries(row.costs.byComponent || {}).forEach(([component, value]) => {
              groups[key].byComponent[component] = (groups[key].byComponent[component] || 0) + value;
            });
            Object.entries(row.costs.byZone || {}).forEach(([zone, value]) => {
              groups[key].byZone[zone] = (groups[key].byZone[zone] || 0) + value;
            });
            Object.entries(row.costs.byPhase || {}).forEach(([phase, value]) => {
              groups[key].byPhase[phase] = (groups[key].byPhase[phase] || 0) + value;
            });
          }
        });

        return Object.values(groups)
          .map((row) => ({
            ...row,
            averagePrice: row.usageKwh > 0 ? row.costTotal / row.usageKwh : 0,
            topZone: this.topKey(row.byZone),
            topComponent: this.topKey(row.byComponent),
          }))
          .sort((a, b) => a.timestamp - b.timestamp);
      },
      formatPeriodLabel(periodStart, mode) {
        if (mode === 'month') {
          return periodStart.toFormat('LLLL yyyy');
        }
        if (mode === 'week') {
          return `${this.$t('Week')} ${periodStart.weekNumber}, ${periodStart.year}`;
        }
        return periodStart.toFormat('dd LLL yyyy');
      },
      formatPeriodHint(periodStart, mode) {
        if (mode === 'week') {
          return `${periodStart.startOf('week').toFormat('dd LLL')} - ${periodStart.endOf('week').toFormat('dd LLL yyyy')}`;
        }
        if (mode === 'month') {
          return `${periodStart.startOf('month').toFormat('dd LLL')} - ${periodStart.endOf('month').toFormat('dd LLL yyyy')}`;
        }
        return periodStart.toLocaleString(DateTime.DATE_HUGE);
      },
      formatBillingBoundary(datetime, timezone) {
        const dateTime = DateTime.fromISO(datetime, {setZone: true}).setZone(timezone);
        if (dateTime.hour === 0 && dateTime.minute === 0) {
          return dateTime.toLocaleString(DateTime.DATE_MED);
        }
        return dateTime.toLocaleString(DateTime.DATETIME_SHORT);
      },
      topKey(values) {
        return Object.entries(values).sort((a, b) => b[1] - a[1])[0]?.[0] || null;
      },
      renderCharts() {
        this.$nextTick(() => {
          this.renderTrendChart();
          this.renderDonutChart();
        });
      },
      renderTrendChart() {
        this.trendChartInstance?.destroy();
        if (!this.$refs.trendChart || !this.rows.length || this.visualMode === 'table') {
          return;
        }
        const categories = this.rows.map((row) => row.label);
        const usageSeries = this.rows.map((row) => Number(row.usageKwh.toFixed(3)));
        const comparisonCostSeries = this.alignComparisonSeries(this.rows, this.comparisonRows, 'costTotal');
        const componentPalette = ['#1f7a4f', '#e76f51', '#3a86ff', '#ffb703', '#8338ec', '#06d6a0', '#ef476f', '#118ab2'];
        const componentSeries = this.trendComponents.map((component, index) => ({
          name: component.replaceAll('_', ' '),
          data: this.rows.map((row) => Number((row.byComponent?.[component] || 0).toFixed(2))),
          type: 'bar',
          color: componentPalette[index % componentPalette.length],
        }));
        const usageMetric = {name: this.$t('Usage'), data: usageSeries, type: 'line', color: '#ffbf00'};
        const comparisonMetric =
          this.compareMode === 'none' ? null : {name: this.$t('Comparison cost'), data: comparisonCostSeries, type: 'line', color: '#5b7c99'};
        const allSeries = [...componentSeries, usageMetric].concat(comparisonMetric ? [comparisonMetric] : []);

        this.trendChartInstance = new ApexCharts(this.$refs.trendChart, {
          chart: {type: 'line', height: 420, toolbar: {show: false}, animations: {enabled: false}, stacked: true},
          series: allSeries,
          stroke: {
            curve: 'smooth',
            width: componentSeries
              .map(() => 0)
              .concat([4])
              .concat(comparisonMetric ? [3] : []),
            dashArray: componentSeries
              .map(() => 0)
              .concat([0])
              .concat(comparisonMetric ? [7] : []),
          },
          fill: {
            opacity: componentSeries
              .map(() => 0.95)
              .concat([1])
              .concat(comparisonMetric ? [0] : []),
          },
          dataLabels: {enabled: false},
          xaxis: {categories, labels: {rotate: -25}},
          yaxis: allSeries.map((series, index) => {
            const isUsageAxis = index === componentSeries.length;
            return {
              seriesName: series.name,
              show: index === 0 || isUsageAxis,
              opposite: isUsageAxis,
              min: 0,
              forceNiceScale: true,
              title: {
                text: index === 0 ? this.$t('Cost') + ' [PLN]' : isUsageAxis ? this.$t('Usage') + ' [kWh]' : '',
              },
              labels: {
                show: index === 0 || isUsageAxis,
                formatter: (value) => (isUsageAxis ? Number(value).toFixed(1) : Number(value).toFixed(0)),
              },
            };
          }),
          tooltip: {
            shared: true,
            intersect: false,
            y: {
              formatter: (value, {seriesIndex}) => {
                if (seriesIndex < componentSeries.length) {
                  return this.formatCurrency(value);
                }
                if (seriesIndex === componentSeries.length) {
                  return this.formatUsage(value);
                }
                return this.formatCurrency(value);
              },
            },
          },
          colors: componentSeries
            .map((series) => series.color)
            .concat([usageMetric.color])
            .concat(comparisonMetric ? [comparisonMetric.color] : []),
          plotOptions: {bar: {columnWidth: '58%', borderRadius: 4}},
          markers: {
            size: componentSeries
              .map(() => 0)
              .concat([6])
              .concat(comparisonMetric ? [4] : []),
            strokeWidth: 0,
            hover: {sizeOffset: 2},
          },
          legend: {position: 'top'},
          grid: {borderColor: 'rgba(0,0,0,0.08)'},
        });
        this.trendChartInstance.render();
      },
      renderDonutChart() {
        this.donutChartInstance?.destroy();
        if (!this.$refs.donutChart || !this.topBreakdownEntries.length) {
          return;
        }
        this.donutChartInstance = new ApexCharts(this.$refs.donutChart, {
          chart: {type: 'donut', height: 320, animations: {enabled: false}},
          series: this.topBreakdownEntries.map((entry) => Number(entry.value.toFixed(2))),
          labels: this.topBreakdownEntries.map((entry) => entry.label),
          legend: {position: 'bottom'},
          dataLabels: {enabled: false},
          colors: ['#1f7a4f', '#f39c12', '#6a4cff', '#ff6b6b', '#2c7be5'],
          plotOptions: {
            pie: {
              donut: {
                size: '68%',
                labels: {
                  show: true,
                  total: {
                    show: true,
                    label: this.$t('Total'),
                    formatter: () => this.formatCurrency(this.rows.reduce((sum, row) => sum + row.costTotal, 0)),
                  },
                },
              },
            },
          },
        });
        this.donutChartInstance.render();
      },
      formatUsage(value) {
        return `${Number(value || 0).toFixed(1)} kWh`;
      },
      formatCurrency(value) {
        return `${Number(value || 0).toFixed(2)} PLN`;
      },
      formatAverage(value) {
        return `${Number(value || 0).toFixed(2)} PLN/kWh`;
      },
      formatSignedCurrency(value) {
        const amount = Number(value || 0);
        return `${amount >= 0 ? '+' : ''}${amount.toFixed(2)} PLN`;
      },
      exportRows(format) {
        const worksheet = XLSX.utils.json_to_sheet(this.buildExportRows(), {
          header: this.exportHeaders.map((header) => header.field),
          dateNF: 'yyyy"-"mm"-"dd hh":"mm":"ss',
        });
        XLSX.utils.sheet_add_aoa(worksheet, [this.exportHeaders.map((header) => this.$t(header.label))], {origin: 'A1'});
        const workbook = XLSX.utils.book_new();
        XLSX.utils.book_append_sheet(workbook, worksheet, this.$t(this.periodHeaderLabel).substring(0, 30));
        XLSX.writeFile(workbook, `${this.exportFilename}.${format}`, {compression: true, FS: format === 'csv' ? ';' : undefined});
      },
      buildExportRows() {
        return this.rows.map((row, index) => {
          const comparison = this.comparisonRows[index];
          return {
            period_label: row.label,
            period_hint: row.periodHint,
            usage_kwh: Number(row.usageKwh.toFixed(3)),
            average_price_pln_per_kwh: Number(row.averagePrice.toFixed(4)),
            cost_total_pln: Number(row.costTotal.toFixed(2)),
            top_zone: row.topZone || '',
            top_component: row.topComponent || '',
            by_zone: Object.entries(row.byZone || {})
              .map(([zone, value]) => `${zone}: ${Number(value).toFixed(2)} PLN`)
              .join(' | '),
            by_component: Object.entries(row.byComponent || {})
              .map(([component, value]) => `${component}: ${Number(value).toFixed(2)} PLN`)
              .join(' | '),
            compare_label: comparison?.label || '',
            compare_usage_kwh: comparison ? Number(comparison.usageKwh.toFixed(3)) : '',
            compare_average_price_pln_per_kwh: comparison ? Number(comparison.averagePrice.toFixed(4)) : '',
            compare_cost_total_pln: comparison ? Number(comparison.costTotal.toFixed(2)) : '',
            compare_cost_delta_pln: comparison ? Number((row.costTotal - comparison.costTotal).toFixed(2)) : '',
          };
        });
      },
      alignComparisonSeries(baseRows, comparisonRows, field) {
        if (!comparisonRows.length) {
          return [];
        }
        return baseRows.map((row, index) => Number(Number(comparisonRows[index]?.[field] || 0).toFixed(3)));
      },
    },
  };
</script>

<style lang="scss">
  .channel-costs-tab {
    .costs-hero {
      display: flex;
      justify-content: space-between;
      gap: 2rem;
      align-items: flex-start;
      padding: 2rem;
      border-radius: 24px;
      background: radial-gradient(circle at top right, rgba(255, 188, 66, 0.4), transparent 30%), linear-gradient(135deg, #133b2c, #1d5d46 55%, #2a7d5f);
      color: #fff;
      margin-top: 1.5rem;
    }

    .costs-hero h2 {
      font-size: 3rem;
      margin: 0.25rem 0 0.5rem;
      font-weight: 700;
    }

    .costs-hero p {
      max-width: 42rem;
      margin: 0;
      color: rgba(255, 255, 255, 0.82);
    }

    .eyebrow {
      text-transform: uppercase;
      letter-spacing: 0.15em;
      font-size: 0.8rem;
      color: rgba(255, 255, 255, 0.72);
    }

    .hero-actions {
      min-width: min(100%, 22rem);
    }

    .wrapped-mode-group {
      display: flex;
      flex-wrap: wrap;
      gap: 0.5rem;
    }

    .btn-orange {
      background: #ffb100;
      color: #173f31;
      border-color: #ffb100;
      font-weight: 700;
    }

    .filter-block,
    .chart-panel,
    .summary-card,
    .mobile-period-card,
    .empty-panel {
      border-radius: 20px;
    }

    .filter-block {
      margin-top: 1.5rem;
    }

    .quick-range-grid {
      display: flex;
      flex-wrap: wrap;
      justify-content: flex-end;
      gap: 0.5rem;
    }

    .summary-card {
      padding: 1.25rem;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(244, 247, 245, 0.95));
      box-shadow: 0 18px 40px rgba(23, 63, 49, 0.08);
      min-height: 10rem;
    }

    .summary-card__label {
      color: #5f7168;
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
    }

    .summary-card__value {
      font-size: 2rem;
      font-weight: 700;
      margin-top: 0.5rem;
      color: #11382b;
    }

    .summary-card__note {
      margin-top: 0.5rem;
      color: #6b7d75;
      font-size: 0.92rem;
    }

    .panel-toolbar {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      gap: 1rem;
      margin-bottom: 1rem;
    }

    .panel-toolbar--compact {
      align-items: center;
    }

    .breakdown-list__item {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      padding: 0.5rem 0;
      border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    }

    .costs-table .table th {
      border-top: 0;
      color: #60736a;
      text-transform: uppercase;
      font-size: 0.8rem;
      letter-spacing: 0.06em;
    }

    .cost-cell {
      color: #156c43;
      font-weight: 700;
    }

    .pill {
      display: inline-flex;
      align-items: center;
      padding: 0.25rem 0.65rem;
      border-radius: 999px;
      background: rgba(33, 128, 86, 0.12);
      color: #185339;
      font-size: 0.82rem;
      font-weight: 600;
    }

    .pill-muted {
      background: rgba(106, 76, 255, 0.08);
      color: #5a48c8;
    }

    .mobile-period-card {
      padding: 1rem;
      border: 1px solid rgba(0, 0, 0, 0.06);
      margin-bottom: 1rem;
      background: rgba(255, 255, 255, 0.96);
    }

    .mobile-period-card__header {
      display: flex;
      justify-content: space-between;
      gap: 1rem;
      align-items: center;
    }

    .mobile-period-card__hint {
      margin-top: 0.35rem;
      color: #6f8178;
      font-size: 0.9rem;
    }

    .mobile-period-card__grid {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 0.75rem;
      margin-top: 1rem;
    }

    .mobile-period-card__grid span {
      display: block;
      color: #6f8178;
      font-size: 0.8rem;
      margin-bottom: 0.15rem;
    }

    .empty-panel {
      text-align: center;
      padding: 4rem 2rem;
      background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(246, 248, 247, 0.95));
    }

    @media (max-width: 991px) {
      .costs-hero,
      .panel-toolbar {
        flex-direction: column;
      }

      .costs-hero h2 {
        font-size: 2.25rem;
      }

      .quick-range-grid {
        justify-content: flex-start;
      }
    }
  }
</style>
