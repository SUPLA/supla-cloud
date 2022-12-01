<!-- Modal component from example in the docs https://vuejs.org/v2/examples/modal.html -->

<template>
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">

                    <div class="modal-header">
                        <h4>
                            <div class="pull-right">
                                <a v-if="displayCloseButton"
                                    @click="$emit('cancel')">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </a>
                            </div>
                            {{ header }}
                        </h4>
                    </div>

                    <div class="modal-body">
                        <slot></slot>
                    </div>

                    <div class="modal-footer">
                        <slot name="footer">
                            <a @click="$emit('cancel')"
                                class="cancel"
                                v-if="cancellable">
                                <i class="pe-7s-close"></i>
                            </a>
                            <a @click="$emit('confirm')"
                                class="confirm">
                                <i class="pe-7s-check"></i>
                            </a>
                        </slot>
                    </div>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
    export default {
        props: {
            header: {type: String},
            cancellable: {type: Boolean, default: false},
            displayCloseButton: {type: Boolean, default: false},
        },
    };
</script>

<style lang="scss">
    @import "../styles/variables";
    @import "../styles/mixins";

    .modal-mask {
        position: fixed;
        z-index: 9998;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        transition: opacity .3s ease;
    }

    .modal-wrapper {
        position: relative;
        top: 50%;
        transform: translateY(-50%);
        .modal-body {
            max-height: 60vh;
            overflow-x: hidden;
            overflow-y: auto;
        }
    }

    .modal-container {
        width: 90%;
        max-width: 600px;
        margin: 0px auto;
        padding: 20px 30px;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        transition: all .3s ease;
        @include on-xs-and-down {
            padding: 5px;
        }
    }

    .modal-800 {
        .modal-container {
            max-width: 800px;
        }
    }

    .modal-450 {
        .modal-container {
            max-width: 450px;
        }
    }

    .modal-default-button {
        float: right;
    }

    .modal-enter {
        opacity: 0;
    }

    .modal-leave-active {
        opacity: 0;
    }

    .modal-enter .modal-container,
    .modal-leave-active .modal-container {
        -webkit-transform: scale(1.1);
        transform: scale(1.1);
    }

    .modal-mask {
        color: black !important;
        h4 {
            font-size: 2em;
            color: $supla-green;
        }
        .modal-header, .modal-footer {
            border: 0;
        }
        .modal-footer {
            a i {
                vertical-align: middle;
                font-size: 4em;
            }
        }
    }

    @mixin modal-variant($type, $color) {
        .modal-#{$type} {
            h4 {
                color: $color;
            }
            .modal-footer a:not(.btn) {
                color: $color;
            }
        }
    }

    @include modal-variant(warning, $supla-orange);

    .modal-footer {
        a.cancel {
            color: $supla-grey-dark !important;
        }
    }

    .green-confirm-button .modal-footer .confirm {
        color: $supla-green !important;
    }
</style>
