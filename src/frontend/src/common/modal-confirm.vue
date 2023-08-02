<template>
    <modal class="modal-confirm"
        :header="header">
        <slot></slot>
        <div slot="footer">
            <button-loading-dots v-if="loading"></button-loading-dots>
            <div v-else>
                <a @click="$emit('cancel')"
                    v-if="cancellable"
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
    export default {
        props: {
            header: String,
            loading: {
                type: Boolean,
                required: false,
            },
            cancellable: {
                type: Boolean,
                required: false,
                default: true,
            }
        },
    };
</script>
