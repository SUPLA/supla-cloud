<template>
    <div class="square-link"
        @click="$emit('click')">
        <slot></slot>
    </div>
</template>

<script>
    export default {};
</script>

<style lang="scss">
    .square-link {
        position: relative;
        display: block;
        background: #00D151;
        min-height: 260px;
        color: white;
        padding: 5px 10px;
        margin: 4px 5px 0 4px;
        h3, h4 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        &.yellow {
            background: #ffe836;
            color: black;
        }
        &.grey {
            background: #eaeaea;
            color: black;
        }
        dl {
            margin-bottom: 0;
            dd, dt {
                display: inline;
            }
            dt:after {
                display: block;
                content: '';
            }
        }
        .separator {
            border-top: solid 1px rgba(255, 255, 255, 0.6);
            display: block;
            margin: 8px 0;
        }
        .square-link-label {
            position: absolute;
            right: 10px;
            bottom: 10px;
        }
        /*&:hover, &:active, &.selected {
            margin-top: 0;
            box-shadow: 0 4px 3px rgba(0, 0, 0, .6);
        }
        &:active {
            transform: scale(0.95);
        }*/
        &.selected {
            border: 2px solid black;
            &:after {
                content: '';
                position: absolute;
                width: 52px;
                height: 52px;
                background: url('/assets/img/checked.svg');
                top: -1px;
                right: -1px;
                border-top-right-radius: 3px;
                transition: all .5s ease-in-out;
                animation-duration: 0.5s;
                animation-fill-mode: both;
                animation-name: fadeIn;
            }
        }
    }
</style>
