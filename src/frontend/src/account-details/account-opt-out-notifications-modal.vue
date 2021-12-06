<template>
    <modal-confirm @confirm="updateOptOutNotifications()"
        @cancel="$emit('cancel')"
        :loading="loading"
        :header="$t('E-mail notifications')">
        <form @submit.prevent="updateOptOutNotifications()"
            class="opt-out-notifications">
            <label :class="['checkbox2', {'current-opt-out-notification': currentOptOutNotification == notification.id}]"
                v-for="notification in possibleNotifications"
                :key="notification.id">
                <input type="checkbox"
                    v-model="selectedNotifications[notification.id]">
                <span>{{ $t(notification.label) }}</span>
            </label>
            <button class="hidden"></button>
        </form>
    </modal-confirm>
</template>

<script>
    import {successNotification} from "../common/notifier";

    export default {
        data() {
            return {
                loading: false,
                possibleNotifications: [
                    {
                        id: 'failed_auth_attempt',
                        label: 'Unsuccessful login attempt', // i18n
                    }
                ],
                selectedNotifications: {},
            };
        },
        mounted() {
            console.log(this.$route.query);
        },
        methods: {
            updateOptOutNotifications() {
                this.loading = true;
                this.$http.patch(`users/current`, {
                    action: 'change:optOutNotifications',
                    newPassword: this.newPassword,
                    oldPassword: this.oldPassword
                })
                    .then(() => {
                        successNotification(this.$t('Successful'), this.$t('Password has been changed'));
                        this.$emit('cancel');
                    })
                    .finally(() => this.loading = false);
            }
        },
        computed: {
            currentOptOutNotification() {
                return this.$route.query.optOutNotification;
            }
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .opt-out-notifications {
        .checkbox2 {
            padding: 1em;
        }
        .current-opt-out-notification {
            border: 2px dotted $supla-grey-dark;
        }
    }
</style>
