<template>
    <div>
        <div class="row">
            <div :class="'col-sm-' + (12 / possibleStates.length)"
                v-for="possibleState in possibleStates">
                <h5 class="no-margin-top">{{ possibleState }}</h5>
                <img :ref="possibleState + 'Preview'"
                    class="icon-preview">
                <input type="file"
                    :ref="possibleState + 'Dropzone'"
                    @change="readUrl(possibleState)">
            </div>
            <!--<a @click="processQueue()">PRO</a>-->
        </div>
        <div class="row">
            <div class="col-xs-12">
                <a class="btn btn-green"
                    @click="uploadIcons()">Wyślij
                </a>
            </div>
        </div>
    </div>
</template>

<script>
    import vueDropzone from 'vue2-dropzone';
    import 'vue2-dropzone/dist/vue2Dropzone.min.css';

    export default {
        props: ['model'],
        components: {vueDropzone},
        data() {
            return {
                dropzoneOptions: {
                    url: '/api/icons',
                    addRemoveLinks: true,
                    acceptedFiles: 'image/*',
                    // autoQueue: false,
                    autoProcessQueue: false,
                    thumbnailHeight: 100,
                    thumbnailWidth: 100,
                    thumbnailMethod: 'contain'
                }
            };
        },

        mounted() {

        },

        methods: {
            readUrl(state) {
                const input = this.$refs[state + 'Dropzone'][0];
                const preview = this.$refs[state + 'Preview'][0];
                var reader = new FileReader();

                reader.onload = function (e) {
                    $(preview).attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);
            },
            uploadIcons() {
                const formData = new FormData();
                for (let [index, possibleState] of this.possibleStates.entries()) {
                    const input = this.$refs[possibleState + 'Dropzone'][0];
                    formData.append('image' + (index + 1), input.files[0], input.files[0].name);
                }
                formData.append('function', this.functionName);
                this.$http.post('channel-icons', formData);
            },
            fileAdded(state, files) {
                for (let possibleState of this.possibleStates) {
                    const currentDropzone = this.$refs[possibleState + 'Dropzone'][0];
                    const currentAcceptedFiles = currentDropzone.getAcceptedFiles();
                    if (currentAcceptedFiles.length > 1) {
                        const possibleStateIndex = this.possibleStates.indexOf(possibleState);
                        for (let i = 1; i < currentAcceptedFiles.length; i++) {
                            currentDropzone.removeFile(currentAcceptedFiles[i]);
                            const possibleTargetState = this.possibleStates[possibleStateIndex + i];
                            if (possibleTargetState) {
                                this.$refs[possibleTargetState + 'Dropzone'][0].manuallyAddFile(currentAcceptedFiles[i]);
                            }
                        }
                    }
                }

                // if (files.length > 1) {
                //     if (state == this.possibleStates[0]) {
                //         this.$refs.openedDropzone[0].removeFile(files[1]);
                //         this.$refs.openedDropzone[0].removeFile(files[2]);
                //         this.$refs.partialDropzone[0].manuallyAddFile(files[1]);
                //         this.$refs.closedDropzone[0].manuallyAddFile(files[2]);
                //     }
                // }
            },
            processQueue() {
                console.log(this.$refs);
            }
        },

        computed: {
            functionName() {
                if (this.model) {
                    if (this.model.function) {
                        return this.model.function.name;
                    } else if (this.model.name) {
                        return this.model.name;
                    } else {
                        return this.model;
                    }
                }
            },
            possibleStates() {
                return this.model.function.possibleVisualStates;
            }
        }
    };
</script>

<style>
    .icon-preview {
        max-width: 100%;
        max-height: 156px;
    }
</style>
