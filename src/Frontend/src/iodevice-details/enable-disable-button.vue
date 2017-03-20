<template>
    <span>
        <a :class="'btn btn-default ' + (device.enabled ? 'btn-enable' : 'btn-disable')"
            @click="toggleEnabled()"
            v-show="!loading">
            <strong class="clearfix">{{ $t(device.enabled ? 'ENABLED' : 'DISABLED') }}</strong>
            {{ $t('CLICK TO ' + (device.enabled ? 'DISABLE' : 'ENABLE')) }}
        </a>
        <button-loading v-if="loading"></button-loading>
        <modal title="Modal Title" :show.sync="!showSchedulesConfirmation">
            Modal Text
        </modal>
        <div class="overlay-delete overlay-data overlay-open"
            v-if="showSchedulesConfirmation">
            <div class="dialog">
                <h1>Harmonogramy</h1>
                <p>Są i się wyłączą</p>
                <div class="controls">
                    <a @click="showSchedulesConfirmation = false"
                        class="overlay-delete-close cancel">{{ $t('CLOSE') }}</a>
                    <a @click="disableConfirm()"
                        class="save">
                        <i class="pe-7s-check"></i>
                    </a>
                </div>
            </div>
        </div>
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
