<template>
    <span class="channel-icon">
        <img :src="'/assets/img/functions/' + channel.function.id + '.svg' | withBaseUrl"
            :width="width">
    </span>
</template>

<script>
    export default {
        props: ['channel', 'width']
    };
</script>

<style lang="scss">
    .channel-icon {
        img {
            max-width: 100%;
        }
    }
</style>
