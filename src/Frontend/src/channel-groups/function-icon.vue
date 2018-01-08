<template>
    <span class="channel-icon">
        <img :src="'/assets/img/functions/' + functionId + '.svg' | withBaseUrl"
            :width="width"
            v-if="functionId">
    </span>
</template>

<script>
    export default {
        props: ['channel', 'function', 'width'],
        computed: {
            functionId() {
                return this.function ? (this.function.id || this.function) : this.channel.function.id;
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
