<template>
    <span class="channel-icon">
        <img :src="'/assets/img/functions/' + functionId + alternativeSuffix + '.svg' | withBaseUrl"
            :width="width"
            v-if="functionId !== undefined">
    </span>
</template>

<script>
    export default {
        props: ['model', 'width', 'alternative'],
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
                return 0;
            },
            alternativeSuffix() {
                if (this.alternative !== undefined) {
                    return this.alternative ? `_${this.alternative}` : '';
                }
                if (this.model) {
                    if (this.model.altIcon > 0) {
                        return `_${this.model.altIcon}`;
                    }
                }
                return '';
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
