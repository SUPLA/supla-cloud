<template>
    <loading-cover loading="true"></loading-cover>
</template>

<script>
    import {errorNotification, successNotification} from "../common/notifier";

    export default {
        props: ['token'],
        mounted() {
            this.$http.patch('confirm/' + this.token, {}, {skipErrorHandler: [400]})
                .then(() => successNotification('Success', 'Account has been activated. You can Sign In now.', this))
                .catch(() => errorNotification('Error', 'Token does not exist', this))
                .finally(() => this.$router.push({name: 'login'}));
        }
    };
</script>
