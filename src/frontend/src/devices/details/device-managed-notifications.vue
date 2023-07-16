<template>
    <div class="container">
        <loading-cover :loading="notification === undefined">
            <div v-if="notification">
                <p class="mb-5">
                    {{ $t('Firmware of this device sends notifications. You can configure its recipients and content here. For more information consult the firmware issuer.') }}
                </p>
                <div class="row">
                    <div class="col-lg-offset-3 col-lg-6">

                        <NotificationForm/>
                        <div class="text-center my-3">
                            <button class="btn btn-white"
                                type="submit">
                                <i class="pe-7s-diskette"></i>
                                {{ $t('Save changes') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <empty-list-placeholder v-else-if="notification === false"/>
        </loading-cover>
    </div>
</template>

<script>
    import NotificationForm from "@/notifications/notification-form.vue";

    export default {
        components: {NotificationForm},
        props: {
            id: [String, Number],
        },
        data() {
            return {
                notification: undefined,
            };
        },
        mounted() {
            this.$http.get(`iodevices/${this.id}/notifications?onlyManaged=true`)
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
