<template>
    <modal-confirm @confirm="changePassword()"
        @cancel="$emit('cancel')"
        :loading="loading"
        :header="$t('Change Password')">
        <form @submit.prevent="changePassword()">
            <div class="form-group">
                <input type="password"
                    class="form-control"
                    v-model="oldPassword"
                    id="old-password">
                <label for="old-password">{{ $t('Current Password') }}</label>
            </div>
            <div class="form-group">
                <input type="password"
                    class="form-control"
                    v-model="newPassword"
                    id="new-password">
                <label for="new-password">{{ $t('New password') }}</label>
            </div>
            <div class="form-group">
                <input type="password"
                    class="form-control"
                    v-model="newPasswordConfirm"
                    id="confirm-password">
                <label for="confirm-password">{{ $t('Confirm Password') }}</label>
            </div>
            <button class="hidden"></button>
        </form>
    </modal-confirm>
</template>

<script>
    import {errorNotification, successNotification} from "../common/notifier";

    export default {
        data() {
            return {
                oldPassword: '',
                newPassword: '',
                newPasswordConfirm: '',
                loading: false,
            };
        },
        methods: {
            changePassword() {
                if (!this.oldPassword) {
                    return errorNotification('Error', 'Current password is incorrect', this);
                }
                if (this.newPassword != this.newPasswordConfirm) {
                    return errorNotification('Error', 'The password and its confirm are not the same.', this);
                }
                this.loading = true;
                this.$http.patch(`users/current`, {
                    action: 'change:password',
                    newPassword: this.newPassword,
                    oldPassword: this.oldPassword
                })
                    .then(() => {
                        successNotification('Successful', 'Password has been changed', this);
                        this.$emit('cancel');
                    })
                    .finally(() => this.loading = false);
            }
        }
    };
</script>
