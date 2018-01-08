<template>
    <div class="dots-route">
        <div class="dot"></div>
        <div class="dot"></div>
        <div class="dot"></div>
    </div>
</template>

<style lang="scss">
    @import "../styles/variables";

    $noOfDots: 3;
    $color: $supla-black;
    $width: 3px;
    $dotRadius: 24px;

    .dots-route {
        clear: both;
        width: 100%  * ($noOfDots - 1) / $noOfDots;
        height: $width - 1px;
        margin: $dotRadius/2 auto;
        background: $color;
        position: relative;
        .dot {
            border: $width solid $color;
            width: $dotRadius;
            height: $dotRadius;
            border-radius: $dotRadius;
            position: absolute;
            top: -$dotRadius/2 + ($width - 1px)/2;
            background: #fafbfc;
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
    }
</style>

<script>

    export default {};
</script>
