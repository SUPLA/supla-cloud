<template>
    <div class="channel-state-table-hvac">
        <div class="d-flex justify-content-center align-items-center">
            <strong>{{ mainTemperature }}&deg;C</strong>
            <span class="arrow-container">
                <fa icon="angle-double-up" fixed-width class="arrow heating" v-if="state.heating"/>
                <fa icon="angle-double-down" class="arrow cooling" v-else-if="state.cooling"/>
                <fa icon="angle-double-right" class="arrow" v-else-if="state.mode !== 'OFF'"/>
            </span>
            <span class="small" v-if="state.mode !== 'OFF'">
                <span v-if="state.mode !== 'COOL'">{{ state.temperatureHeat }}&deg;C</span>
                <span v-if="state.mode === 'HEAT_COOL'">&nbsp;&hyphen;&nbsp;</span>
                <span v-if="state.mode !== 'HEAT'">{{ state.temperatureCool }}&deg;C</span>
                <span class="ml-2" v-if="state.weeklyScheduleTemporalOverride" v-tooltip.bottom="$t('Temporal override')">
                    <fa icon="hand" fixed-width/>
                </span>
            </span>
        </div>
        <div class="channel-state-icons d-flex justify-content-center my-2">
            <span v-if="state.mode === 'OFF'" v-tooltip.bottom="$t('Mode: off')">
                <fa icon="power-off" fixed-width/>
            </span>
            <span v-if="['HEAT_COOL', 'HEAT'].includes(state.mode)" v-tooltip.bottom="$t('Mode: heat')">
                <IconHeating class="text-red"/>
            </span>
            <span v-if="['HEAT_COOL', 'COOL'].includes(state.mode)" v-tooltip.bottom="$t('Mode: cool')">
                <IconCooling class="text-blue"/>
            </span>
            <span v-if="!state.manual" v-tooltip.bottom="$t('Weekly schedule')">
                <fa icon="calendar-week" fixed-width class="text-green"/>
            </span>
            <span v-if="state.countdownTimer" v-tooltip.bottom="$t('With a timer')">
                <fa icon="clock" fixed-width/>
            </span>
        </div>
        <div v-if="(state.heating || state.cooling) && state.partially" class="small">
            <span v-if="state.heating">{{ $t('Heating: {percent}%', {percent: state.partially}) }}</span>
            <span v-else>{{ $t('Cooling: {percent}%', {percent: state.partially}) }}</span>
            <div class="progress">
                <div :class="['progress-bar', state.heating ? 'progress-bar-danger' : 'progress-bar-info']" role="progressbar"
                    :style="{width: `${state.partially}%`}"></div>
            </div>
        </div>
    </div>
</template>

<script>
    import IconHeating from "@/common/icons/icon-heating.vue";
    import IconCooling from "@/common/icons/icon-cooling.vue";

    export default {
        components: {IconCooling, IconHeating},
        props: ['channel', 'state'],
        computed: {
            mainTemperature() {
                let temp = this.state.temperatureMain;
                return temp === null || temp < -272 ? '?' : temp;
            }
        },
    };
</script>

<style scoped lang="scss">
    @import "../styles/variables";

    .channel-state-table-hvac {
        font-size: 1.3em;
    }

    .arrow-container {
        display: inline-block;
        margin: 0 5px;
        .arrow {
            color: $supla-green;
            &.cooling {
                animation: passing-down 2s linear infinite;
                color: $supla-blue;
            }
            &.heating {
                animation: passing-up 2s linear infinite;
                color: $supla-red;
            }
        }
    }

    .progress {
        height: 2px;
        max-width: 50%;
        margin: 10px auto;
    }

    @keyframes passing-down {
        0% {
            -webkit-transform: translateY(-50%);
            -ms-transform: translateY(-50%);
            transform: translateY(-50%);
            opacity: 0;
        }

        50% {
            -webkit-transform: translateY(0%);
            -ms-transform: translateY(0%);
            transform: translateY(0%);
            opacity: 1;
        }

        100% {
            -webkit-transform: translateY(50%);
            -ms-transform: translateY(50%);
            transform: translateY(50%);
            opacity: 0;
        }
    }

    @keyframes passing-up {
        0% {
            -webkit-transform: translateY(50%);
            -ms-transform: translateY(50%);
            transform: translateY(50%);
            opacity: 0;
        }

        50% {
            -webkit-transform: translateY(0%);
            -ms-transform: translateY(0%);
            transform: translateY(0%);
            opacity: 1;
        }

        100% {
            -webkit-transform: translateY(-50%);
            -ms-transform: translateY(-50%);
            transform: translateY(-50%);
            opacity: 0;
        }
    }
</style>
