<template>
    <loading-cover :loading="true" class="pt-5"></loading-cover>
</template>

<script>
    import {errorNotification, successNotification} from "../common/notifier";

    export default {
        props: ['token'],
        mounted() {
            this.$http.patch('confirm/' + this.token, {}, {skipErrorHandler: [400]})
                .then(() => successNotification(this.$t('Success'), this.$t('Account has been activated. You can Sign In now.')))
                .catch(() => errorNotification(this.$t('Error'), this.$t('Token does not exist')))
                .finally(() => this.$router.push({name: 'login'}));
        }
    };
</script>
