<template>
    <button class="btn btn-default"
        type="button"
        :disabled="executed || executing"
        @click="executeScene()">
        <span v-if="!executing">
            <i v-if="executed"
                class="pe-7s-check"></i>
            {{ executed ? $t('executed') : $t('Execute the scene') }}
        </span>
        <button-loading-dots v-else></button-loading-dots>
    </button>
</template>

<script>
    export default {
        props: ['scene', 'disabled'],
        data() {
            return {
                executing: false,
                executed: false,
            };
        },
        methods: {
            executeScene() {
                this.executing = true;
                this.$http.patch(`scenes/${this.scene.id}`)
                    .then(() => {
                        this.executed = true;
                        setTimeout(() => this.executed = false, 3000);
                    })
                    .finally(() => {
                        this.executing = false;
                    });
            },
        }
    };
</script>
