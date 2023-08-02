<template>
    <loading-cover :loading="true"
        class="pt-5"></loading-cover>
</template>

<script>
    import {errorNotification, successNotification} from "../../common/notifier";

    export default {
        props: ['targetCloudId', 'token'],
        mounted() {
            this.$http.delete(`remove-target-cloud/${this.targetCloudId}/${this.token}`, {skipErrorHandler: true})
                .then(() => successNotification(this.$t('Success'), this.$t('Your private SUPLA Cloud instance has been unregistered.')))
                .catch(() => errorNotification(this.$t('Error'), this.$t('Token does not exist')))
                .finally(() => this.$router.push({name: 'login'}));
        }
    };
</script>
