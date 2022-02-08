<template>
    <div>
        <dl>
            <dd>{{ $t('Value added') }}</dd>
            <dt class="text-center"
                v-tooltip="channel.hasPendingChanges && $t('Save or discard configuration changes first.')">
                <a class="btn btn-default btn-block btn-wrapped"
                    :class="{disabled: channel.hasPendingChanges}"
                    @click="changeInitialValues()">
                    {{ $t('Set value added') }}
                </a>
            </dt>
        </dl>
        <modal-confirm v-if="settingInitialValues"
            :header="$t('Set value added')"
            :loading="loading"
            class="modal-800"
            @confirm="saveChanges()"
            @cancel="settingInitialValues = false">
            <div class="initial-values-fields">
                <div class="form-group"
                    :key="counterName"
                    v-for="counterName in countersAvailable">
                    <label :for="'initial-value-' + counterName">{{ $t(labels[counterName] || counterName) }}</label>
                    <span class="input-group">
                        <input type="number"
                            step="0.001"
                            min="0"
                            :id="'initial-value-' + counterName"
                            max="10000000"
                            class="form-control"
                            v-model="initialValues[counterName]"
                            @change="$emit('change')">
                        <span class="input-group-addon">
                            kWh
                        </span>
                    </span>
                </div>
            </div>
            <channel-params-meter-initial-values-mode v-model="addToHistory"></channel-params-meter-initial-values-mode>
        </modal-confirm>
    </div>
</template>

<script>
    import ChannelParamsMeterInitialValuesMode from "./channel-params-meter-initial-values-mode";

    export default {
        components: {ChannelParamsMeterInitialValuesMode},
        props: ['channel'],
        data() {
            return {
                settingInitialValues: false,
                initialValues: {},
                addToHistory: undefined,
                labels: {
                    forwardActiveEnergy: 'Forward active energy', // i18n
                    reverseActiveEnergy: 'Reverse active energy', // i18n
                    forwardReactiveEnergy: 'Forward reactive energy', // i18n
                    reverseReactiveEnergy: 'Reverse reactive energy', // i18n
                    forwardActiveEnergyBalanced: 'Forward active energy balanced', // i18n
                    reverseActiveEnergyBalanced: 'Reverse active energy balanced', // i18n
                },
            };
        },
        methods: {
            changeInitialValues() {
                const initialValues = this.channel.config.electricityMeterInitialValues || {};
                this.initialValues = {...initialValues};
                this.addToHistory = this.currentAddToHistory;
                this.settingInitialValues = true;
                for (const counterName of this.countersAvailable) {
                    if (!this.initialValues[counterName]) {
                        this.initialValues[counterName] = 0;
                    }
                }
            },
            saveChanges() {
                this.channel.config.electricityMeterInitialValues = this.initialValues;
                this.channel.config.addToHistory = this.addToHistory;
                this.$emit('save');
                this.settingInitialValues = false;
            }
        },
        computed: {
            countersAvailable() {
                return this.channel.config.countersAvailable || [];
            },
            currentAddToHistory() {
                return !!this.channel.config.addToHistory;
            },
        }
    };
</script>

<style lang="scss"
    scoped>
    @import '../../styles/mixins';

    .initial-values-fields {
        display: flex;
        flex-wrap: wrap;
        .form-group {
            min-width: 50%;
            @include on-xs-and-down {
                width: 100%;
            }
        }
    }

</style>
