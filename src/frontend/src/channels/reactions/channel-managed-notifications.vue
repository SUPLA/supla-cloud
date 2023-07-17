<template>
    <div class="container">
        <loading-cover :loading="notification === undefined">
            <div v-if="notification">
                <p class="mb-5">
                    {{ $t('Firmware of this device sends notifications in context of this channel. You can configure its recipients and content here. For more information consult the firmware issuer.') }}
                </p>
                <div class="row">
                    <div class="col-lg-offset-3 col-lg-6">
                        <CustomNotificationEditForm :notification="notification"/>
                    </div>
                </div>
            </div>
            <empty-list-placeholder v-else-if="notification === false"/>
        </loading-cover>
    </div>
</template>

<script>
    import CustomNotificationEditForm from "@/devices/details/custom-notification-edit-form.vue";

    export default {
        components: {CustomNotificationEditForm},
        props: {
            subject: Object,
        },
        data() {
            return {
                notification: undefined,
            };
        },
        mounted() {
            this.$http.get(`channels/${this.subject.id}/notifications?onlyManaged=true&include=accessIds`)
                .then(response => {
                    if (response.body.length > 0) {
                        this.notification = response.body[0];
                    }
                })
                .finally(() => {
                    if (!this.notification) {
                        this.notification = false;
                    }
                })
        },
    }
</script>

<style scoped>

</style>
