<template>
    <div>
        <a class="btn btn-default mx-1" @click="showDownloadConfig = true">
            <fa icon="gear" class="mr-1"/>
            {{ $t('Download the history of measurement') }}
        </a>
        <button @click="deleteConfirm = true"
            type="button"
            class="btn btn-red ml-1">
            <i class="pe-7s-trash"></i>
            {{ $t('Delete measurement history') }}
        </button>
        <div class="text-left">
            <modal-confirm v-if="deleteConfirm"
                class="modal-warning"
                @confirm="deleteMeasurements()"
                @cancel="deleteConfirm = false"
                :header="$t('Are you sure you want to delete the entire measurement history saved for this channel?')"/>

            <modal v-if="showDownloadConfig" @confirm="showDownloadConfig = false" :header="$t('Download measurements history')">
                <div class="form-group">
                    <label>{{ $t('File format') }}</label>
                    <div class="radio d-flex justify-content-center">
                        <label class="mx-3"><input type="radio" value="csv" v-model="downloadConfig.format"> CSV</label>
                        <label class="mx-3"><input type="radio" value="xlsx" v-model="downloadConfig.format"> XLSX</label>
                        <label class="mx-3"><input type="radio" value="ods" v-model="downloadConfig.format"> ODS</label>
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

                <div class="text-center form-group">
                    <a :href="`/api/channels/${channel.id}/measurement-logs-download?${downloadParams}&` | withDownloadAccessToken"
                        target="_blank"
                        class="btn btn-default">
                        <fa icon="download" class="mr-1"/>
                        {{ $t('Download the history of measurement') }}
                    </a>
                </div>
            </modal>
        </div>
    </div>
</template>

<script>
    import {successNotification} from "@/common/notifier";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    export default {
        components: {TransitionExpand},
        props: {
            channel: Object,
        },
        data() {
            return {
                deleteConfirm: false,
                showDownloadConfig: false,
                downloadConfig: {
                    format: 'csv',
                    separator: ',',
                },
            };
        },
        methods: {
            deleteMeasurements() {
                this.deleteConfirm = false;
                this.$http.delete(`channels/${this.channel.id}/measurement-logs`)
                    .then(() => successNotification(this.$t('Success'), this.$t('The measurement history has been deleted.')))
                    .then(() => this.$emit('delete'));
            }
        },
        computed: {
            downloadParams() {
                let params = `format=${this.downloadConfig.format}`;
                if (this.downloadConfig.format === 'csv') {
                    params += `&separator=${this.downloadConfig.separator}`;
                }
                return params;
            }
        }
    };
</script>

<style lang="scss" scoped>
</style>
