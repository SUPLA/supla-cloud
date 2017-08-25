<template>
    <span>
        <a :class="'btn btn-default ' + (device.enabled ? 'btn-enable' : 'btn-disable')"
            @click="toggleEnabled()"
            v-show="!loading && !showSchedulesDisablingConfirmation">
            <strong class="clearfix">{{ $t(device.enabled ? 'ENABLED' : 'DISABLED') }}</strong>
            {{ $t('CLICK TO ' + (device.enabled ? 'DISABLE' : 'ENABLE')) }}
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
    import ButtonLoading from "../../common/button-loading.vue";
    import {mapState} from "vuex";
    import DisablingSchedulesModal from "../../schedules/modals/disabling-schedules-modal.vue";
    import EnablingSchedulesModal from "../../schedules/modals/enabling-schedules-modal.vue";

    export default {
        components: {ButtonLoading, DisablingSchedulesModal, EnablingSchedulesModal},
        data() {
            return {
                loading: false,
                showSchedulesDisablingConfirmation: false,
                showSchedulesEnablingConfirmation: false,
                schedules: []
            };
        },
        methods: {
            toggleEnabled(confirm = false) {
                this.loading = true;
                this.showSchedulesEnablingConfirmation = false;
                this.showSchedulesDisablingConfirmation = false;
                this.$store.dispatch('toggleEnabled', confirm)
                    .then(({body}) => {
                        if (this.device.enabled && body.schedules && body.schedules.length) {
                            this.schedules = body.schedules;
                            this.showSchedulesEnablingConfirmation = true;
                        }
                    })
                    .catch(({body, status}) => {
                        if (status == 409) {
                            this.schedules = body;
                            this.showSchedulesDisablingConfirmation = true;
                        }
                    })
                    .finally(() => this.loading = false);
            }
        },
        computed: mapState(['device'])
    };
</script>
