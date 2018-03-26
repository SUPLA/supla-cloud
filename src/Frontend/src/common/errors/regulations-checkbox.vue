<template>
    <div>
        <div class="checkbox">
            <label>
                <input type="checkbox"
                    v-model="agreed"
                    @change="$emit('input', agreed)">
                <component :is="checkboxLabel"
                    @click="rulesShown = true"></component>
            </label>
        </div>
        <modal
            v-if="rulesShown"
            @confirm="rulesShown = false"
            class="text-left modal-800 display-newlines">
            <div slot
                v-html="rules"></div>
        </modal>
    </div>
</template>

<script>
    import Vue from "vue";

    export default {
        props: ['value'],
        data() {
            return {
                agreed: false,
                rules: '',
                rulesShown: false
            };
        },
        mounted() {
            const rulesLang = Vue.config.external.locale == 'pl' ? 'pl' : 'en';
            this.$http.get(`/rules/rules_${rulesLang}.html`).then(response => this.rules = response.body);
        },
        computed: {
            checkboxLabel() {
                const template = this.$t('I accept the [Regulations] and agree to the processing of my personal data for the purpose and scope indicated in the [Regulations].')
                    .replace(/\[(.+?)\]/g, `<a @click.prevent="$emit('click')">$1</a>`);
                return {template: `<span>${template}</span>`};
            }
        }
    };
</script>
