<template>
    <modal
        @confirm="$emit('confirm')"
        class="text-left modal-800 display-newlines">
        <div slot
            v-html="rules"></div>
    </modal>
</template>

<script>
    import Vue from "vue";

    export default {
        data() {
            return {
                rules: '',
            };
        },
        mounted() {
            const rulesLang = Vue.config.external.locale == 'pl' ? 'pl' : 'en';
            this.$http.get(`/rules/rules_${rulesLang}.html`).then(response => this.rules = response.body);
        }
    };
</script>
