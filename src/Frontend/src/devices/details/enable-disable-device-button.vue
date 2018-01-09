<template>
    <span>
        <a :class="'btn btn-default ' + (isEnabled ? 'btn-enable' : 'btn-disable')"
            @click="toggleEnabled()"
            v-show="!loading && !showSchedulesDisablingConfirmation">
            <strong class="clearfix">{{ $t(isEnabled ? 'ENABLED' : 'DISABLED') }}</strong>
            {{ $t('CLICK TO ' + (isEnabled ? 'DISABLE' : 'ENABLE')) }}
        </a>
        <button-loading v-if="loading || showSchedulesDisablingConfirmation"></button-loading>
        <disabling-schedules-modal message="Turning this device off will result in disabling all the associated schedules."
            v-if="showSchedulesDisablingConfirmation"
            :schedules="schedules"
            @confirm="toggleEnabled(true)"
            @cancel="showSchedulesDisablingConfirmation = false"></disabling-schedules-modal>
        <enabling-schedules-modal v-if="showSchedulesEnablingConfirmation"
            :schedules="schedules"
            @confirm="showSchedulesEnablingConfirmation = false"
            @cancel="showSchedulesEnablingConfirmation = false"></enabling-schedules-modal>
    </span>
</template>

<script>
    import ButtonLoading from "../../common/gui/loaders/button-loading.vue";
    import DisablingSchedulesModal from "../../schedules/modals/disabling-schedules-modal.vue";
    import EnablingSchedulesModal from "../../schedules/modals/enabling-schedules-modal.vue";

    export default {
        props: ['deviceId', 'enabled'],
        components: {ButtonLoading, DisablingSchedulesModal, EnablingSchedulesModal},
        data() {
            return {
                loading: false,
                showSchedulesDisablingConfirmation: false,
                showSchedulesEnablingConfirmation: false,
                isEnabled: this.enabled,
                schedules: []
            };
        },
        methods: {
            toggleEnabled(confirm = false) {
                this.loading = true;
                this.showSchedulesEnablingConfirmation = false;
                this.showSchedulesDisablingConfirmation = false;
                this.$http.put(`iodevices/${this.deviceId}`, {enabled: !this.isEnabled, confirm}, {skipErrorHandler: true})
                    .then(({body}) => {
                        this.isEnabled = !this.isEnabled;
                        if (this.isEnabled && body.schedules && body.schedules.length) {
                            this.schedules = body.schedules;
                            this.showSchedulesEnablingConfirmation = true;
                        }
                    })
                    .catch(({body, status}) => {
                        if (status == 409) {
                            this.schedules = body.schedules.filter(schedule => schedule.enabled);
                            this.showSchedulesDisablingConfirmation = true;
                        }
                    })
                    .finally(() => this.loading = false);
            }
        }
    };
</script>
