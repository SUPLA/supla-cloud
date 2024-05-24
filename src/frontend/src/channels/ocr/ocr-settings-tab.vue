<template>
    <div class="container">
        <pending-changes-page
            :header="$t('OCR settings')"
            dont-set-page-title
            @cancel="cancelChanges()"
            @save="saveOcrSettings()"
            :is-pending="hasPendingChanges">

            <transition-expand>
                <div class="row" v-if="conflictingConfig">
                    <div class="col-sm-6 col-sm-offset-3">
                        <ConfigConflictWarning @refresh="replaceConfigWithConflictingConfig()"/>
                    </div>
                </div>
            </transition-expand>

            <div class="form-group">
                <label>{{ $t('Image area crop') }}</label>
                <div
                    class="help-block">{{ $t('This is the last photo sent from the photo device. Please select the area where our system should look for the measurement.') }}
                </div>
                <div class="ocr-photo-crop-area">
                    <OcrPhotoCrop v-model="ocrSettings" @input="hasPendingChanges = true"/>
                </div>
            </div>

        </pending-changes-page>
    </div>
</template>

<script>
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import OcrPhotoCrop from "@/channels/ocr/ocr-photo-crop.vue";
    import EventBus from "@/common/event-bus";
    import {deepCopy} from "@/common/utils";
    import ConfigConflictWarning from "@/channels/config-conflict-warning.vue";
    import TransitionExpand from "@/common/gui/transition-expand.vue";

    export default {
        components: {
            TransitionExpand, ConfigConflictWarning,
            OcrPhotoCrop,
            PendingChangesPage
        },
        props: {
            subject: Object,
        },
        data() {
            return {
                hasPendingChanges: false,
                ocrSettings: undefined,
                conflictingConfig: undefined,
            }
        },
        beforeMount() {
            this.cancelChanges();
        },
        methods: {
            saveOcrSettings() {
                return this.$http.put(`channels/${this.subject.id}`, {
                    config: {
                        ocrSettings: this.ocrSettings,
                    },
                    configBefore: this.subject.configBefore,
                }, {skipErrorHandler: [409]}).then(() => {
                    this.hasPendingChanges = false;
                    EventBus.$emit('channel-updated');
                }).catch(response => {
                    if (response.status === 409) {
                        this.conflictingConfig = response.body.details.config;
                    }
                });
            },
            cancelChanges() {
                this.ocrSettings = deepCopy(this.subject.config.ocrSettings || {});
                this.hasPendingChanges = false;
            },
            replaceConfigWithConflictingConfig() {
                this.subject.config = this.conflictingConfig;
                this.subject.configBefore = deepCopy(this.subject.config);
                this.conflictingConfig = false;
                this.cancelChanges();
            }
        },
        computed: {}
    };
</script>

<style lang="scss">
    .ocr-photo-crop-area {
        margin: 0 auto;
        max-width: 500px;
        max-height: 400px;
    }
</style>
