<template>
    <form @submit.prevent="saveChanges()">
        <NotificationForm v-model="notificationFields" :display-validation-errors="true"
            :disable-title-message="disableTitleMessage"
            :disable-body-message="disableBodyMessage"
            allow-no-recipients
        />
        <div class="text-center my-3">
            <button class="btn btn-white" type="submit" :disabled="this.notificationFields.isValid === false || loading">
                <i class="pe-7s-diskette"></i>
                {{ $t('Save changes') }}
            </button>
        </div>
    </form>
</template>

<script>
    import NotificationForm from "@/notifications/notification-form.vue";
    import {successNotification} from "@/common/notifier";

    export default {
        components: {NotificationForm},
        props: {
            notification: Object,
        },
        data() {
            return {
                notificationFields: undefined,
                disableTitleMessage: undefined,
                disableBodyMessage: undefined,
                loading: false,
            };
        },
        beforeMount() {
            if (this.notification.title === null) {
                this.disableTitleMessage = this.$t('Notification title is set by the firmware.');
            }
            if (this.notification.body === null) {
                this.disableBodyMessage = this.$t('Notification body is set by the firmware.');
            }
            this.notificationFields = {
                title: this.notification.title,
                body: this.notification.body,
                accessIds: this.notification.accessIds.map(aid => aid.id),
            };
        },
        methods: {
            saveChanges() {
                this.loading = true;
                this.$http.put(`notifications/${this.notification.id}`, this.notificationFields)
                    .then(() => successNotification(this.$t('Success'), this.$t('Data saved')))
                    .finally(() => this.loading = false);
            }
        },
    }
</script>
