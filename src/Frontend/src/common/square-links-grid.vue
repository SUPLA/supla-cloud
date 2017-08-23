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
            padding: 7px;
            transition: all .3s ease-out;
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
