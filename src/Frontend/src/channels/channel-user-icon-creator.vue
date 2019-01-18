<template>
    <loading-cover :loading="uploading">
        <div class="form-group">
            <div class="row">
                <div :class="'col-sm-' + (12 / possibleStates.length)"
                    v-for="(possibleState, stateIndex) in possibleStates">
                    <!-- i18n:['state-on','state-off','state-opened','state-closed','state-partially_closed','state-default','state-empty','state-full'] -->
                    <!-- i18n:['state-revealed','state-shut','state-rgb_on_dim_on','state-rgb_on_dim_off','state-rgb_off_dim_on','state-rgb_off_dim_off'] -->
                    <h5 class="no-margin-top"
                        v-if="possibleStates.length > 1">
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
        <div class="checkbox checkbox-green">
            <label>
                <input type="checkbox"
                    v-model="fileCopyrightConfirmed">
                <span class="checkmark"></span>
                {{ $t('Uploaded files do not contain any inappropriate or copyrighted content, nor do they violate Third Party Rights and I have the right to use them.') }}
            </label>
        </div>
        <p class="text-muted">{{ $t('We will try to display the received icons the best possible way, however you will obtain the greatest results by sending us over a PNG file with a transparent background, width {width}px and height {height}.', {width: 210, height: 156}) }}</p>
        <p class="text-danger"
            v-if="filesTooBig">{{ $t('The set of icons you have chosen is too large. Maximum upload limit is {limit}.', {limit: maxUploadSizeTotalPretty}) }}</p>
        <div class="row">
            <div class="col-xs-12">
                <button class="btn btn-green"
                    type="button"
                    :disabled="!fileCopyrightConfirmed || filesTooBig"
                    @click="uploadIcons()">
                    {{ $t(icon ? 'Save' : 'Add') }}
                </button>
                <a class="btn btn-red"
                    v-if="icon"
                    @click="deleteConfirm = true">
                    {{ $t('Delete') }}
                </a>
                <a class="btn btn-white"
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
    import Vue from "vue";

    export default {
        props: ['model', 'icon'],
        data() {
            return {
                deleteConfirm: false,
                images: [],
                previews: [],
                uploading: false,
                fileCopyrightConfirmed: false,
                maxUploadSizePerFile: Vue.config.external.max_upload_size.file || 0,
                maxUploadSizeTotal: Vue.config.external.max_upload_size.total || 0,
                filesTooBig: false,
            };
        },
        mounted() {
            if (this.icon) {
                for (let index = 0; index < this.possibleStates.length; index++) {
                    this.previews.push(withDownloadAccessToken(`/api/user-icons/${this.icon.id}/${index}?`));
                }
            }
        },
        methods: {
            onFileChosen(files, index) {
                for (let file of files) {
                    if (['image/jpg', 'image/jpeg', 'image/png', 'image/gif'].indexOf(file.type.toLowerCase()) >= 0) {
                        if (this.maxUploadSizePerFile && file.size > this.maxUploadSizePerFile) {
                            errorNotification(
                                this.$t('File is too large'),
                                this.$t('Maximum filesize limit is {limit}.', {limit: prettyBytes(this.maxUploadSizePerFile)})
                            );
                        } else {
                            this.images[index] = file;
                            this.loadImagePreview(index);
                            if (++index >= this.possibleStates.length) {
                                break;
                            }
                        }
                    }
                }
                const totalSize = this.images.map(i => i.size).reduce((s, a) => s + a, 0);
                this.filesTooBig = totalSize > this.maxUploadSizeTotal;
            },
            loadImagePreview(index) {
                const reader = new FileReader();
                reader.onload = (e) => this.$set(this.previews, index, e.target.result);
                reader.readAsDataURL(this.images[index]);
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
                    return errorNotification(this.$t('Error'), this.$t('You need to provide icons for all states.'));
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
            }
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
