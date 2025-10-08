<template>
  <div>
    <div v-if="implicitAgreement" class="form-group">
      {{
        $t('I accept the Terms and Conditions and hereby agree for processing of my personal data for the purposes included in the said Terms and Conditions.')
      }}
      <a @click.prevent="rulesShown = true">{{ $t('Please read the Terms and Conditions.') }}</a>
    </div>
    <label v-else class="checkbox2">
      <input v-model="agreed" type="checkbox" @change="$emit('input', agreed)" />
      <span>
        {{
          $t(
            'I accept the Terms and Conditions and hereby agree for processing of my personal data for the purposes included in the said Terms and Conditions.'
          )
        }}
        <a @click.prevent="rulesShown = true">{{ $t('Please read the Terms and Conditions.') }}</a>
      </span>
    </label>
    <regulations-modal v-if="rulesShown" @confirm="rulesShown = false"></regulations-modal>
  </div>
</template>

<script>
  import RegulationsModal from './regulations-modal.vue';

  export default {
    components: {RegulationsModal},
    props: ['value', 'implicitAgreement'],
    data() {
      return {
        agreed: false,
        rulesShown: false,
      };
    },
  };
</script>
