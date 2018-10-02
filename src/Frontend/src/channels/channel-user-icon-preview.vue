<template>
    <div @mouseenter="startPreview()"
        @mouseleave="stopPreview()">
        <img v-for="(image, index) in icon.images"
            :src="'data:image/png;base64,' + image"
            v-show="index == shownIndex">
    </div>
</template>

<script>
    export default {
        props: ['icon'],
        data() {
            return {
                shownIndex: 0,
                previewTimeout: undefined
            };
        },
        methods: {
            startPreview() {
                this.previewTimeout = setTimeout(() => {
                    this.shownIndex++;
                    if (this.shownIndex >= this.icon.images.length) {
                        this.shownIndex = 0;
                    }
                    this.startPreview();
                }, 1000);
            },
            stopPreview() {
                clearTimeout(this.previewTimeout);
                this.shownIndex = 0;
            }
        }
    };
</script>

<style scoped
    lang="scss">
    img {width: 100%;}
</style>
