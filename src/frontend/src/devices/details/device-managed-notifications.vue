<template>
    <div class="container">
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
</template>

<script>
    import NotificationForm from "@/notifications/notification-form.vue";

    export default {
        components: {NotificationForm}
    }
</script>

<style scoped>

</style>
