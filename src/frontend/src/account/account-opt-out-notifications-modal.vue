<template>
    <modal-confirm @confirm="updateOptOutNotifications()"
        @cancel="$emit('cancel')"
        :loading="loading"
        :header="$t('Account notifications')">
        <p>
            {{ $t("We can notify you about certain events with your account. Opt out if you don't want us to bother you.") }}
        </p>
        <p v-if="currentOptOutNotification">
            {{ $t("The notification marked with an orange background is the one you saw when you clicked the unsubscribe link.") }}
        </p>
        <form @submit.prevent="updateOptOutNotifications()"
            class="opt-out-notifications">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th></th>
                    <th>{{ $t('E-mail') }}</th>
                    <th>{{ $t('Push') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="notification in possibleNotifications" :key="notification.id"
                    :class="{'current-opt-out-notification': currentOptOutNotification == notification.id}">
                    <td>{{ $t(notification.label) }}</td>
                    <td>
                        <label class="checkbox2">
                            <input type="checkbox" v-model="selectedNotificationsEmail[notification.id]">
                        </label>
                    </td>
                    <td>
                        <label class="checkbox2">
                            <input type="checkbox" v-model="selectedNotificationsPush[notification.id]">
                        </label>
                    </td>
                </tr>
                </tbody>
            </table>

            <div class="form-group">
                <label>{{ $t('Recipients for push notifications') }}</label>
                <AccessIdsDropdown v-model="accessIds"/>
                <div class="help-block help-error">{{ $t('The notification must have a recipient.') }}</div>
            </div>

            <button class="hidden" type="submit"></button>
        </form>
    </modal-confirm>
</template>

<script>
    import {successNotification} from "../common/notifier";
    import AccessIdsDropdown from "@/access-ids/access-ids-dropdown.vue";

    export default {
        components: {AccessIdsDropdown},
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
                selectedNotificationsEmail: {},
                selectedNotificationsPush: {},
                accessIds: [],
            };
        },
        mounted() {
            this.possibleNotifications.forEach(({id}) => {
                const issetEmail = this.user.preferences?.optOutNotifications?.includes(id);
                const issetPush = this.user.preferences?.optOutNotificationsPush?.includes(id);
                this.$set(this.selectedNotificationsEmail, id, !issetEmail);
                this.$set(this.selectedNotificationsPush, id, !issetPush);
            });
            this.user.preferences?.accountPushNotificationsAccessIdsIds?.forEach(id => this.accessIds.push({id}));
        },
        methods: {
            updateOptOutNotifications() {
                this.loading = true;
                const optOutNotifications = this.possibleNotifications.map(({id}) => id).filter((id) => !this.selectedNotificationsEmail[id]);
                const optOutNotificationsPush = this.possibleNotifications.map(({id}) => id).filter((id) => !this.selectedNotificationsPush[id]);
                const accountPushNotificationsAccessIdsIds = this.accessIds.map(({id}) => id);
                this.$http.patch(`users/current`, {
                    action: 'change:optOutNotifications',
                    optOutNotifications,
                    optOutNotificationsPush,
                    accountPushNotificationsAccessIdsIds,
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
            },
        }
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .opt-out-notifications {

        .current-opt-out-notification td {
            background: #f4c387;
        }
    }
</style>
