<template>
    <div class="row">
        <div class="col-sm-4"
            v-for="possibleState in possibleStates">
            <h5 class="no-margin-top">{{ possibleState }}</h5>
            <vue-dropzone :options="dropzoneOptions"
                :id="possibleState + 'Dropzone'"
                :ref="possibleState + 'Dropzone'"
                @vdropzone-files-added="fileAdded(possibleState, $event)">
                <!--<square-link class="clearfix pointer lift-up grey">-->
                <!--<a class="valign-center text-center"-->
                <!--@click="$upload.select('icons')">-->
                <!--<span>-->
                <!--<i class="pe-7s-plus"></i>-->
                <!--{{ $t('Upload') }}-->
                <!--</span>-->
                <!--</a>-->
                <!--</square-link>-->
            </vue-dropzone>
        </div>
        <!--<a @click="processQueue()">PRO</a>-->
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
                return ['opened', 'partial', 'closed'];
            }
        }
    };
</script>
