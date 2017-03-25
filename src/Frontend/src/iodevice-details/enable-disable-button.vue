<template>
    <span>
        <a :class="'btn btn-default ' + (device.enabled ? 'btn-enable' : 'btn-disable')"
            @click="toggleEnabled()"
            v-show="!loading && !showSchedulesConfirmation">
            <strong class="clearfix">{{ $t(device.enabled ? 'ENABLED' : 'DISABLED') }}</strong>
            {{ $t('CLICK TO ' + (device.enabled ? 'DISABLE' : 'ENABLE')) }}
        </a>
        <button-loading v-if="loading || showSchedulesConfirmation"></button-loading>
        <confirm-modal v-if="showSchedulesConfirmation"
            @confirm="toggleEnabled(true)"
            @cancel="showSchedulesConfirmation = false">
            <h4 slot="header">{{ $t('Existing schedules') }}</h4>
            {{ $t('Turning this device off will result in disabling all schedules that use it.') }}
            <ul>
                <li v-for="schedule in conflictingSchedules">
                    {{ $t('Schedule') }} ID{{ schedule.id }}
                    <span class="small">{{ schedule.caption }}</span>
                </li>
            </ul>
            {{ $t('Are you sure you want to disable this device?') }}
        </confirm-modal>
    </span>
</template>

<script>
    import ButtonLoading from "./button-loading.vue";
    import {mapState, mapActions} from "vuex";

    export default {
        components: {ButtonLoading},
        data() {
            return {
                loading: false,
                showSchedulesConfirmation: false,
                conflictingSchedules: []
            };
        },
        methods: {
            toggleEnabled(confirm = false) {
                this.loading = true;
                this.showSchedulesConfirmation = false;
                this.$store.dispatch('toggleEnabled', confirm)
                    .catch(({body, status}) => {
                        if (status == 409) {
                            this.conflictingSchedules = body;
                            this.showSchedulesConfirmation = true;
                        }
                    })
                    .finally(() => this.loading = false);
            }
        },
        computed: mapState(['device'])
    };
</script>
