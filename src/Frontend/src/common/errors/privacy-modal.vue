<template>
    <modal
        class="text-left modal-800 display-newlines">
        <div slot
            v-html="rules"></div>
        <div slot="footer">
        </div>
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
            let rulesLang = 'en';
            if (['pl', 'it', 'es', 'fr', 'ru', 'pt', 'de'].includes(this.$i18n.locale)) {
                rulesLang = this.$i18n.locale;
            }

            this.$http.get(`/privacy/privacy_${rulesLang}.html`).then(response => this.rules = response.body);
        }
    };
</script>
