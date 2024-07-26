<template>
    <div class="container">
        <pending-changes-page
            :header="$t('OCR settings')"
            dont-set-page-title
            @cancel="cancelChanges()"
            @save="saveOcrSettings()"
            :is-pending="hasPendingChanges">
            <loading-cover :loading="!latestImageDetails && !latestImageError">
                <transition-expand>
                    <div class="row" v-if="conflictingConfig">
                        <div class="col-sm-6 col-sm-offset-3">
                            <ConfigConflictWarning @refresh="replaceConfigWithConflictingConfig()"/>
                        </div>
                    </div>
                </transition-expand>

                <div class="row">
                    <div class="col-md-6 col-md-offset-3">

                        <div class="form-group">
                            <label for="">{{ $t('Photo interval') }}</label>
                            <NumberInput v-model="ocrSettings.photoIntervalSec"
                                :min="5"
                                :max="300"
                                suffix=" s"
                                class="form-control text-center mt-2"
                                @input="onChange()"/>
                        </div>

                        <div class="form-group">
                            <label for="">{{ $t('Lighting mode') }}</label>
                            <div class="dropdown">
                                <button class="btn btn-default dropdown-toggle btn-block btn-wrapped" type="button"
                                    data-toggle="dropdown">
                                    {{ $t(`ocrLightingMode_${ocrSettings.lightingMode}`) }}
                                    <span class="caret"></span>
                                </button>
                                <!-- i18n: ['timeMarginMode_off', 'timeMarginMode_device', 'timeMarginMode_custom'] -->
                                <ul class="dropdown-menu">
                                    <li v-for="mode in ocrSettings.availableLightingModes" :key="mode">
                                        <a @click="ocrSettings.lightingMode = mode; onChange()"
                                            v-show="mode !== ocrSettings.lightingMode">
                                            {{ $t(`ocrLightingMode_${mode}`) }}
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="">{{ $t('Lighting level') }}</label>
                            <NumberInput v-model="ocrSettings.lightingLevel"
                                :min="1"
                                :max="100"
                                suffix=" %"
                                class="form-control text-center mt-2"
                                @input="onChange()"/>
                        </div>

                        <div class="form-group">
                            <label for="">{{ $t('The number of decimal points') }}</label>
                            <NumberInput v-model="ocrSettings.decimalPoints"
                                :min="0"
                                :max="10"
                                class="form-control text-center mt-2"
                                @input="onChange()"/>
                        </div>

                        <div v-if="latestImageError">
                            <div class="alert alert-warning">
                                {{ $t(latestImageError.message, latestImageError.details) }}
                            </div>
                        </div>

                        <div v-else-if="latestImageDetails">
                            <div class="form-group">
                                <label>{{ $t('Image area crop') }}</label>
                                <div
                                    class="help-block">{{ $t('This is the last photo sent from the photo device. Please select the area where our system should look for the measurement.') }}
                                </div>
                                <div class="ocr-photo-crop-area">
                                    <OcrPhotoCrop v-model="ocrSettings.photoSettings" @input="onChange()"
                                        :image-base64="latestImageDetails.image"/>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </loading-cover>
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
    import NumberInput from "@/common/number-input.vue";

    export default {
        components: {
            NumberInput,
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
                latestImageError: undefined,
                latestImageDetails: undefined,
            }
        },
        beforeMount() {
            this.cancelChanges();
        },
        methods: {
            saveOcrSettings() {
                return this.$http.put(`channels/${this.subject.id}`, {
                    config: {ocr: this.ocrSettings},
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
            fetchLatestImage() {
                this.latestImageError = undefined;
                this.latestImageDetails = undefined;
                this.$http.get(`integrations/ocr/${this.subject.id}/latest`, {skipErrorHandler: true})
                    .then(response => this.latestImageDetails = response.body)
                    .catch(error => this.latestImageError = error.body);
            },
            onChange() {
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.ocrSettings = deepCopy(this.subject.config.ocr || {});
                this.hasPendingChanges = false;
                this.fetchLatestImage();
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
        max-height: 450px;
    }
</style>
