<template>
    <loading-cover :loading="!cropperReady">
        <div :class="{invisible: !cropperReady}">
            <!--            <img src="https://ocdn.eu/images/pulscms/M2M7MDA_/bd8b7be0ad21e358dcc1d43b5a90672e.jpg" ref="image"/>-->
            <img :src="imageSrc" ref="image"/>
        </div>
        <div :class="[{invisible: !cropperReady}, 'd-flex mt-2']">
            <div class="flex-grow-1">
                <button @click="rotate(-90)" type="button" class="btn btn-default mr-2">
                    <fa icon="rotate-left"/>
                    90&deg;
                </button>
                <button @click="rotate(-1)" type="button" class="btn btn-default">
                    <fa icon="rotate-left"/>
                </button>
            </div>
            <div>
                <button @click="rotate(1)" type="button" class="btn btn-default mr-2">
                    <fa icon="rotate-right"/>
                </button>
                <button @click="rotate(90)" type="button" class="btn btn-default">
                    <fa icon="rotate-right"/>
                    90&deg;
                </button>
            </div>
        </div>
    </loading-cover>
</template>

<script>
    import 'cropperjs/dist/cropper.css';
    import Cropper from 'cropperjs';

    export default {
        components: {},
        props: {
            value: Object,
            imageBase64: String,
        },
        data() {
            return {
                currentData: undefined,
                cropper: undefined,
                cropperReady: false,
                errorDetails: undefined,
            }
        },
        mounted() {
            this.recreateCropper();
        },
        methods: {
            recreateCropper() {
                this.cropperReady = false;
                this.cropper?.destroy();
                this.cropper = new Cropper(this.$refs.image, {
                    guides: false,
                    center: false,
                    scalable: false,
                    autoCropArea: 0.5,
                    dragMode: 'move',
                    toggleDragModeOnDblclick: false,
                    data: this.value ? this.value.crop : null,
                    ready: () => {
                        if (this.value) {
                            this.cropper.setCropBoxData(this.value.cropBox).setCanvasData(this.value.canvas);
                        }
                        this.$nextTick(() => this.cropperReady = true);
                    },
                    crop: () => {
                        if (this.cropperReady) {
                            this.currentData = {
                                crop: this.cropper.getData(true),
                                cropBox: this.cropper.getCropBoxData(),
                                canvas: this.cropper.getCanvasData(),
                            }
                            this.$emit('input', this.currentData);
                        }
                    },
                });
            },
            rotate(angle) {
                this.cropper.rotate(angle);
            }
        },
        computed: {
            imageSrc() {
                return 'data:image/jpg;base64,' + this.imageBase64;
            },
        },
        watch: {
            value(newValue) {
                if (newValue !== this.currentData) {
                    this.recreateCropper();
                }
            }
        }
    };
</script>

<style lang="scss" scoped>
    img {
        display: block;
        max-width: 100%;
    }
</style>
