<template>
    <div class="container">
        <pending-changes-page
            :header="$t('Device settings')"
            dont-set-page-title
            @cancel="cancelChanges()"
            @save="saveDeviceSettings()"
            :is-pending="hasPendingChanges">
            <div class="row">
                <div class="col-md-6 col-md-offset-3">
                    <transition-expand>
                        <ConfigConflictWarning @refresh="replaceConfigWithConflictingConfig()" v-if="conflictingConfig"/>
                    </transition-expand>
                    <div class="form-group with-border-bottom" v-if="config.buttonVolume !== undefined">
                        <label>{{ $t('Button volume') }}</label>
                        <div class="mt-3 mb-6">
                            <VueSlider v-model="config.buttonVolume" :min="0" :max="100" @change="onChange()" tooltip="always"
                                tooltip-placement="bottom" class="green"/>
                        </div>
                    </div>
                    <div class="form-group with-border-bottom" v-if="config.automaticTimeSync !== undefined">
                        <label class="checkbox2 checkbox2-grey">
                            <input type="checkbox" v-model="config.automaticTimeSync" @change="onChange()">
                            {{ $t('Automatic time synchronization') }}
                        </label>
                    </div>
                    <!-- i18n: ['firmwareUpdatePolicy_ALL_UPDATES', 'firmwareUpdatePolicy_SECURITY_UPDATES', 'firmwareUpdatePolicy_MANUAL_UPDATES', 'firmwareUpdatePolicy_MANUAL_UPDATES'] -->
                    <div class="form-group with-border-bottom" v-if="config.firmwareUpdatePolicy !== undefined">
                        <label for="firmwareUpdatePolicy">{{ $t('Firmware update policy') }}</label>
                        <select id="firmwareUpdatePolicy" class="form-control" v-model="config.firmwareUpdatePolicy" @change="onChange()">
                            <option v-for="option in ['ALL_UPDATES', 'SECURITY_UPDATES', 'MANUAL_UPDATES', 'DISABLED']" :key="option"
                                :value="option">
                                {{ $t('firmwareUpdatePolicy_' + option) }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </pending-changes-page>
    </div>
</template>

<script>
    import PendingChangesPage from "@/common/pages/pending-changes-page.vue";
    import {deepCopy} from "@/common/utils";
    import 'vue-slider-component/theme/antd.css';
    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import ConfigConflictWarning from "@/channels/config-conflict-warning.vue";

    export default {
        components: {
            ConfigConflictWarning,
            TransitionExpand,
            PendingChangesPage,
            VueSlider: () => import('vue-slider-component'),
        },
        props: {
            device: Object,
        },
        data() {
            return {
                hasPendingChanges: false,
                config: undefined,
                conflictingConfig: false,
            };
        },
        beforeMount() {
            this.cancelChanges();
        },
        methods: {
            onChange() {
                this.hasPendingChanges = true;
            },
            cancelChanges() {
                this.config = deepCopy(this.device.config);
                this.hasPendingChanges = false;
            },
            saveDeviceSettings() {
                const config = deepCopy(this.config);
                this.$http.put(`iodevices/${this.device.id}?safe=true`, {
                    config,
                    configBefore: this.device.configBefore
                }, {skipErrorHandler: [409]})
                    .then(response => {
                        this.device.config = response.body.config;
                        this.device.configBefore = response.body.configBefore;
                    })
                    .then(() => this.cancelChanges())
                    .catch(response => {
                        if (response.status === 409) {
                            this.conflictingConfig = response.body.details.config;
                        }
                    });
            },
            replaceConfigWithConflictingConfig() {
                this.device.config = this.conflictingConfig;
                this.device.configBefore = deepCopy(this.device.config);
                this.conflictingConfig = false;
                this.cancelChanges();
            },
        },
    }
</script>

<style lang="scss">
    @import "../../styles/variables";

    .vue-slider-mark-label.mark-on-top {
        top: auto !important;
        bottom: 100%;
        margin-bottom: 10px;
    }

    .with-border-bottom {
        padding-bottom: 1.5em;
        margin-bottom: 1.5em;
        border-bottom: 1px solid $supla-grey-light;
        &:last-child {
            border: 0;
        }
    }
</style>
