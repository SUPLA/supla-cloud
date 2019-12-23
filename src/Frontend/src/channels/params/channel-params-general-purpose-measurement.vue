<template>
    <div>

        <dl>
            <dd>{{ $t('Measurement multiplier') }}</dd>
            <dt>
                <input type="number"
                    step="0.0001"
                    min="-1000"
                    max="1000"
                    class="form-control text-center"
                    v-model="channel.params.measurementMultiplier"
                    @change="$emit('change')">
            </dt>
            <dd>{{ $t('Measurement adjustment') }}</dd>
            <dt>
                <input type="number"
                    step="0.0001"
                    min="-1000"
                    max="1000"
                    class="form-control text-center"
                    v-model="channel.params.measurementAdjustment"
                    @change="$emit('change')">
            </dt>
            <dd>{{ $t('Precision') }}</dd>
            <dt>
                <input type="number"
                    step="1"
                    min="0"
                    max="5"
                    class="form-control text-center"
                    v-model="channel.params.precision"
                    @change="$emit('change')">
            </dt>
            <dd>{{ $t('Unit') }}</dd>
            <dt>
                <span class="input-group">
                    <input type="text"
                        class="form-control text-right"
                        v-model="channel.params.unitPrefix"
                        @change="$emit('change')">
                    <span class="input-group-addon">
                        {{ $t('value') }}
                    </span>
                    <input type="text"
                        class="form-control text-left"
                        v-model="channel.params.unitSuffix"
                        @change="$emit('change')">
                </span>
            </dt>
            <dd>{{$t('Store measurements history')}}</dd>
            <dt class="text-center">
                <toggler v-model="channel.params.storeMeasurementHistory"
                    @input="$emit('change')"></toggler>
            </dt>
            <dd>{{$t('Chart presentation')}}</dd>
            <dt>
                <channel-params-button-selector v-model="channel.params.chartPresentation"
                    @input="$emit('change')"
                    :values="[{id: 0, label: $t('Linear')}, {id: 1, label: $t('Bar')}]"></channel-params-button-selector>
            </dt>
            <dd>{{$t('Chart type')}}</dd>
            <dt>
                <channel-params-button-selector v-model="channel.params.chartType"
                    @input="$emit('change')"
                    :values="[{id: 0, label: $t('Differential')}, {id: 1, label: $t('Standard')}]"></channel-params-button-selector>
            </dt>
        </dl>
        <transition-expand>
            <dl v-if="channel.params.chartType === 0">
                <dd>{{$t('Interpolate measurements')}}</dd>
                <dt class="text-center">
                    <toggler v-model="channel.params.interpolateMeasurements"
                        @input="$emit('change')"></toggler>
                </dt>
            </dl>
        </transition-expand>
    </div>
</template>

<script>
    import ChannelParamsButtonSelector from "./channel-params-button-selector";
    import TransitionExpand from "../../common/gui/transition-expand";

    export default {
        components: {TransitionExpand, ChannelParamsButtonSelector},
        props: ['channel'],
    };
</script>
