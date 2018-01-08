<template>
    <div :class="'loading-cover' + (loading ? ' is-loading' : '')">
        <div class="loading">
            <loader-dots></loader-dots>
        </div>
        <div class="loading-content clearfix">
            <slot></slot>
        </div>
    </div>
</template>

<script>
    import LoaderDots from "../common/loader-dots.vue";

    export default {
        props: ['loading'],
        components: {LoaderDots},
    };
</script>

<style lang="scss">
    .loading-cover {
        position: relative;
        &.is-loading {
            .loading-content {
                opacity: .4;
            }
            .loading {
                display: block;
            }
        }
        .loading {
            display: none;
            .loader-dots {
                position: absolute;
                left: 50%;
                margin: 0 0 0 -60px;
                top: 50%;
                z-index: 1000;
            }
            &:before, &:after {
                content: " ";
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                min-height: 50px;
                min-width: 50px;
                z-index: 1000;
            }
            &:before {
                opacity: 0;
            }
        }
    }
</style>
