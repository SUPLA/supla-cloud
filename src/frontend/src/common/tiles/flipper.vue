<template>
    <div class="flip-container">
        <div class="front"
            v-if="!flipped">
            <slot name="front"></slot>
        </div>
        <div class="back"
            v-else>
            <slot name="back"></slot>
        </div>
    </div>
</template>

<script>
    export default {
        props: ['flipped']
    };
</script>

<style lang="scss">
    .flip-container {
        &, .front, .back {
            width: 100%;
            height: 100%;
        }
    }
</style>
