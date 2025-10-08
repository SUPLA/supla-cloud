<template>
  <loading-cover :loading="hasLogs === undefined || fetchingDenseLogs">
    <div class="container">
      <div class="clearfix left-right-header reverse">
        <div class="mb-3">
          <DropdownMenu v-if="supportedChartModes.length > 1" class="d-inline-block">
            <DropdownMenuTrigger class="btn btn-default btn-wrapped">
              {{ $t('Show data') }}:
              {{ $t(chartModeLabels[chartMode] || '') }}
            </DropdownMenuTrigger>
            <ul class="dropdown-menu">
              <li v-for="mode in supportedChartModes" :key="mode" :class="{active: mode === chartMode}">
                <a @click="changeChartMode(mode)">
                  {{ $t(chartModeLabels[mode]) }}
                </a>
              </li>
            </ul>
          </DropdownMenu>
        </div>
        <div class="mb-3">
          <ChannelMeasurementsDownload
            v-if="hasLogs"
            :channel="channel"
            :storage="storage"
            :chart-mode="chartMode"
            :date-range="dateRange"
            @delete="onMeasurementsDelete()"
          />
        </div>
      </div>

      <div v-if="supportsChart && storage && hasStorageSupport">
        <transition-expand>
          <div v-if="fetchingLogsProgress" class="mb-5">
            <div v-if="fetchingLogsProgress === true" class="text-center">
              <label>{{ $t('All logs has been downloaded. Reload the chart to see them.') }}</label>
              <div>
                <a class="btn btn-default" @click="rerender()">{{ $t('Reload the chart') }}</a>
              </div>
            </div>
            <div v-else>
              <div class="form-group text-center">
                <label>{{ $t('You can browse only the most recent logs now. Wait a while for the full history.') }}</label>
              </div>
              <div class="progress">
                <div
                  class="progress-bar progress-bar-success progress-bar-striped active"
                  role="progressbar"
                  aria-valuemin="0"
                  :aria-valuenow="Math.min(100, fetchingLogsProgress)"
                  aria-valuemax="100"
                  :style="{width: Math.min(100, fetchingLogsProgress) + '%'}"
                >
                  <span v-if="fetchingLogsProgress < 100">{{ Math.min(100, Math.round(fetchingLogsProgress)) }}%</span>
                </div>
              </div>
            </div>
          </div>
        </transition-expand>

        <ChannelMeasurementsHistoryChart
          v-if="hasLogs"
          :channel="channel"
          :storage="storage"
          :chart-mode="chartMode"
          @date-range-changed="(d) => (dateRange = d)"
        />
      </div>

      <empty-list-placeholder v-if="hasLogs === false" class="my-5"></empty-list-placeholder>

      <div v-if="!hasStorageSupport" class="alert alert-warning my-5">
        {{
          $t(
            'Your browser does not support client-side database mechanism (IndexedDB). It is required to render the charts and enable advanced export modes. You may need to upgrade your browser or exit private browsing mode to use these features.'
          )
        }}
      </div>
    </div>
  </loading-cover>
</template>

<script>
  import {CHART_TYPES} from './channel-measurements-history-chart-strategies';
  import ChannelMeasurementsDownload from '@/channels/history/channel-measurements-download.vue';
  import {IndexedDbMeasurementLogsStorage} from '@/channels/history/channel-measurements-storage';
  import TransitionExpand from '@/common/gui/transition-expand.vue';
  import ChannelMeasurementsHistoryChart from '@/channels/history/channel-measurements-history-chart.vue';
  import ChannelFunction from '@/common/enums/channel-function';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';
  import EmptyListPlaceholder from '@/common/gui/empty-list-placeholder.vue';
  import DropdownMenu from '@/common/gui/dropdown/dropdown-menu.vue';
  import DropdownMenuTrigger from '@/common/gui/dropdown/dropdown-menu-trigger.vue';

  export default {
    components: {
      DropdownMenuTrigger,
      DropdownMenu,
      EmptyListPlaceholder,
      LoadingCover,
      ChannelMeasurementsHistoryChart,
      TransitionExpand,
      ChannelMeasurementsDownload,
    },
    props: ['channel'],
    data: function () {
      return {
        deleteConfirm: false,
        hasLogs: undefined,
        fetchingDenseLogs: false,
        chartMode: 'default',
        dateRange: {},
        chartModeLabels: {
          fae: 'Forward active energy', // i18n
          rae: 'Reverse active energy', // i18n
          fre: 'Forward reactive energy', // i18n
          rre: 'Reverse reactive energy', // i18n
          fae_rae: 'Arithmetic balance', // i18n
          fae_rae_vector: 'Vector balance', // i18n
          voltageHistory: 'Voltage', // i18n
          currentHistory: 'Current', // i18n
          powerActiveHistory: 'Power active', // i18n
        },
        storage: undefined,
        fetchingLogsProgress: false,
        hasStorageSupport: true,
      };
    },
    computed: {
      supportsChart() {
        return this.channel && CHART_TYPES[this.channel.function.name];
      },
      supportedChartModes() {
        if (this.channel.functionId === ChannelFunction.ELECTRICITYMETER) {
          const modesMap = {
            forwardActiveEnergy: 'fae',
            reverseActiveEnergy: 'rae',
            forwardReactiveEnergy: 'fre',
            reverseReactiveEnergy: 'rre',
          };
          const defaultModes = ['forwardActiveEnergy', 'reverseActiveEnergy', 'forwardReactiveEnergy', 'reverseReactiveEnergy'];
          let availableModes = this.channel.config.countersAvailable || defaultModes;
          const modes = availableModes.filter((mode) => modesMap[mode]).map((mode) => modesMap[mode]);
          if (modes.includes('fae') && modes.includes('rae')) {
            modes.push('fae_rae');
          }
          if (availableModes.includes('forwardActiveEnergyBalanced') && availableModes.includes('reverseActiveEnergyBalanced')) {
            modes.push('fae_rae_vector');
          }
          modes.push('voltageHistory');
          modes.push('currentHistory');
          modes.push('powerActiveHistory');
          return modes;
        }
        return [];
      },
    },
    async mounted() {
      if (this.supportsChart) {
        await this.initializeStorage();
      } else {
        this.hasLogs = true;
      }
      if (this.supportedChartModes.length > 0) {
        this.chartMode = this.supportedChartModes[0];
      }
    },
    methods: {
      async initializeStorage() {
        this.chartStrategy = CHART_TYPES.forChannel(this.channel, this.chartMode);
        this.storage = new IndexedDbMeasurementLogsStorage(this.channel, this.chartMode);
        await this.storage.connect();
        this.hasStorageSupport = await this.storage.checkSupport();
        this.hasLogs = (await this.storage.init()).length > 1;
        if (this.hasLogs && this.hasStorageSupport) {
          this.fetchAllLogs();
        }
      },
      fetchAllLogs() {
        this.storage.fetchOlderLogs((progress) => (this.fetchingLogsProgress = progress)).then((downloaded) => (this.fetchingLogsProgress = downloaded));
      },
      changeChartMode(newMode) {
        this.hasLogs = undefined;
        this.chartMode = newMode;
        this.initializeStorage();
      },
      onMeasurementsDelete() {
        this.hasLogs = false;
      },
      async rerender() {
        this.hasLogs = undefined;
        this.fetchingLogsProgress = false;
        this.$nextTick(async () => (this.hasLogs = (await this.storage.init()).length > 1));
      },
    },
  };
</script>
