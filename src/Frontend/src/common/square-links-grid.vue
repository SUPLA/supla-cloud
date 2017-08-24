<template>
    <transition-group name="square-links-grid"
        tag="div"
        class="square-links-grid">
        <slot></slot>
    </transition-group>
</template>

<style lang="scss">
    .square-links-grid {
        > div {
            display: inline-block;
            float: left;
            padding: 7px;
            transition: all .3s ease-out;
            width: 100%;
            $minimumWidthOfOneSquare: 350px;
            @for $i from 2 through 7 {
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
</style>

<script>
    export default {};
</script>
