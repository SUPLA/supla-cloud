<template>
    <span class="channel-icon">
        <img :src="'/assets/img/functions/' + functionId + '.svg' | withBaseUrl"
            :width="width"
            v-if="functionId">
    </span>
</template>

<script>
    export default {
        props: ['model', 'width'],
        computed: {
            functionId() {
                if (this.model) {
                    if (this.model.functionId) {
                        return this.model.functionId;
                    } else if (this.model.function) {
                        return this.model.function.id;
                    } else if (this.model.id) {
                        return this.model.id;
                    } else if (Number.isInteger(this.model)) {
                        return this.model;
                    }
                }
                return false;
            }
        }
    };
</script>

<style lang="scss">
    .channel-icon {
        img {
            max-width: 100%;
        }
    }
</style>
