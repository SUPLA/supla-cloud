<template>
    <div @mouseenter="startPreview()"
        @mouseleave="stopPreview()">
        <img v-for="(state, stateIndex) in icon.function.possibleVisualStates"
            :src="`/api/channel-icons/${icon.id}/${stateIndex}?access_token=${$user.getFilesDownloadToken()}`"
            v-show="stateIndex == shownIndex">
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
                    if (this.shownIndex >= this.icon.function.possibleVisualStates.length) {
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
    img {
        max-width: 100%;
        max-height: 100px;
    }
</style>
