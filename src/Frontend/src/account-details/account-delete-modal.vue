<template>
    <modal class="modal-warning"
        @cancel="$emit('cancel')"
        :header="$t('We will miss you')">
        <p class="text-center">{{ $t('Deleting your account will result also in deletion of all your data, including your connected devices, configure channels, direct links and measurement history. Deleting an account is irreversible.') }}</p>
        <p class="text-center">{{ $t('In order to confirm account deletion, enter your password.') }}</p>
        <input type="password"
            class="form-control"
            v-model="password"
            id="password">
        <label for="password">{{ $t('Password') }}</label>
        <div slot="footer">
            <button-loading-dots v-if="loading"></button-loading-dots>
            <div v-else>
                <a @click="$emit('cancel')"
                    class="btn btn-grey">
                    {{ $t('Cancel') }}
                </a>
                <a class="btn btn-red-outline"
                    @click="deleteAccount()">
                    {{ $t('I confirm! Delete my account.') }}
                </a>
            </div>
        </div>
    </modal>
</template>

<script>
    import {errorNotification, successNotification} from "../common/notifier";
    import ButtonLoadingDots from "../common/gui/loaders/button-loading-dots.vue";

    export default {
        components: {ButtonLoadingDots},
        data() {
            return {
                password: '',
                loading: false,
            };
        },
        methods: {
            deleteAccount() {
                if (!this.password) {
                    return errorNotification(this.$t('Error'), this.$t('Incorrect password'));
                }
                this.loading = true;
                this.$http.patch(`users/current`, {action: 'delete', password: this.password})
                    .then(() => {
                        successNotification(this.$t('Successful'), this.$t('Your account has been deleted. We hope you will come back to us soon.'));
                        this.$emit('cancel');
                        $("#logoutButton")[0].click();
                    })
                    .finally(() => this.loading = false);
            }
        }
    };
</script>
