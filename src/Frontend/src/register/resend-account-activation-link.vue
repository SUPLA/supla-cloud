<template>
    <div>
        <button-loading-dots v-if="loading"></button-loading-dots>
        <component v-else-if="!success && !error"
            :is="resendHelpText"
            @click="resendActivationLink()"></component>
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
        computed: {
            resendHelpText() {
                const template = this.$t('Having problems with account activation? Make sure that the message did not land in the SPAM/Junk folder. You can also click [here] to resend the account activation link.')
                    .replace(/\[(.+?)\]/g, `<a @click.prevent="$emit('click')">$1</a>`);
                return {template: `<span>${template}</span>`};
            }
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
                        .catch(response => this.error = response.body.message)
                }
            }
        }
    };
</script>
