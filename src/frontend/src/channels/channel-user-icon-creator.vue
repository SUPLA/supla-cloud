<template>
    <loading-cover :loading="uploading">
        <div class="btn-group btn-group-flex mb-3">
            <a :class="['btn', mode === 'light' ? 'btn-green' : 'btn-white']" @click="mode = 'light'">{{ $t('Light mode') }}</a>
            <a :class="['btn', mode === 'dark' ? 'btn-green' : 'btn-white']" @click="mode = 'dark'">{{ $t('Dark mode') }}</a>
        </div>
        <div class="form-group" v-show="mode === 'light'">
            <div class="row">
                <div :class="'col-sm-' + (12 / possibleStates.length)"
                    :key="stateIndex"
                    v-for="(possibleState, stateIndex) in possibleStates">
                    <!-- i18n:['state-on','state-off','state-opened','state-closed','state-partially_closed','state-default','state-empty','state-full'] -->
                    <!-- i18n:['state-revealed','state-shut','state-rgb_on_dim_on','state-rgb_on_dim_off','state-rgb_off_dim_on','state-rgb_off_dim_off'] -->
                    <!-- i18n:['state-temperature','state-humidity'] -->
                    <h5 class="no-margin-top" v-if="possibleStates.length > 1">
                        {{ $t(`state-${possibleState}`) }}
                    </h5>
                    <div class="dropbox">
                        <input type="file"
                            multiple
                            accept="image/*"
                            class="input-file"
                            :disabled="uploading"
                            @change="onFileChosen($event.target.files, stateIndex)">
                        <img v-if="previews[stateIndex]"
                            :src="previews[stateIndex]"
                            class="icon-preview">
                        <p v-else>{{ $t('Drag your image(s) here or click to select from the disk') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group" v-show="mode === 'dark'">
            <div class="row">
                <div :class="'col-sm-' + (12 / possibleStates.length)"
                    :key="stateIndex"
                    v-for="(possibleState, stateIndex) in possibleStates">
                    <h5 class="no-margin-top" v-if="possibleStates.length > 1">
                        {{ $t(`state-${possibleState}`) }}
                    </h5>
                    <div class="dropbox">
                        <input type="file"
                            multiple
                            accept="image/*"
                            class="input-file"
                            :disabled="uploading"
                            @change="onFileChosen($event.target.files, stateIndex, true)">
                        <img v-if="previewsDark[stateIndex]"
                            :src="previewsDark[stateIndex]"
                            alt=""
                            class="icon-preview">
                        <p v-else>{{ $t('Drag your image(s) here or click to select from the disk') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <label class="checkbox2 text-left">
            <input type="checkbox"
                v-model="fileCopyrightConfirmed">
            {{ $t('Uploaded files do not contain any inappropriate or copyrighted content, nor do they violate Third Party Rights and I have the right to use them.') }}
        </label>
        <p class="text-muted">{{
                $t('We will try to display the received icons the best possible way, however you will obtain the greatest results by sending us over a PNG file with a transparent background, width {width}px and height {height}.', {
                    width: 210,
                    height: 156
                })
            }}</p>
        <p class="text-danger"
            v-if="filesTooBig">{{ $t('The set of icons you have chosen is too large. Maximum upload limit is {limit}.', {limit: maxUploadSizeTotalPretty}) }}</p>
        <div class="row">
            <div class="col-xs-12">
                <button class="btn btn-green mx-2"
                    type="button"
                    :disabled="!fileCopyrightConfirmed || filesTooBig"
                    @click="uploadIcons()">
                    {{ $t(icon ? 'Save' : 'Add') }}
                </button>
                <a class="btn btn-red mx-2"
                    v-if="icon"
                    @click="deleteConfirm = true">
                    {{ $t('Delete') }}
                </a>
                <a class="btn btn-white mx-2"
                    @click="$emit('cancel')">
                    {{ $t('Cancel') }}
                </a>
            </div>
        </div>
        <modal-confirm v-if="deleteConfirm"
            @confirm="deleteIcon()"
            @cancel="deleteConfirm = false"
            :header="$t('Are you sure you want to delete this icon?')"
            :loading="uploading">
            <p>{{ $t('After deletion of this icon all channels and channel groups that use this icon, will receive default icons.') }}</p>
        </modal-confirm>
    </loading-cover>
</template>

<script>
    import {errorNotification} from "../common/notifier";
    import {prettyBytes, withDownloadAccessToken} from "../common/filters";
    import {mapState} from "pinia";
    import {useFrontendConfigStore} from "@/stores/frontend-config-store";

    export default {
        props: ['model', 'icon'],
        data() {
            return {
                mode: 'light',
                deleteConfirm: false,
                images: [],
                imagesDark: [],
                previews: [],
                previewsDark: [],
                uploading: false,
                fileCopyrightConfirmed: false,
                filesTooBig: false,
            };
        },
        mounted() {
            if (this.icon) {
                for (let index = 0; index < this.possibleStates.length; index++) {
                    this.previews.push(withDownloadAccessToken(`/api/user-icons/${this.icon.id}/${index}?`));
                    this.previewsDark.push(withDownloadAccessToken(`/api/user-icons/${this.icon.id}/${index}?dark=1&`));
                }
            }
        },
        methods: {
            onFileChosen(files, index, isDark = false) {
                for (let file of files) {
                    if (['image/jpg', 'image/jpeg', 'image/png', 'image/gif'].indexOf(file.type.toLowerCase()) >= 0) {
                        if (this.maxUploadSizePerFile && file.size > this.maxUploadSizePerFile) {
                            errorNotification(
                                this.$t('File is too large'),
                                this.$t('Maximum filesize limit is {limit}.', {limit: prettyBytes(this.maxUploadSizePerFile)})
                            );
                        } else {
                            if (isDark) {
                                this.imagesDark[index] = file;
                            } else {
                                this.images[index] = file;
                            }
                            this.loadImagePreview(index, isDark);
                            if (++index >= this.possibleStates.length) {
                                break;
                            }
                        }
                    }
                }
                const totalSize = this.images.map(i => i.size).reduce((s, a) => s + a, 0);
                this.filesTooBig = totalSize > this.maxUploadSizeTotal;
            },
            loadImagePreview(index, isDark = false) {
                const reader = new FileReader();
                if (isDark) {
                    reader.onload = (e) => this.$set(this.previewsDark, index, e.target.result);
                    reader.readAsDataURL(this.imagesDark[index]);
                } else {
                    reader.onload = (e) => this.$set(this.previews, index, e.target.result);
                    reader.readAsDataURL(this.images[index]);
                }
            },
            uploadIcons() {
                const formData = new FormData();
                let addedImages = 0;
                for (let [index, image] of this.images.entries()) {
                    if (image) {
                        formData.append('image' + (index + 1), image, image.name);
                        ++addedImages;
                    }
                }
                if (!this.icon && addedImages < this.possibleStates.length) {
                    return errorNotification(this.$t('Error'), this.$t('You need to provide icons for all states in light mode.'));
                }
                for (let [index, image] of this.imagesDark.entries()) {
                    if (image) {
                        formData.append('imageDark' + (index + 1), image, image.name);
                    }
                }
                this.uploading = true;
                formData.append('function', this.model.function.name);
                if (this.icon) {
                    formData.append('sourceIcon', this.icon.id);
                }
                this.$http.post('user-icons', formData)
                    .then((response) => this.$emit('created', response.body))
                    .finally(() => this.uploading = false);
            },
            deleteIcon() {
                this.uploading = true;
                this.$http.delete('user-icons/' + this.icon.id)
                    .then(() => this.$emit('cancel'))
                    .finally(() => this.uploading = false);
            }
        },
        computed: {
            possibleStates() {
                return this.model.function.possibleVisualStates;
            },
            maxUploadSizeTotalPretty() {
                return prettyBytes(this.maxUploadSizeTotal);
            },
            ...mapState(useFrontendConfigStore, {frontendConfig: 'config'}),
            maxUploadSizePerFile() {
                return this.frontendConfig.max_upload_size?.file || 0;
            },
            maxUploadSizeTotal() {
                return this.frontendConfig.max_upload_size?.total || 0;
            },
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .icon-preview {
        max-width: 100%;
        max-height: 156px;
        padding: 5px;
    }

    .dropbox {
        outline: 2px dashed $supla-grey-light !important;
        background: $supla-white;
        color: $supla-grey-dark;
        position: relative;
        cursor: pointer;
        &:hover {
            outline: 2px dashed $supla-green !important;
        }
        p {
            padding: 10px;
        }
    }

    .input-file {
        opacity: 0;
        width: 100%;
        height: 100%;
        position: absolute;
        cursor: pointer;
    }
</style>
