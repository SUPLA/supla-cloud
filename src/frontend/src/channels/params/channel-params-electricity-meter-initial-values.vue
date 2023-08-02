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
            class="modal-800"
            @confirm="saveChanges()"
            @cancel="settingInitialValues = false"
            :cancellable="countersAvailable && countersAvailable.length > 0">
            <div v-if="countersAvailable && countersAvailable.length > 0">
                <div class="row mb-5">
                    <div class="col-xs-4">
                        <a v-for="counterName in countersAvailable" :key="`link-${counterName}`"
                            @click="currentCounter = counterName"
                            :class="['btn btn-block btn-wrapped ellipsis', {'btn-green': currentCounter === counterName, 'btn-white': currentCounter !== counterName}]">
                            {{ $t(labels[counterName] || counterName) }}
                        </a>
                    </div>
                    <div class="col-xs-8">
                        <div>
                            <h5 class="m-0 mb-2">{{ $t(labels[currentCounter] || currentCounter) }}</h5>
                            <ChannelParamsElectricityMeterInitialValue
                                :channel="channel"
                                :counter-name="currentCounter"
                                v-model="initialValues[currentCounter]"
                                :key="`form-${currentCounter}`"/>
                        </div>
                    </div>
                </div>
                <ChannelParamsMeterInitialValuesMode v-model="addToHistory"/>
            </div>
            <div v-else class="alert alert-warning">
                {{ $t('Electricity meter device has no available counters configured. Please upgrade the device firmware and try again.') }}
            </div>
        </modal-confirm>
    </div>
</template>

<script>
    import ChannelParamsMeterInitialValuesMode from "./channel-params-meter-initial-values-mode";
    import ChannelParamsElectricityMeterInitialValue from "@/channels/params/channel-params-electricity-meter-initial-value";

    export default {
        components: {ChannelParamsElectricityMeterInitialValue, ChannelParamsMeterInitialValuesMode},
        props: ['channel'],
        data() {
            return {
                settingInitialValues: false,
                initialValues: {},
                addToHistory: undefined,
                currentCounter: undefined,
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
                        this.$set(this.initialValues, counterName, 0);
                    }
                }
                this.currentCounter = this.countersAvailable[0];
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


</style>
