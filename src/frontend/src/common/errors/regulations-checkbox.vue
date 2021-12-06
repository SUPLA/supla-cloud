<template>
    <div>
        <div class="form-group"
            v-if="implicitAgreement">
            <component :is="checkboxLabel"
                @click="rulesShown = true"></component>
        </div>
        <label class="checkbox2"
            v-else>
            <input type="checkbox"
                v-model="agreed"
                @change="$emit('input', agreed)">
            <component :is="checkboxLabel"
                @click="rulesShown = true"></component>
        </label>
        <regulations-modal v-if="rulesShown"
            @confirm="rulesShown = false"></regulations-modal>
    </div>
</template>

<script>
    import RegulationsModal from "./regulations-modal";

    export default {
        props: ['value', 'implicitAgreement'],
        components: {RegulationsModal},
        data() {
            return {
                agreed: false,
                rulesShown: false
            };
        },
        computed: {
            checkboxLabel() {
                const template = this.$t('I accept the [Terms and Conditions] and hereby agree for processing of my personal data for the purposes included in the said [Terms and Conditions].')
                    .replace(/\[(.+?)\]/g, `<a @click.prevent="$emit('click')">$1</a>`);
                return {template: `<span>${template}</span>`};
            }
        }
    };
</script>
