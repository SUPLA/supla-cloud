<template>
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

            <div class="form-group text-center">
                <label>
                    <label class="checkbox2 checkbox2-grey">
                        <input type="checkbox" v-model="ocrSettings.enabled" @change="onChange()">
                        {{ $t('Enable support for OCR readings for this channel') }}
                    </label>
                </label>
            </div>
            <transition-expand>
                <div v-if="ocrSettings.enabled">

                    <div class="form-group">
                        <label for="">{{ $t('Photo interval') }}</label>
                        <TimeIntervalSlider v-model="ocrSettings.photoIntervalSec" :min="60" @input="onChange()" seconds
                            class="mt-5"/>
                    </div>

                    <div v-if="ocrSettings.availableLightingModes.length">
                        <div class="form-group">
                            <label for="">{{ $t('Lighting mode') }}</label>
                            <DropdownMenu>
                              <DropdownMenuTrigger class="btn btn-default btn-block btn-wrapped">
                                    {{ $t(`ocrLightingMode_${ocrSettings.lightingMode}`) }}
                              </DropdownMenuTrigger>
                                <!-- i18n: ['ocrLightingMode_OFF', 'ocrLightingMode_AUTO', 'ocrLightingMode_ALWAYS_ON'] -->
                                <ul class="dropdown-menu">
                                    <li v-for="mode in ocrSettings.availableLightingModes" :key="mode">
                                        <a @click="ocrSettings.lightingMode = mode; onChange()"
                                            v-show="mode !== ocrSettings.lightingMode">
                                            {{ $t(`ocrLightingMode_${mode}`) }}
                                        </a>
                                    </li>
                                </ul>
                            </DropdownMenu>
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
                    </div>

                    <div v-if="latestImageError">
                        <div class="alert alert-warning">
                            {{ $t(latestImageError.message, latestImageError.details) }}
                        </div>
                    </div>

                    <div v-else-if="latestImageDetails">
                        <div class="form-group">
                            <label>{{ $t('Image area crop') }}</label>
                            <div class="d-flex align-items-center">
                                <div class="help-block pr-4">
                                    {{ $t('This is the last photo sent from the photo device. Please select the area where our system should look for the measurement.') }}
                                </div>
                                <div>
                                    <button type="button" class="btn btn-white text-nowrap"
                                        :disabled="editingPhoto" @click="editingPhoto = true">
                                        {{ $t('Edit area crop') }}
                                    </button>
                                </div>
                            </div>
                            <div class="ocr-photo-crop-area">
                                <OcrPhotoCrop v-model="ocrSettings.photoSettings" @input="onChange()"
                                    :editable="editingPhoto"
                                    :image-base64="latestImageDetails.image"/>
                            </div>
                        </div>
                    </div>

                    <div class="text-center">
                        <button class="btn btn-white" type="button" @click="takePhotoNow()" :disabled="takingPhoto">
                            {{ $t('Take a photo now') }}
                        </button>
                        <button class="btn btn-white ml-3" type="button" @click="fetchLatestImage()">
                            {{ $t('Reload last photo') }}
                        </button>
                    </div>
                </div>
            </transition-expand>
        </loading-cover>
    </pending-changes-page>
</template>

<script>
  import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
  import OcrPhotoCrop from "@/channels/ocr/ocr-photo-crop.vue";
  import EventBus from "@/common/event-bus";
  import {deepCopy} from "@/common/utils";
  import ConfigConflictWarning from "@/channels/config-conflict-warning.vue";
  import TransitionExpand from "@/common/gui/transition-expand.vue";
  import NumberInput from "@/common/number-input.vue";
  import {successNotification} from "@/common/notifier";
  import {measurementUnit} from "@/channels/channel-helpers";
  import TimeIntervalSlider from "@/scenes/time-interval-slider.vue";
  import {api} from "@/api/api.js";
  import DropdownMenu from "@/common/gui/dropdown/dropdown-menu.vue";
  import DropdownMenuTrigger from "@/common/gui/dropdown/dropdown-menu-trigger.vue";
  import LoadingCover from "@/common/gui/loaders/loading-cover.vue";

  export default {
        components: {
          LoadingCover,
          DropdownMenuTrigger,
          DropdownMenu,
            TimeIntervalSlider,
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
                takingPhoto: false,
                editingPhoto: false,
            }
        },
        beforeMount() {
            this.cancelChanges();
        },
        methods: {
            saveOcrSettings() {
                return api.put(`channels/${this.subject.id}`, {
                    config: {ocr: this.ocrSettings},
                    configBefore: this.subject.configBefore,
                }, {skipErrorHandler: [409]}).then(() => {
                    this.hasPendingChanges = false;
                    this.editingPhoto = false;
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
                api.get(`integrations/ocr/${this.subject.id}/images/latest`, {skipErrorHandler: true})
                    .then(response => this.latestImageDetails = response.body)
                    .catch(error => this.latestImageError = error.body);
            },
            onChange() {
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.editingPhoto = false;
                this.ocrSettings = deepCopy(this.subject.config.ocr || {});
                this.hasPendingChanges = false;
                this.fetchLatestImage();
            },
            replaceConfigWithConflictingConfig() {
                this.subject.config = this.conflictingConfig;
                this.subject.configBefore = deepCopy(this.subject.config);
                this.conflictingConfig = false;
                this.cancelChanges();
            },
            takePhotoNow() {
                this.takingPhoto = true;
                api.patch(`channels/${this.subject.id}/settings`, {action: 'ocr:takePhoto'})
                    .then(() => successNotification(
                        this.$t('Success'),
                        this.$t('Command has been sent to the device. Try refreshing the photo in a while.')
                    ))
                    .finally(() => this.takingPhoto = false);
            },
        },
        computed: {
            unit() {
                return measurementUnit(this.subject);
            }
        }
    };
</script>

<style lang="scss">
    .ocr-photo-crop-area {
        max-height: 450px;
    }
</style>
