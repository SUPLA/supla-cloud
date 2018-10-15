<template>
    <modal
        @confirm="$emit('confirm')"
        class="text-left modal-800 display-newlines">
        <div slot
            v-html="rules"></div>
    </modal>
</template>

<script>
    export default {
        data() {
            return {
                rules: '',
            };
        },
        mounted() {
            const rulesLang = this.$i18n.locale == 'pl' ? 'pl' : 'en';
            this.$http.get(`/rules/rules_${rulesLang}.html`).then(response => this.rules = response.body);
        }
    };
</script>
