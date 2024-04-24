<template>
    <div>
        <dl>
            <dd></dd>
            <dt>
                <button type="button" class="btn btn-white btn-xs" @click="resetToDefaults()">
                    {{ $t('Reset to defaults') }}
                </button>
            </dt>
            <dd>{{ $t('Value multiplier') }}</dd>
            <dt>
                <VueNumber v-model="valueMultiplier"
                    :min="-2000000"
                    :max="2000000"
                    :placeholder="1"
                    v-bind="{decimal: '.', precision: 3, separator: ' '}"
                    class="form-control text-center"/>
            </dt>
            <dd>{{ $t('Value divider') }}</dd>
            <dt>
                <VueNumber v-model="valueDivider"
                    :min="-2000000"
                    :max="2000000"
                    :placeholder="1"
                    v-bind="{decimal: '.', precision: 3, separator: ' '}"
                    class="form-control text-center"/>
            </dt>
            <dd>{{ $t('Value added') }}</dd>
            <dt>
                <VueNumber v-model="valueAdded"
                    :min="-100000000"
                    :max="100000000"
                    :placeholder="0"
                    v-bind="{
                        decimal: '.',
                        precision: 3,
                        separator: ' ',
                    }"
                    class="form-control text-center"/>
                <ChannelParamsMeterInitialValuesMode v-if="channel.function.name === 'GENERAL_PURPOSE_METER'"
                    v-model="channel.config.includeValueAddedInHistory" @input="$emit('change')"/>
            </dt>
            <dd>{{ $t('Precision') }}</dd>
            <dt>
                <div class="btn-group btn-group-flex">
                    <a :class="'btn ' + (prec === valuePrecision ? 'btn-green' : 'btn-default')"
                        v-for="prec in [0,1,2,3,4]" :key="prec" @click="valuePrecision = prec">
                        {{ prec }}
                    </a>
                </div>
            </dt>
            <dd>{{ $t('Unit') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="text"
                        class="form-control text-right"
                        v-model="channel.config.unitBeforeValue"
                        @focusin="lastUnitField = 'unitBeforeValue'"
                        maxlength="14"
                        @change="$emit('change')">
                    <span class="input-group-addon pr-0">
                        <a @click="channel.config.noSpaceBeforeValue = !channel.config.noSpaceBeforeValue; $emit('change')">
                            <span :class="channel.config.noSpaceBeforeValue ? 'without-space' : 'with-space'"></span>
                        </a>
                    </span>
                    <span class="input-group-addon">x</span>
                    <span class="input-group-addon pl-0">
                        <a @click="channel.config.noSpaceAfterValue = !channel.config.noSpaceAfterValue; $emit('change')">
                            <span :class="channel.config.noSpaceAfterValue ? 'without-space' : 'with-space'"></span>
                        </a>
                    </span>
                    <input type="text"
                        class="form-control"
                        v-model="channel.config.unitAfterValue"
                        @focusin="lastUnitField = 'unitAfterValue'"
                        maxlength="14"
                        @change="$emit('change')">
                </span>
                <span class="help-block text-center">
                    <unit-symbol-helper @typed="channel.config[lastUnitField] += $event; $emit('change')"></unit-symbol-helper>
                </span>
            </dt>
            <dd>{{ $t('Example value') }}</dd>
            <dt>
                <div class="help-block text-center">
                    (
                    <input type="number" class="example-measurement-input no-spinner text-center" step="1" v-model="exampleValue"
                        placeholder="0">
                    &middot; {{ channel.config.valueMultiplier }}
                    ÷ {{ channel.config.valueDivider }})
                    + {{ channel.config.valueAdded }}
                    =
                    <div>
                        <strong>{{ exampleValue * channel.config.valueMultiplier / (channel.config.valueDivider || 1) + channel.config.valueAdded | formatGpmValue(channel.config) }}</strong>
                    </div>
                </div>
            </dt>
            <dd>{{ $t('Refresh interval') }}</dd>
            <dt>
                <toggler v-model="defaultRefreshInterval" :label="$t('Use device default')" @input="$emit('change')"/>
                <NumberInput v-model="channel.config.refreshIntervalMs"
                    v-if="!defaultRefreshInterval"
                    :min="200"
                    :max="65535"
                    suffix=" ms"
                    class="form-control text-center mt-2"
                    @input="$emit('change')"/>
            </dt>
        </dl>
    </div>
</template>

<script>
    import UnitSymbolHelper from "./unit-symbol-helper";
    import {component as VueNumber} from '@coders-tm/vue-number-format'
    import ChannelParamsMeterInitialValuesMode from "@/channels/params/channel-params-meter-initial-values-mode.vue";
    import NumberInput from "@/common/number-input.vue";

    export default {
        components: {NumberInput, ChannelParamsMeterInitialValuesMode, UnitSymbolHelper, VueNumber},
        props: ['channel'],
        data() {
            return {
                formValues: {
                    valueDivider: 1,
                    valueMultiplier: 1,
                    valueAdded: 0,
                    valuePrecision: 0,
                },
                exampleValue: 100,
                lastUnitField: 'unitAfterValue',
            };
        },
        beforeMount() {
            this.initFormFromChannel();
        },
        methods: {
            initFormFromChannel() {
                this.formValues.valueDivider = this.channel.config.valueDivider;
                this.formValues.valueMultiplier = this.channel.config.valueMultiplier;
                this.formValues.valueAdded = this.channel.config.valueAdded;
                this.formValues.valuePrecision = this.channel.config.valuePrecision;
            },
            setConfigValue(name, value, valueIfZero = 0) {
                this.formValues[name] = value;
                if (!value || +value === 0) {
                    this.channel.config[name] = valueIfZero;
                } else {
                    this.channel.config[name] = +value;
                }
                this.$emit('change');
            },
            resetToDefaults() {
                this.valueDivider = this.channel.config.defaults.valueDivider || 1;
                this.valueMultiplier = this.channel.config.defaults.valueMultiplier || 1;
                this.valueAdded = this.channel.config.defaults.valueAdded || 0;
                this.valuePrecision = this.channel.config.defaults.valuePrecision || 0;
                this.channel.config.unitBeforeValue = this.channel.config.defaults.unitBeforeValue || '';
                this.channel.config.unitAfterValue = this.channel.config.defaults.unitAfterValue || '';
                this.$emit('change');
            },
        },
        computed: {
            valueDivider: {
                set(v) {
                    this.setConfigValue('valueDivider', v, 1);
                },
                get() {
                    return this.formValues.valueDivider;
                }
            },
            valueMultiplier: {
                set(v) {
                    this.setConfigValue('valueMultiplier', v, 1);
                },
                get() {
                    return this.formValues.valueMultiplier;
                }
            },
            valueAdded: {
                set(v) {
                    this.setConfigValue('valueAdded', v, 0);
                },
                get() {
                    return this.formValues.valueAdded;
                }
            },
            valuePrecision: {
                set(v) {
                    this.setConfigValue('valuePrecision', v, 0);
                },
                get() {
                    return this.formValues.valuePrecision;
                }
            },
            defaultRefreshInterval: {
                set(v) {
                    this.channel.config.refreshIntervalMs = v ? 0 : 2000;
                },
                get() {
                    return +this.channel.config.refreshIntervalMs === 0;
                }
            }
        },
    };
</script>

<style scoped lang="scss">
    @import "../../styles/variables";

    .example-measurement-input {
        width: 40px;
        font-size: .8em;
    }

    .with-space, .without-space {
        position: relative;
        display: inline-block;
        height: 5px;
        width: 10px;
        border-left: 1px solid $supla-grey-dark;
        border-right: 1px solid $supla-grey-dark;
        border-bottom: 1px dotted $supla-grey-dark;
    }

    .without-space::after {
        display: inline-block;
        content: ' ';
        position: absolute;
        transform: rotate(-45deg);
        width: 13px;
        left: -2px;
        top: 2px;
        border-bottom: 1px solid $supla-red;
    }
</style>
