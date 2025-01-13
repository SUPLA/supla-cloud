<template>
    <modal
        @confirm="$emit('confirm')"
        class="text-left modal-800 display-newlines">
        <loading-cover :loading="!rules">
            <div v-if="rules"
                v-html="rules"></div>
        </loading-cover>
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
            this.fetch();
        },
        methods: {
            fetch() {
                this.rules = '';
                const rulesLang = this.$i18n.locale == 'pl' ? 'pl' : 'en';
                this.$http.get(`/rules/rules_${rulesLang}.html`).then(response => this.rules = response.body);
            }
        },
        watch: {
            '$i18n.locale'() {
                this.fetch();
            }
        }
    };
</script>
