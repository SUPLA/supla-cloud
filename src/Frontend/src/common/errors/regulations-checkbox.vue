<template>
    <div>
        <div class="checkbox checkbox-yellow">
            <label>
                <input type="checkbox"
                    v-model="agreed"
                    @change="$emit('input', agreed)">
                <span class="checkmark"></span>
                <component :is="checkboxLabel"
                    @click="rulesShown = true"></component>
            </label>
        </div>
        <regulations-modal v-if="rulesShown"
            @confirm="rulesShown = false"></regulations-modal>
    </div>
</template>

<script>
    import RegulationsModal from "./regulations-modal";

    export default {
        props: ['value'],
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
