<template>
    <div>
        <button-loading-dots v-if="loading"></button-loading-dots>
        <i18n-t
            keypath="Can’t find activation email? Please check your SPAM or Junk mail folders. Alternately please {clickHereLink} to resend."
            v-else-if="!success && !error">
            <template #clickHereLink>
                <a @click="resendActivationLink()">{{ $t('click here') }}</a>
            </template>
        </i18n-t>
        <span v-else-if="error">
            {{ $t(error) }}
        </span>
        <span v-else>
            {{ $t('The activation link has been sent again. Check the inbox.') }}
        </span>
    </div>
</template>

<script>
    import ButtonLoadingDots from "@/common/gui/loaders/button-loading-dots.vue";
    import {errorNotification, successNotification} from "../common/notifier";

    export default {
        props: ['username', 'notifications'],
        components: {ButtonLoadingDots},
        data() {
            return {
                loading: false,
                success: false,
                error: ''
            };
        },
        methods: {
            resendActivationLink() {
                this.loading = true;
                const promise = this.$http.patch('register-resend', {email: this.username}, {skipErrorHandler: [400, 409]})
                    .finally(() => this.loading = false);
                if (this.notifications) {
                    promise
                        .then(() => successNotification(this.$t('Successful'), this.$t('The activation link has been sent again. Check the inbox.')))
                        .catch((response) => errorNotification(this.$t('Error'), this.$t(response.body.message)));
                } else {
                    promise
                        .then(() => this.success = true)
                        .catch(response => this.error = response.body.message);
                }
            }
        }
    };
</script>
