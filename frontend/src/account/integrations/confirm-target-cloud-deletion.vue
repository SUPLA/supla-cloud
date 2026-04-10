<template>
  <loading-cover :loading="true" class="pt-5"></loading-cover>
</template>

<script>
  import {errorNotification, successNotification} from '@/common/notifier.js';
  import {api} from '@/api/api.js';
  import LoadingCover from '@/common/gui/loaders/loading-cover.vue';

  export default {
    components: {LoadingCover},
    props: ['targetCloudId', 'token'],
    mounted() {
      api
        .delete_(`remove-target-cloud/${this.targetCloudId}/${this.token}`, {skipErrorHandler: true})
        .then(() => successNotification(this.$t('Your private SUPLA Cloud instance has been unregistered.')))
        .catch(() => errorNotification(this.$t('Token does not exist')))
        .finally(() => this.$router.push({name: 'login'}));
    },
  };
</script>
