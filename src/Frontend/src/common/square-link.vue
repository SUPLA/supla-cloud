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
    @import "../styles/variables";

    .square-link {
        position: relative;
        display: block;
        background: $supla-green;
        color: white;
        padding: 5px 10px;
        border-radius: 3px;
        border: 2px solid $supla-green;
        transition: all .3s;
        > a {
            display: block;
            width: 100%;
            height: 100%;
            color: inherit;
            &.valign-center {
                display: table;
                > * {
                    display: table-cell;
                    vertical-align: middle;
                }
            }
        }
        h3, h4 {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        &.yellow {
            background: $supla-yellow;
            color: $supla-black;
            border-color: $supla-yellow !important;
        }
        &.grey {
            background: $supla-grey-light;
            color: $supla-black;
            border-color: $supla-grey-light;
        }
        &:hover {
            border-color: $supla-black;
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
        .square-link-label {
            position: absolute;
            right: 10px;
            bottom: 10px;
            font-size: 12px;
            line-height: 12px;
            .label {
                border: 0;
            }
        }
        &:active {
            transform: scale(0.95);
        }
        &.selected {
            border: 2px solid $supla-black;
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

    @mixin square-link-height($height) {
        .square-links-height-#{$height} {
            .square-link {
                height: $height + px;
            }
            .flip-container {
                &, .front, .back {
                    height: $height + px;
                }
            }
        }
    }

    @include square-link-height(240);
</style>
