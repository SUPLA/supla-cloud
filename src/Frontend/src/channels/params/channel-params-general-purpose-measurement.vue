<template>
    <div>

        <dl>
            <dd>{{ $t('Value per unit') }}</dd>
            <dt>
                <input type="number"
                    step="0.0001"
                    min="-1000000"
                    max="1000000"
                    class="form-control text-center"
                    v-model="channel.config.impulsesPerUnit"
                    @change="$emit('change')">
            </dt>
            <dd>{{ $t('Initial value') }}</dd>
            <dt>
                <input type="number"
                    step="0.0001"
                    min="-1000000"
                    max="1000000"
                    class="form-control text-center"
                    v-model="channel.config.initialValue"
                    @change="$emit('change')">
            </dt>
        </dl>
        <span class="help-block text-center">
            {{ $t('Channel value') }} = <br>
            ({{ $t('Device channel value') }} ÷ {{ $t('Value per unit') }}) + {{ $t('Initial value') }}
        </span>
        <dl>
            <dd>{{ $t('Precision') }}</dd>
            <dt>
                <input type="number"
                    step="1"
                    min="0"
                    max="5"
                    class="form-control text-center"
                    v-model="channel.config.precision"
                    @change="$emit('change')">
            </dt>
            <dd>{{ $t('Unit') }}</dd>
            <dt>
                <span class="input-group">
                    <span class="input-group-addon">
                        {{ $t('prefix') }}
                    </span>
                    <input type="text"
                        class="form-control"
                        v-model="channel.config.unitPrefix"
                        @focusin="lastUnitField = 'unitPrefix'"
                        maxlength="4"
                        @change="$emit('change')">
                </span>
            </dt>
            <dd></dd>
            <dt>
                <span class="input-group">
                    <span class="input-group-addon">
                        {{ $t('suffix') }}
                    </span>
                    <input type="text"
                        class="form-control"
                        v-model="channel.config.unitSuffix"
                        @focusin="lastUnitField = 'unitSuffix'"
                        maxlength="4"
                        @change="$emit('change')">
                </span>
                <span class="help-block text-center">
                    <unit-symbol-helper @typed="channel.config[lastUnitField] += $event"></unit-symbol-helper>
                </span>
            </dt>
            <dd>{{$t('Store measurements history')}}</dd>
            <dt class="text-center">
                <toggler v-model="channel.config.storeMeasurementHistory"
                    @input="$emit('change')"></toggler>
            </dt>
            <dd>{{$t('Chart type')}}</dd>
            <dt>
                <channel-params-button-selector v-model="channel.config.chartType"
                    @input="$emit('change')"
                    :values="[{id: 0, label: $t('Linear')}, {id: 1, label: $t('Bar')}]"></channel-params-button-selector>
            </dt>
            <dd>{{$t('Chart data source type')}}</dd>
            <dt>
                <channel-params-button-selector v-model="channel.config.chartDataSourceType"
                    @input="$emit('change')"
                    :values="[{id: 0, label: $t('Differential')}, {id: 1, label: $t('Standard')}]"></channel-params-button-selector>
            </dt>
        </dl>
        <transition-expand>
            <dl v-if="channel.config.chartDataSourceType === 0">
                <dd>{{$t('Interpolate measurements')}}</dd>
                <dt class="text-center">
                    <toggler v-model="channel.config.interpolateMeasurements"
                        @input="$emit('change')"></toggler>
                </dt>
            </dl>
        </transition-expand>
    </div>
</template>

<script>
    import ChannelParamsButtonSelector from "./channel-params-button-selector";
    import TransitionExpand from "../../common/gui/transition-expand";
    import UnitSymbolHelper from "./unit-symbol-helper";

    export default {
        components: {TransitionExpand, ChannelParamsButtonSelector, UnitSymbolHelper},
        props: ['channel'],
        data() {
            return {
                lastUnitField: 'unitPrefix',
            };
        }
    };
</script>

<style>
    .frac {
        display: inline-block;
        position: relative;
        vertical-align: middle;
        letter-spacing: 0.001em;
        text-align: center;
    }

    .frac > span {
        display: block;
        padding: 0.1em;
    }

    .frac span.bottom {
        border-top: thin solid black;
    }

    .frac span.symbol {
        display: none;
    }
</style>
