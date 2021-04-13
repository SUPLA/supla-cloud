<template>
    <div class="flip-container"
        :class="{flipped: flipped}">
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
        &.flipped {
            .back {
                opacity: 1;
                z-index: 2;
            }
            .front {
                opacity: .3;
            }
        }
        &, .front, .back {
            width: 100%;
            // height of the flipper is set in square-link
            /*height: 240px;*/
        }
        .flipper {
            position: relative;
        }
        .front, .back {
            transition: 0.6s;
            position: absolute;
            top: 0;
            left: 0;
        }
        .front {
            z-index: 1;
            opacity: 1;
        }
        .back {
            opacity: 0;
        }
    }
</style>
