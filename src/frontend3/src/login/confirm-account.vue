<template>
    <loading-cover :loading="true" class="pt-5"></loading-cover>
</template>

<script>
  import {errorNotification, successNotification} from "../common/notifier";
  import LoadingCover from "@/common/gui/loaders/loading-cover.vue";
  import {api} from "@/api/api.js";

  export default {
      components: {LoadingCover},
        props: ['token'],
        mounted() {
          api.patch('confirm/' + this.token, {}, {skipErrorHandler: [400]})
                .then(() => successNotification(this.$t('Success'), this.$t('Account has been activated. You can Sign In now.')))
                .catch(() => errorNotification(this.$t('Error'), this.$t('Token does not exist')))
                .finally(() => this.$router.push({name: 'login'}));
        }
    };
</script>
