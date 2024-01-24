<template>
    <div>
        <a class="d-flex accordion-header" @click="displayGroup('measurements')">
            <span class="flex-grow-1">{{ $t('Measurement settings') }}</span>
            <span>
                <fa :icon="group === 'measurements' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'measurements'">
                <dl>
                    <dd>{{ $t('Value multiplier') }}</dd>
                    <dt>
                        <VueNumber v-model="valueMultiplier"
                            @change="$emit('change')"
                            :min="-2000000"
                            :max="2000000"
                            :placeholder="channel.config.defaults.valueMultiplier"
                            v-bind="{decimal: '.', precision: 3, separator: ' '}"
                            class="form-control text-center"/>
                    </dt>
                    <dd>{{ $t('Value divider') }}</dd>
                    <dt>
                        <VueNumber v-model="valueDivider"
                            @change="$emit('change')"
                            :min="-2000000"
                            :max="2000000"
                            :placeholder="channel.config.defaults.valueDivider"
                            v-bind="{decimal: '.', precision: 3, separator: ' '}"
                            class="form-control text-center"/>
                    </dt>
                    <dd>{{ $t('Value added') }}</dd>
                    <dt>
                        <VueNumber v-model="valueAdded"
                            @change="$emit('change')"
                            :min="-100000000"
                            :max="100000000"
                            :placeholder="channel.config.defaults.valueAdded"
                            v-bind="{
                        decimal: '.',
                        precision: 3,
                        separator: ' ',
                        prefix: channel.config.unitBeforeValue + (channel.config.noSpaceBeforeValue ? '' : ' '),
                        suffix: (channel.config.noSpaceAfterValue ? '' : ' ') + channel.config.unitAfterValue,
                    }"
                            class="form-control text-center"/>
                    </dt>
                    <dd>{{ $t('Precision') }}</dd>
                    <dt>
                        <VueNumber v-model="valuePrecision"
                            @change="$emit('change')"
                            :min="0"
                            :max="10"
                            :placeholder="channel.config.defaults.valuePrecision"
                            v-bind="{precision: 0}"
                            class="form-control text-center"/>
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
                            <span class="input-group-addon">
                                {{ $t('x') }}
                            </span>
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
                            <unit-symbol-helper @typed="channel.config[lastUnitField] += $event"></unit-symbol-helper>
                        </span>
                    </dt>
                    <dd>{{ $t('Example value') }}</dd>
                    <dt>
                        <span class="help-block text-center">
                            (
                            <input type="number" class="example-measurement-input no-spinner text-center" step="1" v-model="exampleValue">
                            &middot; {{ channel.config.valueMultiplier }}
                            ÷ {{ channel.config.valueDivider }})
                            + {{ channel.config.valueAdded | formatGpmValue(channel.config) }}
                            =
                            <strong>{{ exampleValue * channel.config.valueMultiplier / channel.config.valueDivider + channel.config.valueAdded | formatGpmValue(channel.config) }}</strong>
                        </span>
                    </dt>
                </dl>
            </div>
        </transition-expand>
        <a class="d-flex accordion-header" @click="displayGroup('history')">
            <span class="flex-grow-1">{{ $t('History') }}</span>
            <span>
                <fa :icon="group === 'history' ? 'chevron-down' : 'chevron-right'"/>
            </span>
        </a>
        <transition-expand>
            <div v-show="group === 'history'">
                <dl>
                    <dd>{{ $t('Store measurements history') }}</dd>
                    <dt class="text-center">
                        <toggler v-model="channel.config.keepHistory"
                            @input="$emit('change')"></toggler>
                    </dt>
                    <dd>{{ $t('Chart type') }}</dd>
                    <dt>
                        <ChannelParamsButtonSelector
                            v-model="channel.config.chartType"
                            @input="$emit('change')"
                            :values="[{id: 'LINEAR', label: $t('Linear')}, {id: 'BAR', label: $t('Bar')}, {id: 'CANDLE', label: $t('Candle')}]"/>
                    </dt>
                </dl>
            </div>
        </transition-expand>
    </div>
</template>

<script>
    import ChannelParamsButtonSelector from "./channel-params-button-selector";
    import TransitionExpand from "../../common/gui/transition-expand";
    import UnitSymbolHelper from "./unit-symbol-helper";
    import {component as VueNumber} from '@coders-tm/vue-number-format'

    export default {
        components: {TransitionExpand, ChannelParamsButtonSelector, UnitSymbolHelper, VueNumber},
        props: ['channel'],
        data() {
            return {
                formValues: {
                    valueDivider: 1,
                    valueMultiplier: 1,
                    valueAdded: 0,
                    valuePrecision: 0,
                },
                group: undefined,
                exampleValue: 100,
                lastUnitField: 'unitPrefix',
            };
        },
        beforeMount() {
            this.initFormFromChannel();
        },
        methods: {
            displayGroup(group) {
                if (this.group === group) {
                    this.group = undefined;
                } else {
                    this.group = group;
                }
            },
            initFormFromChannel() {
                this.formValues.valueDivider = this.channel.config.valueDivider;
                this.formValues.valueMultiplier = this.channel.config.valueMultiplier;
                this.formValues.valueAdded = this.channel.config.valueAdded;
                this.formValues.valuePrecision = this.channel.config.valuePrecision;
            },
            setConfigValue(name, value, valueIfZero = 0) {
                this.formValues[name] = value;
                if (!value) {
                    this.channel.config[name] = this.channel.config.defaults[name] || valueIfZero;
                } else if (+value === 0) {
                    this.channel.config[name] = valueIfZero;
                } else {
                    this.channel.config[name] = +value;
                }
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
        }
    };
</script>

<style scoped lang="scss">
    @import "../../styles/variables";

    .example-measurement-input {
        width: 40px;
        font-size: .8em;
    }

    .with-space {
        display: inline-block;
        height: 5px;
        width: 10px;
        border-left: 1px solid $supla-grey-dark;
        border-right: 1px solid $supla-grey-dark;
        border-bottom: 1px dotted $supla-grey-dark;
    }

    .without-space {
        display: inline-block;
        height: 5px;
        width: 2px;
        margin: 0 4px;
        border-left: 1px solid $supla-grey-dark;
    }
</style>
