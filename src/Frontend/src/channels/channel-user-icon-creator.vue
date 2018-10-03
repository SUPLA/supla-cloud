<template>
    <loading-cover :loading="uploading">
        <div class="form-group">
            <div class="row">
                <div :class="'col-sm-' + (12 / possibleStates.length)"
                    v-for="(possibleState, stateIndex) in possibleStates">
                    <h5 class="no-margin-top">{{ possibleState }}</h5>

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
                        <p v-else>Drag your file(s) here to begin<br> or click to browse</p>
                    </div>

                </div>
            </div>
        </div>
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
                    this.previews.push(`/api/channel-icons/${this.icon.id}/${index}?access_token=${this.$user.getFilesDownloadToken()}`);
                }
            }
        },
        methods: {
            onFileChosen(files, index) {
                for (let file of files) {
                    this.images[index] = file;
                    this.loadImagePreview(index);
                    if (++index >= this.possibleStates.length) {
                        break;
                    }
                }
            },
            loadImagePreview(index) {
                const reader = new FileReader();
                reader.onload = (e) => this.$set(this.previews, index, e.target.result);
                reader.readAsDataURL(this.images[index]);
            },
            uploadIcons() {
                this.uploading = true;
                const formData = new FormData();
                for (let [index, image] of this.images.entries()) {
                    formData.append('image' + (index + 1), image, image.name);
                }
                formData.append('function', this.model.function.name);
                this.$http.post('channel-icons', formData)
                    .then((response) => this.$emit('created', response.body))
                    .finally(() => this.uploading = false);
            },
            deleteIcon() {
                this.uploading = true;
                this.$http.delete('channel-icons/' + this.icon.id)
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
