<template>
    <div class="flip-container" :class="{flipped: flipped}">
        <div class="flipper">
            <div class="front">
                <slot name="front"></slot>
            </div>
            <div class="back">
                <slot name="back"></slot>
            </div>
        </div>
    </div>
</template>

<script>
    // https://davidwalsh.name/css-flip
    export default {
        props: ['flipped']
    };
</script>

<style lang="scss">
    .flip-container {
        perspective: 1000px;
        &.flipped .flipper {
            transform: rotateY(180deg);
        }
        &, .front, .back {
            width: 100%;
            height: 240px;
        }
        .flipper {
            transition: 0.6s;
            transform-style: preserve-3d;
            position: relative;
        }
        .front, .back {
            backface-visibility: hidden;
            position: absolute;
            top: 0;
            left: 0;
        }
        .front {
            z-index: 2;
            transform: rotateY(0deg);
        }
        .back {
            transform: rotateY(180deg);
        }
    }
</style>
