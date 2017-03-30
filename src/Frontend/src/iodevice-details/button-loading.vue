<template>
    <div class="btn btn-load">
        <div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </div>
</template>

<style lang="scss"
    rel="stylesheet/scss">
    .btn-load {
        border-color: #fff;
        height: 44px;
        padding: 12px 20px 0;
        cursor: no-drop;
        > div {
            display: inline-block;
            width: 50px;
            > div {
                background-color: transparent;
                float: left;
                height: 11px;
                margin-left: 4px;
                width: 10px;
                animation: 1.5s bounce_circleG infinite;
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

    @keyframes bounce_circleG {
        0% {
        }
        50% {
            background-color: #00D151;
        }
        100% {
        }
    }
</style>

<script>
    export default {};
</script>
