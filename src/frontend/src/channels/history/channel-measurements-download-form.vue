<template>
    <div>
        <div v-if="supportsFrontendExport">
            <div v-if="storage.isReady">

                <div class="form-group">
                    <label>{{ $t('Date range') }}</label>
                    <div class="radio d-flex justify-content-center">
                        <label class="mx-3"><input type="radio" value="selected"
                            v-model="downloadConfig.dateRange"> {{ $t('Selected') }}</label>
                        <label class="mx-3"><input type="radio" value="all" v-model="downloadConfig.dateRange"> {{ $t('All') }}</label>
                    </div>
                </div>

                <div class="form-group">
                    <label>{{ $t('File format') }}</label>
                    <div class="radio d-flex justify-content-center">
                        <label class="mx-3"><input type="radio" value="csv" v-model="downloadConfig.format"> CSV</label>
                        <label class="mx-3"><input type="radio" value="ods" v-model="downloadConfig.format"> ODS</label>
                        <label class="mx-3"><input type="radio" value="xlsx" v-model="downloadConfig.format"> XLSX</label>
                        <label class="mx-3"><input type="radio" value="html" v-model="downloadConfig.format"> HTML</label>
                    </div>
                </div>

                <transition-expand>
                    <div v-if="downloadConfig.format === 'csv'" class="form-group">
                        <label>{{ $t('Value separator') }}</label>
                        <div class="radio d-flex justify-content-center">
                            <label class="mx-3"><input type="radio" value="," v-model="downloadConfig.separator"> {{ $t('Comma') }}
                                <code>,</code></label>
                            <label class="mx-3"><input type="radio" value=";" v-model="downloadConfig.separator"> {{ $t('Colon') }}
                                <code>;</code></label>
                            <label class="mx-3"><input type="radio" value="tab" v-model="downloadConfig.separator"> {{ $t('Tab') }}
                                <code>\t</code></label>
                        </div>
                    </div>
                </transition-expand>

                <transition-expand>
                    <div v-if="supportsIncrementalLogs" class="form-group">
                        <label>{{ $t('Logs transformation') }}</label>
                        <div class="radio text-center">
                            <label class="mx-3"><input type="radio" value="cumulative" v-model="downloadConfig.transformation">
                                {{ $t('Counter') }}
                                <span class="small">({{ $t('values as seen on counter') }})</span>
                            </label>
                            <label class="mx-3"><input type="radio" value="none" v-model="downloadConfig.transformation">
                                {{ $t('Incremental') }}
                                <span class="small">({{ $t('values as seen on chart') }})</span>
                            </label>
                        </div>
                    </div>
                </transition-expand>

                <div class="text-center mt-4">
                    <a @click="download()" v-if="!downloading" class="btn btn-default">
                        <fa icon="download" class="mr-1"/>
                        {{ $t('Download the history of measurement') }}
                    </a>
                    <span v-else>
                        {{ $t('Your data are being collected. Please be patient.') }}
                    </span>
                    <div v-if="downloadError" class="text-danger mt-3">
                        <p>{{ $t('We were not able to prepare your data. Please see the error details below.') }}</p>
                        {{ downloadError }}
                    </div>
                </div>
            </div>
            <div v-else class="alert alert-info">
                {{ $t('Your data are being fetched from the server. Wait for it to complete before exporting your logs.') }}
            </div>
        </div>
        <div v-else class="text-center">
            <a :href="`/api/channels/${channel.id}/measurement-logs-csv?` | withDownloadAccessToken"
                class="btn btn-default">
                <fa icon="download" class="mr-1"/>
                {{ $t('Download the history of measurement') }}
            </a>
        </div>
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import XLSX from "xlsx";
    import ChannelFunction from "@/common/enums/channel-function";
    import {channelTitle} from "@/common/filters";
    import {DateTime} from "luxon";
    import latinize from "latinize";

    const EXPORT_DEFINITIONS = {
        [ChannelFunction.THERMOMETER]: [
            {field: 'date_timestamp', label: 'Timestamp'},
            {field: 'date', label: 'Date and time'},
            {field: 'temperature', label: 'Temperature'},
        ],
        [ChannelFunction.HUMIDITYANDTEMPERATURE]: [
            {field: 'date_timestamp', label: 'Timestamp'},
            {field: 'date', label: 'Date and time'},
            {field: 'temperature', label: 'Temperature'},
            {field: 'humidity', label: 'Humidity'},
        ],
        [ChannelFunction.HUMIDITY]: [
            {field: 'date_timestamp', label: 'Timestamp'},
            {field: 'date', label: 'Date and time'},
            {field: 'humidity', label: 'Humidity'},
        ],
        [ChannelFunction.ELECTRICITYMETER]: [
            {field: 'date_timestamp', label: 'Timestamp'},
            {field: 'date', label: 'Date and time'},
            {field: 'phase1_fae', label: 'Phase 1 Forward active Energy kWh'},
            {field: 'phase1_rae', label: 'Phase 1 Reverse active Energy kWh'},
            {field: 'phase1_fre', label: 'Phase 1 Forward reactive Energy kvarh'},
            {field: 'phase1_rre', label: 'Phase 1 Reverse reactive Energy kvarh'},
            {field: 'phase2_fae', label: 'Phase 2 Forward active Energy kWh'},
            {field: 'phase2_rae', label: 'Phase 2 Reverse active Energy kWh'},
            {field: 'phase2_fre', label: 'Phase 2 Forward reactive Energy kvarh'},
            {field: 'phase2_rre', label: 'Phase 2 Reverse reactive Energy kvarh'},
            {field: 'phase3_fae', label: 'Phase 3 Forward active Energy kWh'},
            {field: 'phase3_rae', label: 'Phase 3 Reverse active Energy kWh'},
            {field: 'phase3_fre', label: 'Phase 3 Forward reactive Energy kvarh'},
            {field: 'phase3_rre', label: 'Phase 3 Reverse reactive Energy kvarh'},
            {field: 'fae_total', label: 'Forward active Energy kWh - total'},
            {field: 'rae_total', label: 'Reverse active Energy kWh - total'},
            {field: 'fae_rae_balance', label: 'Active Energy kWh - Arithmetic balance'},
            {field: 'fae_balanced', label: 'Forward active Energy kWh - Vector balance'},
            {field: 'rae_balanced', label: 'Reverse active Energy kWh - Vector balance'},
        ],
        [ChannelFunction.ELECTRICITYMETER + 'voltageHistory']: [
            {field: 'date_timestamp', label: 'Timestamp'},
            {field: 'date', label: 'Date and time'},
            {field: 'phase1_min', label: 'Phase 1 - min'},
            {field: 'phase1_max', label: 'Phase 1 - max'},
            {field: 'phase1_avg', label: 'Phase 1 - average'},
            {field: 'phase2_min', label: 'Phase 2 - min'},
            {field: 'phase2_max', label: 'Phase 2 - max'},
            {field: 'phase2_avg', label: 'Phase 2 - average'},
            {field: 'phase3_min', label: 'Phase 3 - min'},
            {field: 'phase3_max', label: 'Phase 3 - max'},
            {field: 'phase3_avg', label: 'Phase 3 - average'},
        ],
        [ChannelFunction.IC_GASMETER]: [
            {field: 'date_timestamp', label: 'Timestamp'},
            {field: 'date', label: 'Date and time'},
            {field: 'counter', label: 'Counter'},
            {field: 'calculated_value', label: 'Calculated value'},
        ],
        [ChannelFunction.GENERAL_PURPOSE_MEASUREMENT]: [
            {field: 'date_timestamp', label: 'Timestamp'},
            {field: 'date', label: 'Date and time'},
            {field: 'avg_value', label: 'Average'},
            {field: 'open_value', label: 'Open value'},
            {field: 'max_value', label: 'High value'},
            {field: 'min_value', label: 'Low value'},
            {field: 'close_value', label: 'Close value'},
        ],
        [ChannelFunction.GENERAL_PURPOSE_METER]: [
            {field: 'date_timestamp', label: 'Timestamp'},
            {field: 'date', label: 'Date and time'},
            {field: 'value', label: 'Value'},
        ],
    };

    EXPORT_DEFINITIONS[ChannelFunction.IC_HEATMETER] = EXPORT_DEFINITIONS[ChannelFunction.IC_GASMETER];
    EXPORT_DEFINITIONS[ChannelFunction.IC_WATERMETER] = EXPORT_DEFINITIONS[ChannelFunction.IC_GASMETER];
    EXPORT_DEFINITIONS[ChannelFunction.IC_ELECTRICITYMETER] = EXPORT_DEFINITIONS[ChannelFunction.IC_GASMETER];
    EXPORT_DEFINITIONS[ChannelFunction.ELECTRICITYMETER + 'currentHistory'] = EXPORT_DEFINITIONS[ChannelFunction.ELECTRICITYMETER + 'voltageHistory'];
    EXPORT_DEFINITIONS[ChannelFunction.ELECTRICITYMETER + 'powerActiveHistory'] = EXPORT_DEFINITIONS[ChannelFunction.ELECTRICITYMETER + 'voltageHistory'];

    const CUSTOM_CHART_MODES = ['voltageHistory', 'currentHistory', 'powerActiveHistory'];

    export default {
        components: {TransitionExpand},
        props: {
            storage: Object,
            dateRange: Object,
            chartMode: String,
        },
        data() {
            return {
                downloading: false,
                downloadError: undefined,
                downloadConfig: {
                    dateRange: 'selected',
                    format: 'csv',
                    separator: ',',
                    transformation: 'none',
                },
            };
        },
        beforeMount() {
            if (this.supportsIncrementalLogs) {
                this.downloadConfig.transformation = 'cumulative';
            }
        },
        methods: {
            async download() {
                this.downloading = true;
                this.downloadError = undefined;
                try {
                    const fromDate = DateTime.fromISO(this.dateRange.dateStart).toJSDate();
                    const toDate = DateTime.fromISO(this.dateRange.dateEnd).toJSDate();
                    const range = IDBKeyRange.bound(fromDate, toDate);
                    const table = this.downloadConfig.transformation === 'cumulative' ? 'logs_raw' : 'logs';
                    let rows = (await (await this.storage.db).getAllFromIndex(table, 'date', this.downloadConfig.dateRange === 'all' ? undefined : range));
                    rows = rows
                        .filter(row => !row.interpolated)
                        .map(row => {
                            delete row.counterReset;
                            return row;
                        });
                    await this.downloadFile(rows);
                    this.$emit('downloaded');
                } catch (e) {
                    this.downloadError = e.toString();
                    throw e;
                } finally {
                    this.downloading = false;
                }
            },
            async downloadFile(rows) {
                const fieldSeparator = this.downloadConfig.separator === 'tab' ? "\t" : this.downloadConfig.separator;
                const columnLabels = this.exportFields.map(f => f.label);
                const jsonFields = this.exportFields.map(f => f.field);
                const worksheet = XLSX.utils.json_to_sheet(rows, {
                    header: jsonFields,
                    dateNF: 'yyyy"-"mm"-"dd" "hh":"mm":"ss',
                });
                XLSX.utils.sheet_add_aoa(worksheet, [columnLabels], {origin: "A1"});
                const workbook = XLSX.utils.book_new();
                const sheetName = channelTitle(this.channel).replace(/[^0-9a-z]/ig, '_').replace(/_+/g, ' ').substr(0, 30);
                XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);
                const filename = `${this.filename}.${this.downloadConfig.format}`;
                XLSX.writeFile(workbook, filename, {compression: true, FS: fieldSeparator});
            },
        },
        computed: {
            channel() {
                return this.storage.channel;
            },
            filename() {
                return [
                    latinize((this.channel.caption || '').toLowerCase().trim()).replace(/[^a-z0-9]/g, '-').replace(/-+/g, '-'),
                    'ID' + this.channel.id,
                    DateTime.now().toFormat('yyyy-dd-MM_HH-mm-ss'),
                    CUSTOM_CHART_MODES.includes(this.chartMode) ? this.chartMode : 'measurements',
                ]
                    .filter(p => !!p)
                    .join('_');
            },
            exportFields() {
                let fncDescriptor = this.channel.functionId;
                if (CUSTOM_CHART_MODES.includes(this.chartMode)) {
                    fncDescriptor += this.chartMode;
                }
                return EXPORT_DEFINITIONS[fncDescriptor];
            },
            supportsFrontendExport() {
                return window.indexedDB && this.storage.hasSupport && !!this.exportFields;
            },
            supportsIncrementalLogs() {
                return !CUSTOM_CHART_MODES.includes(this.chartMode) && [
                    ChannelFunction.IC_WATERMETER,
                    ChannelFunction.IC_HEATMETER,
                    ChannelFunction.IC_GASMETER,
                    ChannelFunction.IC_ELECTRICITYMETER,
                    ChannelFunction.ELECTRICITYMETER,
                    ChannelFunction.GENERAL_PURPOSE_METER,
                ].includes(this.channel.functionId);
            },
        }
    };
</script>
