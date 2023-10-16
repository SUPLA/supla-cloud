<template>
    <div>
        <div class="radio">
            <label>
                <input type="radio"
                    value="CMD_WEEKLY_SCHEDULE"
                    v-model="mainMode"
                    @change="onChange()">
                {{ $t('Switch to weekly schedule') }}
            </label>
        </div>
        <div class="radio">
            <label>
                <input type="radio"
                    value="manual"
                    v-model="mainMode"
                    @change="onChange()">
                {{ $t('Switch to manual mode') }}
            </label>
        </div>
        <transition-expand>
            <div v-if="mainMode === 'manual'" class="well py-1">
                <div class="radio">
                    <label>
                        <input type="radio" value="CMD_SWITCH_TO_MANUAL" v-model="mode" @change="onChange()">
                        {{ $t('Manual latest') }}
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" value="HEAT" v-model="mode" @change="onChange()">
                        {{ $t('Manual heat') }}
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" value="COOL" v-model="mode" @change="onChange()">
                        {{ $t('Manual cool') }}
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" value="AUTO" v-model="mode" @change="onChange()">
                        {{ $t('Manual automatic') }}
                    </label>
                </div>
                <transition-expand>
                    <div v-if="mode !== 'AUTO'">
                        <div class="form-group">
                            <label class="checkbox2">
                                <input type="checkbox" v-model="setTemperatures">
                                <span>
                                    {{ $t('Set new temperatures') }}
                                </span>
                            </label>
                        </div>
                        <transition-expand>
                            <div v-if="setTemperatures">
                                <div class="form-group form-group-sm my-1">
                                    <span class="input-group">
                                        <span class="input-group-addon">
                                            <IconHeating/>
                                            {{ $t('Heat to') }}
                                        </span>
                                        <input type="number"
                                            step="0.1"
                                            :min="roomMin"
                                            :max="roomMax"
                                            class="form-control text-center"
                                            @change="temperatureChanged(program, 'setpointTemperatureHeat')"
                                            v-model="setpointTemperatureHeat">
                                        <span class="input-group-addon">
                                            &deg;C
                                        </span>
                                    </span>
                                </div>
                                <div class="form-group form-group-sm my-1">
                                    <span class="input-group">
                                        <span class="input-group-addon">
                                            <IconCooling/>
                                            {{ $t('Cool to') }}
                                        </span>
                                        <input type="number"
                                            step="0.1"
                                            :min="roomMin"
                                            :max="roomMax"
                                            class="form-control text-center"
                                            @change="temperatureChanged(program, 'setpointTemperatureHeat')"
                                            v-model="setpointTemperatureCool">
                                        <span class="input-group-addon">
                                            &deg;C
                                        </span>
                                    </span>
                                </div>
                            </div>
                        </transition-expand>
                        <div class="form-group">
                            <label class="checkbox2">
                                <input type="checkbox" v-model="countdownEnabled">
                                <span>
                                    {{ $t('For a limited time') }}
                                </span>
                            </label>
                        </div>
                    </div>
                </transition-expand>
            </div>
        </transition-expand>
        <div class="radio">
            <label>
                <input type="radio"
                    value="NOT_SET"
                    v-model="mainMode"
                    @change="onChange()">
                {{ $t('Set temperatures') }}
            </label>
        </div>
        <transition-expand>
            <div v-if="mainMode === 'NOT_SET'" class="well py-1">
                <div class="form-group form-group-sm my-1">
                    <span class="input-group">
                        <span class="input-group-addon">
                            <IconHeating/>
                            {{ $t('Heat to') }}
                        </span>
                        <input type="number"
                            step="0.1"
                            :min="roomMin"
                            :max="roomMax"
                            class="form-control text-center"
                            @change="temperatureChanged(program, 'setpointTemperatureHeat')"
                            v-model="setpointTemperatureHeat">
                        <span class="input-group-addon">
                            &deg;C
                        </span>
                    </span>
                </div>
                <div class="form-group form-group-sm my-1">
                    <span class="input-group">
                        <span class="input-group-addon">
                            <IconCooling/>
                            {{ $t('Cool to') }}
                        </span>
                        <input type="number"
                            step="0.1"
                            :min="roomMin"
                            :max="roomMax"
                            class="form-control text-center"
                            @change="temperatureChanged(program, 'setpointTemperatureHeat')"
                            v-model="setpointTemperatureCool">
                        <span class="input-group-addon">
                            &deg;C
                        </span>
                    </span>
                </div>
            </div>
        </transition-expand>
        <div class="radio">
            <label>
                <input type="radio"
                    value="OFF"
                    v-model="mainMode"
                    @change="onChange()">
                {{ $t('Turn off with timer') }}
            </label>
        </div>
        <transition-expand>
            <div v-if="mainMode === 'OFF'" class="well py-1">
                <div class="radio">
                    <label>
                        <input type="radio"
                            value="delay"
                            v-model="countdownMode"
                            @change="onChange()">
                        {{ $t('Period') }}
                    </label>
                </div>
                <transition-expand>
                    <div class="form-group my-1" v-if="countdownMode === 'delay'">
                        <span class="input-group">
                            <input type="number" class="form-control" min="1" v-model="countdownValue">
                            <span class="input-group-btn">
                                <a class="btn btn-white" @click="changeMultiplier()">
                                    <span v-if="multiplier === 1">{{ $t('seconds') }}</span>
                                    <span v-if="multiplier === 60">{{ $t('minutes') }}</span>
                                    <span v-if="multiplier === 3600">{{ $t('hours') }}</span>
                                    <span v-if="multiplier === 86400">{{ $t('days') }}</span>
                                </a>
                            </span>
                        </span>
                    </div>
                </transition-expand>
                <div class="radio">
                    <label>
                        <input type="radio" value="calendar" v-model="countdownMode" @change="onChange()">
                        {{ $t('To date') }}
                    </label>
                </div>
                <transition-expand>
                    <div class="form-group" v-if="countdownMode === 'calendar'">
                        <input type="datetime-local" v-model="countdownDate" @change="onChange()" class="form-control">
                    </div>
                </transition-expand>
            </div>
        </transition-expand>
    </div>
</template>

<script>

    import TransitionExpand from "@/common/gui/transition-expand.vue";
    import IconHeating from "@/common/icons/icon-heating.vue";
    import ChannelFunction from "@/common/enums/channel-function";
    import IconCooling from "@/common/icons/icon-cooling.vue";

    export default {
        components: {IconCooling, IconHeating, TransitionExpand},
        props: {
            subject: Object,
            value: Object,
        },
        data() {
            return {
                mode: 'CMD_WEEKLY_SCHEDULE',
                setpointTemperatureHeat: 0,
                setpointTemperatureCool: 0,
                setTemperatures: false,
                countdownEnabled: false,
                countdownMode: 'delay',
                multiplier: 60,
                countdownValue: 15,
            };
        },
        mounted() {

        },
        methods: {
            onChange() {

            },
            changeMultiplier() {
                if (this.multiplier === 1) {
                    this.multiplier = 60;
                } else if (this.multiplier === 60) {
                    this.multiplier = 3600;
                } else if (this.multiplier === 3600) {
                    this.multiplier = 86400;
                } else {
                    this.multiplier = 1;
                }
            },
        },
        computed: {
            mainMode: {
                get() {
                    if (['CMD_WEEKLY_SCHEDULE', 'OFF', 'NOT_SET'].includes(this.mode)) {
                        return this.mode;
                    } else {
                        return 'manual';
                    }
                },
                set(mode) {
                    if (['CMD_WEEKLY_SCHEDULE', 'OFF', 'NOT_SET'].includes(mode)) {
                        this.mode = mode;
                    } else {
                        this.mode = 'CMD_SWITCH_TO_MANUAL';
                    }
                }
            },
            roomMin() {
                return this.subject.config?.temperatureConstraints?.roomMin;
            },
            roomMax() {
                return this.subject.config?.temperatureConstraints?.roomMax;
            },
            heatAvailable() {
                return [ChannelFunction.HVAC_THERMOSTAT_AUTO].includes(this.subject.functionId)
                    || this.subject.config?.subfunction === 'HEAT';
            },
            coolAvailable() {
                return [ChannelFunction.HVAC_THERMOSTAT_AUTO].includes(this.subject.functionId)
                    || this.subject.config?.subfunction === 'COOL';
            },
        },
        watch: {
            channelFunction() {
            }
        }
    };
</script>

<style lang="scss">

</style>
