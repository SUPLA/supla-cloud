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
    @import "../../styles/variables";

    .square-link {
        position: relative;
        display: block;
        background: $supla-green;
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        border: 2px solid $supla-green;
        transition: all .3s;
        height: 100%;
        cursor: pointer;
        > a, > .valign-center {
            display: block;
            width: 100%;
            height: 100%;
            color: inherit;
        }
        > .valign-center {
            display: table;
            table-layout: fixed;
            > * {
                display: table-cell;
                vertical-align: middle;
            }
            i {
                font-size: 6em;
                display: block;
                margin-bottom: 15px;
            }
        }
        h2, h3 {
            margin-top: 3px;
            word-break: break-word;
        }
        /*h3, h4 {*/
        /*    white-space: nowrap;*/
        /*    overflow: hidden;*/
        /*    text-overflow: ellipsis;*/
        /*}*/
        &.yellow {
            background: $supla-yellow;
            color: $supla-black;
            border-color: $supla-yellow;
        }
        &.grey {
            background: $supla-grey-light;
            color: $supla-black;
            border-color: $supla-grey-light;
        }
        &.black {
            background: $supla-black;
            color: $supla-white;
            border-color: $supla-black;
        }
        &.blue {
            background: $supla-blue;
            color: $supla-white;
            border-color: $supla-black;
        }
        &:hover {
            border-color: $supla-black;
            > a {
                color: inherit;
            }
        }
        dl {
            margin-bottom: 0;
            font-size: .95em;
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
        &.with-label {
            padding-bottom: 30px;
        }
        .square-link-label, .square-link-label-left {
            position: absolute;
            bottom: 10px;
            font-size: 12px;
            line-height: 12px;
            .label {
                border: 0;
                padding: 0.2em 0.6em 0.2em;
            }
        }
        .square-link-label {
            right: 10px;
        }
        .square-link-label-left {
            left: 10px;
        }
        &:active:not(.not-transform) {
            transform: scale(0.95);
        }
        .channel-icon {
            transition: margin-right .3s;
        }
        &.selected {
            border: 2px solid $supla-black;
            &:after {
                content: '';
                position: absolute;
                width: 50px;
                height: 51px;
                background: url('../../assets/checked-corner.svg') no-repeat;
                top: -1px;
                right: -1px;
                border-top-right-radius: 3px;
                transition: all .5s ease-in-out;
                animation-duration: 0.5s;
                animation-fill-mode: both;
                animation-name: fadeIn;
            }
            .channel-icon {
                margin-right: 20px;
            }
        }
        &.lift-up {
            transition: all .2s;
            top: 0;
            &:hover, &.selected {
                top: -3px;
                box-shadow: 0 4px 3px rgba(0, 0, 0, .6);
            }
        }
    }
</style>
