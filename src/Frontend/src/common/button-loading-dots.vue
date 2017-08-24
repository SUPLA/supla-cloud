<template>
    <div class="button-loading-dots">
        <div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</template>

<style lang="scss">
    @import "../styles/variables";

    .button-loading-dots {
        cursor: no-drop;
        > div {
            display: inline-block;
            width: 50px;
            > div {
                opacity: 0;
                float: left;
                height: 11px;
                margin-left: 4px;
                width: 10px;
                animation: 1.5s supla-loading-dots infinite;
                border-radius: 7px;
                &:nth-child(2) {
                    animation-delay: .1s;
                }
                &:nth-child(3) {
                    animation-delay: .2s;
                }
            }
        }
    }

    @mixin loading-dots-color($color) {
        .button-loading-dots > div > div {
            background-color: $color;
        }
    }

    @include loading-dots-color($supla-green);
    .btn-black, .btn-green, .btn-enable, .btn-orange {
        @include loading-dots-color($supla-white);
    }

    .btn-black.btn-outline {
        @include loading-dots-color($supla-black);
    }

    .btn-green.btn-outline:not(:hover) {
        @include loading-dots-color($supla-green);
    }

    .btn-orange.btn-outline:not(:hover) {
        @include loading-dots-color($supla-orange);
    }

    @keyframes supla-loading-dots {
        0% {
        }
        50% {
            opacity: 1;
        }
        100% {
        }
    }
</style>

<script>
    export default {};
</script>
