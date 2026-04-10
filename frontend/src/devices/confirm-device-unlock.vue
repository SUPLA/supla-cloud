<template>
  <loading-cover :loading="true" class="pt-5"></loading-cover>
</template>

<script>
  import {errorNotification, successNotification} from '../common/notifier';
  import {api} from '@/api/api.js';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';

  export default {
    components: {LoadingCover},
    props: {
      deviceId: String,
      unlockCode: String,
    },
    mounted() {
      api
        .patch('confirm-device-unlock/' + this.deviceId, {code: this.unlockCode}, {skipErrorHandler: [400, 404]})
        .then(() => successNotification(this.$t('The device has been unlocked.')))
        .catch(() => errorNotification(this.$t('The device could not be unlocked.')))
        .finally(() => this.$router.push('/').catch(() => {}));
    },
  };
</script>
