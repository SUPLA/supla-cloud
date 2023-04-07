<template>
    <div>
        <div class="form-group">
            <label>{{ $t('File format') }}</label>
            <div class="radio d-flex justify-content-center">
                <label class="mx-3"><input type="radio" value="csv" v-model="downloadConfig.format"> CSV</label>
                <label class="mx-3"><input type="radio" value="xlsx" v-model="downloadConfig.format"> XLSX</label>
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

        <div class="text-center form-group">
            <a @click="download()">
                <fa icon="download" class="mr-1"/>
                {{ $t('Download the history of measurement') }}
            </a>
        </div>
    </div>
</template>

<script>
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import XLSX from "xlsx";
    import Papa from "papaparse";

    export default {
        components: {TransitionExpand},
        props: {
            storage: Object,
        },
        data() {
            return {
                downloadConfig: {
                    format: 'csv',
                    separator: ',',
                },
            };
        },
        methods: {
            async download() {
                const rows = await (await this.storage.db).getAllFromIndex('logs', 'date');
                if (this.downloadConfig.format === 'csv') {
                    await this.downloadCsv(rows);
                } else {
                    await this.downloadXlsx(rows);
                }
            },
            async downloadXlsx(rows) {
                const worksheet = XLSX.utils.json_to_sheet(rows);
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, "Dates");
                XLSX.writeFile(workbook, "Presidents.xlsx", {compression: true});
            },
            async downloadCsv(rows) {
                const delimiter = this.downloadConfig.separator === 'tab' ? "\t" : this.downloadConfig.separator;
                const text = Papa.unparse(rows, {columns: ['date_timestamp', 'temperature'], delimiter})
                const element = document.createElement('a');
                element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
                element.setAttribute('download', 'measurement.csv');

                element.style.display = 'none';
                document.body.appendChild(element);

                element.click();

                document.body.removeChild(element);
            }
        },
    };
</script>

<style lang="scss" scoped>
</style>
