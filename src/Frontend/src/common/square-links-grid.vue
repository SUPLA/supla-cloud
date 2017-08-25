<template>
    <div :class="'square-links-grid-container ' + (count <= 4 ? 'square-links-grid-container-narrow' : '')">
        <transition-group name="square-links-grid"
            tag="div"
            class="square-links-grid">
            <slot></slot>
        </transition-group>
    </div>
</template>

<style lang="scss">
    @import "../styles/mixins";

    $gridGap: 14px;
    $minimumWidthOfOneSquare: 265px;

    .square-links-grid-container {
        transition: all .3s;
        padding: 0 $gridGap/2;
    }

    .square-links-grid {
        > div {
            display: inline-block;
            float: left;
            padding: $gridGap / 2;
            transition: all .3s ease-out;
            width: 100%;
            @for $i from 2 through 10 {
                $breakpoint: $i * $minimumWidthOfOneSquare;
                @media only screen and (min-width: $breakpoint) {
                    width: 100% / $i;
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
            margin: 0 auto;
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
