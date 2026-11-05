<template>
  <TemporaryFeedback :message-i18n="message ? `calcfg_feedback_${message}` : ''" :timeout="5000" />
</template>

<script>
  import TemporaryFeedback from '@/common/gui/temporary-feedback.vue';

  // i18n: ['calcfg_feedback_QUEUED', 'calcfg_feedback_WAITING_FOR_RESULT', 'calcfg_feedback_WAITING_FOR_RESULT_PASSWORD']
  export default {
    compatConfig: {MODE: 3},
    components: {TemporaryFeedback},
    props: {message: {type: String, default: ''}},
  };
</script>
