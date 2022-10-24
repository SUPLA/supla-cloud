<template>
    <div>
        <div class="form-group"
            v-if="implicitAgreement">
            {{ $t('I accept the Terms and Conditions and hereby agree for processing of my personal data for the purposes included in the said Terms and Conditions.') }}
            <a @click.prevent="rulesShown = true">{{ $t('Please read the Terms and Conditions.') }}</a>
        </div>
        <label class="checkbox2"
            v-else>
            <input type="checkbox"
                v-model="agreed"
                @change="$emit('input', agreed)">
            <span>
                {{ $t('I accept the Terms and Conditions and hereby agree for processing of my personal data for the purposes included in the said Terms and Conditions.') }}
                <a @click.prevent="rulesShown = true">{{ $t('Please read the Terms and Conditions.') }}</a>
            </span>
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
    };
</script>
