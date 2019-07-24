<template>
    <loading-cover loading="true"></loading-cover>
</template>

<script>
    import {errorNotification, successNotification} from "../common/notifier";

    export default {
        props: ['token'],
        mounted() {
            this.$http.patch('confirm-deletion/' + this.token, {}, {skipErrorHandler: [400]})
                .then(() => successNotification(this.$t('Successful'), this.$t('Your account has been deleted. We hope you will come back to us soon.')))
                .catch(() => errorNotification(this.$t('Error'), this.$t('Token does not exist')))
                .finally(() => this.$router.push({name: 'login'}));
        }
    };
</script>
