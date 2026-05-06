<template>
  <div>
    <input v-model="crontab" type="text" class="form-control text-center crontab-input" placeholder="23 0-20/2 * * *" @input="updateValue()" />
    <div class="help-block text-right">
      {{ humanizedCrontab }}
    </div>
  </div>
</template>

<script>
  export default {
    compatConfig: {
      MODE: 3,
    },
  };
</script>

<script setup>
  import cronstrue from 'cronstrue/i18n';
  import {computed, ref} from 'vue';
  import {useI18n} from 'vue-i18n';

  const model = defineModel();
  const i18n = useI18n();

  const crontab = ref(model.value || '');

  const humanizedCrontab = computed(() => {
    try {
      let currentCrontab = crontab.value;
      let sunsetMatch = currentCrontab.match(/^S[SR]-?[0-9]+/);
      if (sunsetMatch) {
        currentCrontab = currentCrontab.replace(/^S[SR]-?[0-9]+/, '*/25');
      }
      let description = cronstrue.toString(currentCrontab, {locale: i18n.locale.value});
      if (description.indexOf('undefined') !== -1 || description.indexOf('null') !== -1) {
        return '';
      }
      if (sunsetMatch) {
        const constDescription = cronstrue.toString('*/25 * * * *', {locale: i18n.locale.value});
        description = description.replace(constDescription, sunDescription(sunsetMatch[0]));
      }
      return description;
    } catch (error) {
      console.warn(error);
      return '';
    }
  });

  function sunDescription(minutePart) {
    const sunset = minutePart.charAt(1) === 'S';
    const delay = parseInt(minutePart.substr(2));
    if (delay === 0) {
      if (sunset) {
        return i18n.t('At sunset');
      } else {
        return i18n.t('At sunrise');
      }
    } else if (delay > 0) {
      if (sunset) {
        return i18n.t('{minutes} minutes after sunset', {minutes: delay});
      } else {
        return i18n.t('{minutes} minutes after sunrise', {minutes: delay});
      }
    } else {
      if (sunset) {
        return i18n.t('{minutes} minutes before sunset', {minutes: -delay});
      } else {
        return i18n.t('{minutes} minutes before sunrise', {minutes: -delay});
      }
    }
  }

  function updateValue() {
    model.value = humanizedCrontab.value ? crontab.value : '';
  }
</script>

<style lang="scss">
  .crontab-input {
    font-size: 1.3em;
    font-weight: bold;
  }
</style>
