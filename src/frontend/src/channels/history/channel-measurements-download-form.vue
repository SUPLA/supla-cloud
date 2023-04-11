<template>
    <div>
        <div v-if="supportsFrontendExport">
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
                <div v-if="supportsCumulativeLogs" class="form-group">
                    <label>{{ $t('Logs transformation') }}</label>
                    <div class="radio text-center">
                        <label class="mx-3"><input type="radio" value="none" v-model="downloadConfig.transformation">
                            {{ $t('Incremental') }}
                            <span class="small">({{ $t('values as seen on chart') }})</span>
                        </label>
                        <label class="mx-3"><input type="radio" value="cumulative" v-model="downloadConfig.transformation">
                            {{ $t('Counter') }}
                            <span class="small">({{ $t('values as seen on counter') }})</span>
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
                    {{ $t('Your data are being collected. Please be patient. ') }}
                </span>
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
            {field: 'fae_balanced', label: 'Forward active Energy kWh - Vector balance'},
            {field: 'rae_balanced', label: 'Reverse active Energy kWh - Vector balance'},
        ],
    };

    export default {
        components: {TransitionExpand},
        props: {
            storage: Object,
        },
        data() {
            return {
                downloading: false,
                downloadConfig: {
                    format: 'csv',
                    separator: ',',
                    transformation: 'none',
                },
            };
        },
        methods: {
            async download() {
                this.downloading = true;
                this.$emit('downloading', true);
                let rows = (await (await this.storage.db).getAllFromIndex('logs', 'date'));
                if (this.downloadConfig.transformation === 'cumulative') {
                    rows = this.storage.chartStrategy.cumulateLogs(rows);
                }
                rows = rows
                    .filter(row => !row.interpolated)
                    .map(row => {
                        delete row.counterReset;
                        return row;
                    });
                await this.downloadFile(rows);
                this.downloading = false;
                this.$emit('downloading', false);
            },
            async downloadFile(rows) {
                const fieldSeparator = this.downloadConfig.separator === 'tab' ? "\t" : this.downloadConfig.separator;
                const columnLabels = this.exportFields.map(f => f.label);
                const jsonFields = this.exportFields.map(f => f.field);
                const worksheet = XLSX.utils.json_to_sheet(rows, {
                    header: jsonFields,
                    dateNF: 'yyyy"-"mm"-"dd" "hh":"mm":"ss',
                    cellDates: true
                });
                XLSX.utils.sheet_add_aoa(worksheet, [columnLabels], {origin: "A1"});
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, channelTitle(this.channel, this));
                const filename = `measurements_${this.channel.id}.${this.downloadConfig.format}`;
                XLSX.writeFile(workbook, filename, {compression: true, FS: fieldSeparator});
            },
        },
        computed: {
            channel() {
                return this.storage.channel;
            },
            exportFields() {
                return EXPORT_DEFINITIONS[this.channel.functionId];
            },
            supportsFrontendExport() {
                return window.indexedDB && !!this.exportFields;
            },
            supportsCumulativeLogs() {
                return [
                    ChannelFunction.ELECTRICITYMETER,
                ].includes(this.channel.functionId);
            },
        }
    };
</script>

<style lang="scss" scoped>
</style>
