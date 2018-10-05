<template>
    <loading-cover :loading="uploading">
        <div class="form-group">
            <div class="row">
                <div :class="'col-sm-' + (12 / possibleStates.length)"
                    v-for="(possibleState, stateIndex) in possibleStates">
                    <h5 class="no-margin-top"
                        v-if="possibleStates.length > 1">
                        {{ $t('state-' + possibleState) }}
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
                        <p v-else>{{ $t('Drag your image(s) here or click to browse') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <p class="text-muted">{{ $t('We will do our best, but you will end up with the best icons if you upload PNG files with transparent background and size {width}px (width) and {height}px (height).', {width: 210, height: 156}) }}</p>
        <div class="row">
            <div class="col-xs-12">
                <a class="btn btn-green"
                    @click="uploadIcons()">
                    {{ $t(icon ? 'Save' : 'Add') }}
                </a>
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
            <p>{{ $t('All channels or channel groups that use this icon will return to the default icon after deletion.') }}</p>
        </modal-confirm>
    </loading-cover>
</template>

<script>
    import {errorNotification} from "../common/notifier";
    import {withDownloadAccessToken} from "../common/filters";

    export default {
        props: ['model', 'icon'],
        data() {
            return {
                deleteConfirm: false,
                images: [],
                previews: [],
                uploading: false,
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
                    if (file.type.indexOf('image/') === 0) {
                        this.images[index] = file;
                        this.loadImagePreview(index);
                        if (++index >= this.possibleStates.length) {
                            break;
                        }
                    }
                }
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
                    return errorNotification(this.$t('Error'), 'You need to choose icons for all states.');
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
