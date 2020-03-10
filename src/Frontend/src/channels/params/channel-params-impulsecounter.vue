<template>
    <dl>
        <dd>{{ $t('Unit') }}</dd>
        <dt>
            <input type="text"
                maxlength="4"
                class="form-control text-center"
                v-model="channel.config.unit"
                @input="$emit('change')">
        </dt>
        <channel-params-meter-cost :channel="channel"
            :unit="unit"
            @change="$emit('change')"></channel-params-meter-cost>
        <dd>{{ $t('Impulses') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="1"
                    min="0"
                    max="1000000"
                    class="form-control text-center"
                    v-model="channel.config.impulsesPerUnit"
                    @change="$emit('change')">
                <span class="input-group-addon">
                    / {{ unit }}
                </span>
            </span>
        </dt>
        <dd>{{ $t('Initial value') }}</dd>
        <dt>
            <span class="input-group">
                <input type="number"
                    step="0.01"
                    min="0"
                    max="1000000"
                    class="form-control text-center"
                    v-model="channel.config.initialValue"
                    @change="$emit('change')">
                <span class="input-group-addon">
                    {{ unit }}
                </span>
            </span>
        </dt>
    </dl>
</template>

<script>
    import ChannelParamsMeterCost from "./channel-params-meter-cost";

    export default {
        components: {ChannelParamsMeterCost},
        props: ['channel'],
        computed: {
            unit() {
                let defaultUnit = 'm³';
                switch(this.channel.function.name) {
                    case 'IC_ELECTRICITYMETER':
                        defaultUnit = 'kWh';
                        break;
                    case 'IC_HEATMETER':
                        defaultUnit = 'GJ';
                        break;
                }
                return this.channel.config.unit || defaultUnit;
            }
        }
    };
</script>
