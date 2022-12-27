<template>
    <div>
        <dl v-if="advancedModeAvailable" class="mb-3">
            <dd>{{ $t('Set different values for each phase') }}</dd>
            <dt class="text-center">
                <Toggler v-model="advancedMode"/>
            </dt>
        </dl>
        <div v-if="advancedMode">
            <div class="form-group" v-for="phaseNumber in enabledPhases" :key="`phase-${phaseNumber}`">
                <label :for="`initial-value-${phaseNumber}`">{{ $t('Phase {phaseNumber}', {phaseNumber}) }}</label>
                <span class="input-group">
                    <input type="number"
                        step="0.001"
                        min="-10000000"
                        :id="`initial-value-${phaseNumber}`"
                        max="10000000"
                        class="form-control"
                        v-model="value[phaseNumber]">
                    <span class="input-group-addon">
                        kWh
                    </span>
                </span>
            </div>
        </div>
        <div class="form-group" v-else>
            <label for="initial-value">{{ $t('Value added') }}</label>
            <span class="input-group">
                <input type="number"
                    step="0.001"
                    min="-10000000"
                    id="initial-value"
                    max="10000000"
                    class="form-control"
                    v-model="value"
                    @change="$emit('change')">
                <span class="input-group-addon">
                    kWh
                </span>
            </span>
        </div>
    </div>
</template>

<script>
    import Toggler from "@/common/gui/toggler";

    export default {
        components: {Toggler},
        props: {
            channel: Object,
            value: [Number, Object],
            counterName: String,
        },
        data() {
            return {};
        },
        methods: {},
        computed: {
            advancedMode: {
                get() {
                    return typeof this.value === 'object';
                },
                set(value) {
                    if (value) {
                        const defaultInitialValue = this.value || 0;
                        const valueForPhases = {};
                        for (let phaseNo of this.enabledPhases) {
                            valueForPhases['' + phaseNo] = defaultInitialValue;
                        }
                        this.$emit('input', valueForPhases);
                    } else {
                        const defaultInitialValue = this.value[this.enabledPhases[0]] || 0;
                        this.$emit('input', defaultInitialValue);
                    }
                }
            },
            enabledPhases() {
                return this.channel.config.enabledPhases || [1, 2, 3];
            },
            advancedModeAvailable() {
                return !['forwardActiveEnergyBalanced', 'reverseActiveEnergyBalanced'].includes(this.counterName);
            }
        }
    };
</script>
