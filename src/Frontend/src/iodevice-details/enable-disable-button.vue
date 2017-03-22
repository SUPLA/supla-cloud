<template>
    <span>
        <a :class="'btn btn-default ' + (device.enabled ? 'btn-enable' : 'btn-disable')"
            @click="toggleEnabled()"
            v-show="!loading && !showSchedulesConfirmation">
            <strong class="clearfix">{{ $t(device.enabled ? 'ENABLED' : 'DISABLED') }}</strong>
            {{ $t('CLICK TO ' + (device.enabled ? 'DISABLE' : 'ENABLE')) }}
        </a>
        <button-loading v-if="loading || showSchedulesConfirmation"></button-loading>
        <modal title="Istniejące harmonogramy"
            :show.sync="showSchedulesConfirmation">
            Wyłączenie tego urządzenia spowoduje także wyłączenie harmongoramów, które są z nim powiązane.
            <div slot="footer">
                <a @click="showSchedulesConfirmation = false"
                    class="cancel">
                    <i class="pe-7s-close"></i>
                </a>
                <a class="confirm">
                    <i class="pe-7s-check"></i>
                </a>
            </div>
        </modal>
    </span>
</template>

<script>
    import ButtonLoading from "./button-loading.vue";
    import {mapState, mapActions} from "vuex";
    import Modal from "vue-bootstrap-modal";

    export default {
        components: {ButtonLoading, Modal},
        data() {
            return {
                loading: false,
                showSchedulesConfirmation: false
            };
        },
        methods: {
            toggleEnabled() {
                this.loading = true;
                this.$store.dispatch('toggleEnabled')
                    .catch(() => this.showSchedulesConfirmation = true)
                    .finally(() => this.loading = false);
            }
        },
        computed: mapState(['device'])
    };
</script>
