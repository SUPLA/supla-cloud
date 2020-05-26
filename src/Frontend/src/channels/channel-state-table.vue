<template>
    <div v-if="currentState">
        <dl v-if="currentState.temperature !== undefined">
            <dd>{{ $t('Temperature') }}</dd>
            <dt>{{ currentState.temperature }}&deg;C</dt>
        </dl>
        <dl v-if="currentState.humidity !== undefined">
            <dd>{{ $t('Humidity') }}</dd>
            <dt>{{ currentState.humidity }}%</dt>
        </dl>
        <dl v-if="currentState.depth !== undefined">
            <dd>{{ $t('Depth') }}</dd>
            <dt>{{ currentState.depth }} m</dt>
        </dl>
        <dl v-if="currentState.distance !== undefined">
            <dd>{{ $t('Distance') }}</dd>
            <dt>{{ currentState.distance }} m</dt>
        </dl>
        <dl v-if="currentState.color_brightness">
            <dd>{{ $t('Color') }}</dd>
            <dt>
                <span class="rgb-color-preview"
                    :style="{'background-color': cssColor(currentState.color)}"></span>
            </dt>
            <dd>{{ $t('Color brightness') }}</dd>
            <dt>{{currentState.color_brightness}}%</dt>
        </dl>
        <dl v-if="currentState.brightness">
            <dd>{{ $t('Brightness') }}</dd>
            <dt>{{currentState.brightness}}%</dt>
        </dl>
        <dl v-if="currentState.is_calibrating">
            <dd>{{ $t('Calibration') }}</dd>
            <dt></dt>
        </dl>
        <dl v-if="currentState.shut !== undefined">
            <dd>{{ $t('Percentage of closing') }}</dd>
            <dt>{{currentState.shut}}%</dt>
        </dl>
        <dl v-if="currentState.closed !== undefined && channel.function.name === 'VALVEPERCENTAGE'">
            <dd>{{ $t('Percentage of closing') }}</dd>
            <dt>{{currentState.closed}}%</dt>
        </dl>
        <dl v-if="currentState.calculatedValue !== undefined">
            <dd>{{ $t('Meter value') }}</dd>
            <dt>{{currentState.calculatedValue|roundToDecimals}} {{currentState.unit || ''}}</dt>
        </dl>
        <dl v-if="currentState.phases && currentState.phases[0].totalForwardActiveEnergy !== undefined">
            <dd>{{ $t('Forward active energy') }}</dd>
            <dt>{{(currentState.phases[0].totalForwardActiveEnergy + currentState.phases[1].totalForwardActiveEnergy + currentState.phases[2].totalForwardActiveEnergy) | roundToDecimals}} kWh</dt>
        </dl>
        <dl v-if="currentState.value !== undefined">
            <dd>{{ $t('State') }}</dd>
            <dt>
                <span v-if="channel.function.name === 'WEIGHTSENSOR' && currentState.value >= 2000">
                    {{ currentState.value / 1000 | roundToDecimals(4) }}
                </span>
                <span v-else>
                    {{ currentState.value }}
                </span>
                <span v-if="channel.function.name === 'WINDSENSOR'">m/s</span>
                <span v-else-if="channel.function.name === 'PRESSURESENSOR'">hPa</span>
                <span v-else-if="channel.function.name === 'RAINSENSOR'">l/m<sup>2</sup></span>
                <span v-else-if="channel.function.name === 'WEIGHTSENSOR'">
                    <span v-if="currentState.value >= 2000">kg</span>
                    <span v-else>g</span>
                </span>
            </dt>
        </dl>
        <div class="channel-state-labels">
            <span class="label label-danger"
                v-if="currentState.flooding === true">
                {{ $t('Flooding') }}
            </span>
            <span class="label label-warning"
                v-if="currentState.manuallyClosed === true">
                {{ $t('Manually closed') }}
            </span>
            <span class="label label-danger"
                v-if="currentState.connected === false">
                {{ $t('Disconnected') }}
            </span>
        </div>
    </div>
</template>

<script>
    import EventBus from "../common/event-bus";

    export default {
        props: ['channel', 'state'],
        data() {
            return {
                timer: undefined,
                fetching: false,
            };
        },
        mounted() {
            if (!this.state) {
                this.fetchState();
                this.timer = setInterval(() => this.fetchState(), 7000);
            }
            EventBus.$on('channel-state-updated', this.fetchState);
        },
        methods: {
            fetchState() {
                if (!this.fetching) {
                    this.fetching = true;
                    this.$http.get(`channels/${this.channel.id}?include=state`)
                        .then(({body}) => this.$set(this.channel, 'state', body.state))
                        .finally(() => this.fetching = false);
                }
            },
            cssColor(hexStringColor) {
                return hexStringColor.replace('0x', '#');
            }
        },
        computed: {
            currentState() {
                return this.state || this.channel.state;
            }
        },
        beforeDestroy() {
            clearInterval(this.timer);
            EventBus.$off('channel-state-updated', this.loadNewClientAppsListener);
        }
    };
</script>

<style scoped
    lang="scss">
    dl {
        margin-bottom: 0;
        dd, dt {
            display: inline;
        }
        dt:after {
            display: block;
            content: '';
        }
    }

    .rgb-color-preview {
        display: inline-block;
        height: 10px;
        width: 40px;
        border-radius: 5px;
    }

    .channel-state-labels .label {
        display: inline-block;
        clear: both;
        margin-top: 5px;
    }
</style>
