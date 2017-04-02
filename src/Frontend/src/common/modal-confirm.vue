<template>
    <modal class="modal-confirm" :header="header">
        <slot></slot>
        <div slot="footer">
            <a @click="$emit('cancel')"
                class="cancel">
                <i class="pe-7s-close"></i>
            </a>
            <a class="confirm"
                @click="$emit('confirm')">
                <i class="pe-7s-check"></i>
            </a>
        </div>
    </modal>
</template>

<script>
    export default {
        props: ['header']
    };
</script>

<style lang="scss"
    rel="stylesheet/scss">
    .modal-confirm {
        .modal-footer {
            .cancel {
                color: #BBB;
            }
        }
    }
</style>
