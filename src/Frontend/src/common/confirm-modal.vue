<!-- Modal component from example in the docs https://vuejs.org/v2/examples/modal.html -->

<template>
    <transition name="modal">
        <div class="modal-mask">
            <div class="modal-wrapper">
                <div class="modal-container">

                    <div class="modal-header">
                        <slot name="header">
                            {{ $t('Are you sure?') }}
                        </slot>
                    </div>

                    <div class="modal-body">
                        <slot></slot>
                    </div>

                    <div class="modal-footer">
                        <slot name="footer">
                            <a @click="$emit('cancel')"
                                class="cancel">
                                <i class="pe-7s-close"></i>
                            </a>
                            <a class="confirm"
                                @click="$emit('confirm')">
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
    export default {};
</script>

<style lang="scss"
    rel="stylesheet/scss">
    .modal-mask {
        position: fixed;
        z-index: 9998;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, .5);
        display: table;
        transition: opacity .3s ease;
    }

    .modal-wrapper {
        display: table-cell;
        vertical-align: middle;
    }

    .modal-container {
        width: 600px;
        margin: 0px auto;
        padding: 20px 30px;
        background-color: #fff;
        border-radius: 2px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, .33);
        transition: all .3s ease;
        font-family: Helvetica, Arial, sans-serif;
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
            color: #00D151;
        }
        .modal-header, .modal-footer {
            border: 0;
        }
        .modal-footer {
            a i {
                vertical-align: middle;
                font-size: 4em;
            }
            .cancel {
                color: #BBB;
            }
        }
    }
</style>
