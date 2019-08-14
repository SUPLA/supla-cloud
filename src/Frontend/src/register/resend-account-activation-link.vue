<template>
    <div>
        <button-loading-dots v-if="loading"></button-loading-dots>
        <component v-else-if="!success"
            :is="resendHelpText"
            @click="resendActivationLink()"></component>
        <span v-else>
            {{ $t('The activation link has been sent again. Check the inbox.') }}
        </span>
    </div>
</template>

<script>
    import ButtonLoadingDots from "@/common/gui/loaders/button-loading-dots.vue";

    export default {
        props: ['username'],
        components: {ButtonLoadingDots},
        data() {
            return {
                loading: false,
                success: false,
            };
        },
        computed: {
            resendHelpText() {
                const template = this.$t('Having problems with account activation? Click [here] to resend the account activation link.')
                    .replace(/\[(.+?)\]/g, `<a @click.prevent="$emit('click')">$1</a>`);
                return {template: `<span>${template}</span>`};
            }
        },
        methods: {
            resendActivationLink() {
                this.loading = true;
                this.$http.patch('register-resend', {email: this.username})
                    .then(() => this.success = true)
                    .finally(() => this.loading = false);
            }
        }
    };
</script>
