<template>
    <div v-if="currentState" class="channel-state-table">
        <dl v-if="currentState.temperature !== undefined">
            <dd>{{ $t('Temperature') }}</dd>
            <dt v-if="currentState.temperature > -272">{{ currentState.temperature }}&deg;C</dt>
            <dt v-else>?&deg;C</dt>
        </dl>
        <dl v-if="currentState.humidity !== undefined">
            <dd>{{ $t('Humidity') }}</dd>
            <dt>{{ currentState.humidity }}%</dt>
        </dl>
        <dl v-if="currentState.depth !== undefined">
            <dd>{{ $t('Depth') }}</dd>
            <dt>
                <span v-if="currentState.value === null">---</span>
                <span v-else>{{ currentState.depth }} m</span>
            </dt>
        </dl>
        <dl v-if="currentState.distance !== undefined">
            <dd>{{ $t('Distance') }}</dd>
            <dt>
                <span v-if="currentState.value === null">---</span>
                <span v-else>{{ currentState.distance }} m</span>
            </dt>
        </dl>
        <dl v-if="currentState.color_brightness">
            <dd>{{ $t('Color') }}</dd>
            <dt>
                <span class="rgb-color-preview"
                    :style="{'background-color': cssColor(currentState.color)}"></span>
            </dt>
            <dd>{{ $t('Color brightness') }}</dd>
            <dt>{{ currentState.color_brightness }}%</dt>
        </dl>
        <dl v-if="currentState.brightness">
            <dd>{{ $t('Brightness') }}</dd>
            <dt>{{ currentState.brightness }}%</dt>
        </dl>
        <dl v-if="currentState.isCalibrating">
            <dd>{{ $t('Calibration') }}</dd>
            <dt></dt>
        </dl>
        <dl v-if="!currentState.isCalibrating && currentState.shut !== undefined">
            <dd v-if="channel.function.name === 'TERRACE_AWNING'">{{ $t('Percentage of extension') }}</dd>
            <dd v-else>{{ $t('Percentage of closing') }}</dd>
            <dt>{{ currentState.shut }}%</dt>
        </dl>
        <dl v-if="!currentState.isCalibrating && currentState.tiltPercent !== undefined && channel.config.tiltingTimeS > 0">
            <dd>{{ $t('Tilt percent') }}</dd>
            <dt>{{ currentState.tiltPercent }}%</dt>
        </dl>
        <dl v-if="!currentState.isCalibrating && currentState.tiltAngle !== undefined">
            <dd>{{ $t('Tilt angle') }}</dd>
            <dt v-if="channel.config.tiltingTimeS > 0">{{ currentState.tiltAngle }}&deg;</dt>
            <dt v-else>{{ $t('configuration missing') }}</dt>
        </dl>
        <dl v-if="currentState.closed !== undefined && channel.function.name === 'VALVEPERCENTAGE'">
            <dd>{{ $t('Percentage of closing') }}</dd>
            <dt>{{ currentState.closed }}%</dt>
        </dl>
        <dl v-if="currentState.calculatedValue !== undefined">
            <dd>{{ $t('Meter value') }}</dd>
            <dt>
                {{ currentState.calculatedValue|roundToDecimals }} {{ currentState.unit || '' }}
            </dt>
        </dl>
        <dl v-if="currentState.phases && currentState.phases[0].totalForwardActiveEnergy !== undefined">
            <dd>{{ $t('Forward active energy') }}</dd>
            <dt>{{ (currentState.phases[0].totalForwardActiveEnergy + currentState.phases[1].totalForwardActiveEnergy + currentState.phases[2].totalForwardActiveEnergy) | roundToDecimals }} kWh</dt>
        </dl>
        <ChannelStateTableHvac v-if="currentState.connected && isHvac" :channel="channel" :state="currentState"/>
        <strong v-if="currentState.value !== undefined">
            <span v-if="currentState.value === null">---</span>
            <span v-else-if="channel.function.name === 'WEIGHTSENSOR'">
                <span v-if="currentState.value >= 2000">
                    {{ currentState.value / 1000 | roundToDecimals(4) }} kg
                </span>
                <span v-else>
                    {{ currentState.value }} g
                </span>
            </span>
            <span v-else-if="channel.function.name === 'RAINSENSOR'">
                {{ currentState.value / 1000 | roundToDecimals(4) }} l/m
            </span>
            <span v-else-if="channel.function.name === 'PRESSURESENSOR'">
                {{ currentState.value }} hPa
            </span>
            <span v-else-if="channel.function.name === 'WINDSENSOR'">
                {{ currentState.value }} m/s
            </span>
            <span v-else-if="['GENERAL_PURPOSE_MEASUREMENT', 'GENERAL_PURPOSE_METER'].includes(channel.function.name)">
                {{ currentState.value | formatGpmValue(channel.config) }}
            </span>
            <span v-else>
                {{ currentState.value }}
            </span>
        </strong>
        <div class="channel-state-labels">
            <div v-if="currentState.flooding === true">
                <span class="label label-danger">{{ $t('Flooding') }}</span>
            </div>
            <div v-if="currentState.manuallyClosed === true">
                <span class="label label-warning">{{ $t('Manually closed') }}</span>
            </div>
            <div v-if="currentState.connected === false">
                <span class="label label-danger">{{ $t('Disconnected') }}</span>
            </div>
            <div v-if="currentState.operational === false && currentState.connected">
                <span class="label label-warning">{{ $t('Not available') }}</span>
            </div>
            <div v-if="currentState.currentOverload === true">
                <span class="label label-danger">{{ $t('Current Overload') }}</span>
            </div>
            <div v-if="currentState.clockError === true">
                <span class="label label-warning">{{ $t('Clock error') }}</span>
            </div>
            <div v-if="currentState.thermometerError === true">
                <span class="label label-danger">{{ $t('Thermometer error') }}</span>
            </div>
            <div v-if="currentState.batteryCoverOpen === true">
                <span class="label label-danger">{{ $t('Battery cover open') }}</span>
            </div>
            <div v-if="currentState.forcedOffBySensor === true">
                <span class="label label-info">{{ $t('Forced off by sensor') }}</span>
            </div>
        </div>
    </div>
</template>

<script>
    import ChannelStateTableHvac from "@/channels/channel-state-table-hvac.vue";
    import {mapState} from "pinia";
    import {useChannelsStore} from "@/stores/channels-store";

    export default {
        components: {ChannelStateTableHvac},
        props: ['channel', 'state'],
        mounted() {
        },
        methods: {
            cssColor(hexStringColor) {
                return hexStringColor.replace('0x', '#');
            }
        },
        computed: {
            currentState() {
                return this.state || this.stateFromStore;
            },
            isHvac() {
                return ['HVAC', 'THERMOSTATHEATPOLHOMEPLUS'].includes(this.channel.type.name);
            },
            ...mapState(useChannelsStore, {channels: 'all'}),
            stateFromStore() {
                return this.channels[this.channel.id]?.state;
            },
        },
    };
</script>

<style lang="scss">
    @import "../styles/variables";

    .channel-state-table {
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
            margin-top: 5px;
        }

        .channel-state-icons {
            > span {
                display: inline-block;
                padding: 0 5px;
                font-size: 1.3em;
            }
        }
    }
</style>
