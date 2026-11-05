<template>
  <div v-if="message" class="alert alert-info mt-2">
    {{ $t(`calcfg_feedback_${message}`) }}
  </div>
</template>

<script>
  // i18n: ['calcfg_feedback_QUEUED', 'calcfg_feedback_WAITING_FOR_RESULT', 'calcfg_feedback_WAITING_FOR_RESULT_PASSWORD']
  export default {
    compatConfig: {MODE: 3},
    props: {message: {type: String, default: ''}},
  };
</script>
