<template>
    <loading-cover :loading="true" class="pt-5"></loading-cover>
</template>

<script>
    import {errorNotification, successNotification} from "../common/notifier";

    export default {
        props: {
            deviceId: String,
            unlockCode: String,
        },
        mounted() {
            this.$http.patch('confirm-device-unlock/' + this.deviceId, {code: this.unlockCode}, {skipErrorHandler: [400, 404]})
                .then(() => successNotification(this.$t('Success'), this.$t('The device has been unlocked.')))
                .catch(() => errorNotification(this.$t('Error'), this.$t('The device could not be unlocked.')))
                .finally(() => this.$router.push('/').catch(() => {
                }));
        }
    };
</script>
