<template>
    <div :class="'square-links-grid-container clearfix ' + (count <= 4 ? 'square-links-grid-container-narrow' : '')">
        <transition-group name="square-links-grid"
            tag="div"
            class="square-links-grid">
            <slot></slot>
        </transition-group>
    </div>
</template>

<style lang="scss">
    @use "sass:math";
    @import "../../styles/mixins";

    $gridGap: 14px;
    $minimumWidthOfOneSquare: 265px;
    $maximumSupportedScreenWidth: 1675px;
    $maximumSquaresInRow: floor(math.div($maximumSupportedScreenWidth, $minimumWidthOfOneSquare));

    .square-links-grid-container {
        transition: all .3s;
        padding: 0 math.div($gridGap, 2);
        margin: 0 auto;

        // Here comes one of the most mournful lines in the whole SUPLA codebase.
        // It prevents the square grid to occupy the whole available space.
        // It has to be there in order to be consistent with indolent and clumsy locations and access identifiers views.
        // But brace yourself. The day will inevitably come.
        // This line will be eventually wiped out and all unicorns will throw rainbows everywhere out of their joy.
        max-width: $maximumSupportedScreenWidth;
    }

    .square-links-grid {
        display: flex;
        flex-wrap: wrap;
        > div {
            display: inline-block;
            padding: math.div($gridGap, 2);
            transition: all .3s ease-out;
            width: 100%;
            @for $i from 2 through $maximumSquaresInRow {
                $breakpoint: $i * $minimumWidthOfOneSquare;
                @media only screen and (min-width: $breakpoint) {
                    width: math.div(100%, $i);
                }
            }
        }
        &-enter, &-leave-to {
            opacity: 0;
            transform: scale(0.01);
        }
        &-leave-active {
            position: absolute;
        }
    }

    @include on-lg-and-up {
        .square-links-grid-container-narrow {
            max-width: 1170px;
            .square-links-grid > div {
                width: 25%;
            }
        }
    }
</style>

<script>
    export default {
        props: ['count']
    };
</script>
