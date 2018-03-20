<template>
    <div :class="'dots-route ' + (shown ? 'shown' : '')">
        <div :class="'dot dot-' + dot1Color"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>
</template>

<style lang="scss">
    @import "../../styles/variables";

    $noOfDots: 3;
    $color: $supla-black;
    $width: 3px;
    $dotRadius: 24px;

    .dots-route {
        clear: both;
        $wholeWidth: 100%  * ($noOfDots - 1) / $noOfDots;
        width: 0;
        height: $width - 1px;
        margin: $dotRadius/2 (100%-$wholeWidth)/2;
        background: $color;
        position: relative;
        transition: width .3s ease-in;
        .dot {
            border: $width solid $color;
            width: $dotRadius;
            height: $dotRadius;
            border-radius: $dotRadius;
            position: absolute;
            top: -$dotRadius/2 + ($width - 1px)/2;
            background: $supla-bg;
            transition: background-color .3s;
            &:nth-child(1) {
                left: -$dotRadius;
            }
            &:nth-child(2) {
                left: 50%;
                margin-left: -$dotRadius/2;
            }
            &:nth-child(3) {
                left: 100%;
            }
        }
        &.shown {
            width: $wholeWidth;
        }
    }

    .bg-green .dots-route {
        background: $supla-white;
        .dot {
            border: $width solid $supla-white;
            background: $supla-green;
        }
    }

    .dot-red {
        background: $supla-red !important;
    }
</style>

<script>
    export default {
        props: ['dot1Color'],
        data() {
            return {
                shown: false,
            };
        },
        mounted() {
            setTimeout(() => this.shown = true);
        }
    };
</script>
