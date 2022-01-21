<template>
    <modal-confirm @confirm="updateOptOutNotifications()"
        @cancel="$emit('cancel')"
        :loading="loading"
        :header="$t('E-mail notifications')">
        <p>
            {{ $t("We can notify you about certain events with your account. Opt out if you don't want us to bother you.") }}
        </p>
        <p v-if="currentOptOutNotification">
            {{ $t("The notification marked with a border is the one you saw when you clicked the unsubscribe link.") }}
        </p>
        <form @submit.prevent="updateOptOutNotifications()"
            class="opt-out-notifications">
            <label :class="['checkbox2', {'current-opt-out-notification': currentOptOutNotification == notification.id}]"
                v-for="notification in possibleNotifications"
                :key="notification.id">
                <input type="checkbox"
                    v-model="selectedNotifications[notification.id]">
                <span>{{ $t(notification.label) }}</span>
            </label>
            <button class="hidden"
                type="submit"></button>
        </form>
    </modal-confirm>
</template>

<script>
    import {successNotification} from "../common/notifier";

    export default {
        props: ['user'],
        data() {
            return {
                loading: false,
                possibleNotifications: [
                    {
                        id: 'failed_auth_attempt',
                        label: 'Unsuccessful login attempt', // i18n
                    },
                    {
                        id: 'new_io_device',
                        label: 'New IO device added to your account', // i18n
                    },
                    {
                        id: 'new_client_app',
                        label: 'New client app (smartphone) added to your account', // i18n
                    },
                ],
                selectedNotifications: {},
            };
        },
        mounted() {
            this.possibleNotifications.forEach(({id}) => {
                const isset = this.user.preferences?.optOutNotifications?.includes(id);
                this.$set(this.selectedNotifications, id, !isset);
            });
        },
        methods: {
            updateOptOutNotifications() {
                this.loading = true;
                const optOutNotifications = this.possibleNotifications.map(({id}) => id).filter((id) => !this.selectedNotifications[id]);
                this.$http.patch(`users/current`, {
                    action: 'change:optOutNotifications',
                    optOutNotifications,
                })
                    .then(({body}) => {
                        this.user.preferences = body.preferences;
                        successNotification(this.$t('Successful'), this.$t('Your preferences has been updated.'));
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
            padding: .5em;
        }
        .current-opt-out-notification {
            border: 2px dotted $supla-grey-dark;
        }
    }
</style>
