<template>
    <modal class="modal-confirm"
        :header="header">
        <slot></slot>
        <div slot="footer">
            <button-loading-dots v-if="loading"></button-loading-dots>
            <div v-else>
                <a @click="$emit('cancel')"
                    class="cancel">
                    <i class="pe-7s-close"></i>
                </a>
                <a class="confirm"
                    @click="$emit('confirm')">
                    <i class="pe-7s-check"></i>
                </a>
            </div>
        </div>
    </modal>
</template>

<script>
    import ButtonLoadingDots from "./button-loading-dots.vue";

    export default {
        props: ['header', 'loading'],
        components: {ButtonLoadingDots},
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .modal-confirm {
        .modal-footer {
            .cancel {
                color: $supla-grey-light;
            }
        }
    }
</style>
