<template>
    <table class="table">
        <thead>
        <tr>
            <th></th>
            <th>{{ $t('Total') }}</th>
            <th v-for="phaseNo in enabledPhases" :key="`header${phaseNo}`">
                {{ $t('Phase {phaseNumber}', {phaseNumber: phaseNo}) }}
            </th>
        </tr>
        </thead>
        <tbody>
        <tr v-if="countersAvailable.includes('forwardActiveEnergy')">
            <th>{{ $t('Forward active energy') }} (kWh)</th>
            <td>{{ totalLog.fae_total.toFixed(5) }}</td>
            <td v-for="phaseNo in enabledPhases" :key="`header_fae_${phaseNo}`">{{ totalLog[`phase${phaseNo}_fae`].toFixed(5) }}</td>
        </tr>
        <tr v-if="countersAvailable.includes('reverseActiveEnergy')">
            <th>{{ $t('Reverse active energy') }} (kWh)</th>
            <td>{{ totalLog.rae_total.toFixed(5) }}</td>
            <td v-for="phaseNo in enabledPhases" :key="`header_rae_${phaseNo}`">{{ totalLog[`phase${phaseNo}_rae`].toFixed(5) }}</td>
        </tr>
        <tr v-if="countersAvailable.includes('forwardReactiveEnergy')">
            <th>{{ $t('Forward reactive energy') }} (kvarh)</th>
            <td>{{ (totalLog.phase1_fre + totalLog.phase2_fre + totalLog.phase3_fre).toFixed(5) }}</td>
            <td v-for="phaseNo in enabledPhases" :key="`header_fre_${phaseNo}`">{{ totalLog[`phase${phaseNo}_fre`].toFixed(5) }}</td>
        </tr>
        <tr v-if="countersAvailable.includes('reverseReactiveEnergy')">
            <th>{{ $t('Reverse reactive energy') }} (kvarh)</th>
            <td>{{ (totalLog.phase1_rre + totalLog.phase2_rre + totalLog.phase3_rre).toFixed(5) }}</td>
            <td v-for="phaseNo in enabledPhases" :key="`header_rre_${phaseNo}`">{{ totalLog[`phase${phaseNo}_rre`].toFixed(5) }}</td>
        </tr>
        </tbody>
    </table>
</template>

<script>
    import ChannelFunction from "@/common/enums/channel-function";
    import {CHART_TYPES} from "@/channels/history/channel-measurements-history-chart-strategies";

    export default {
        props: {
            channel: Object,
            logs: Array,
        },
        data() {
            return {
                ChannelFunction,
            };
        },
        computed: {
            enabledPhases() {
                return this.channel.config.enabledPhases || [1, 2, 3];
            },
            countersAvailable() {
                const defaultModes = ['forwardActiveEnergy', 'reverseActiveEnergy', 'forwardReactiveEnergy', 'reverseReactiveEnergy'];
                return this.channel.config.countersAvailable || defaultModes;
            },
            totalLog() {
                const log = CHART_TYPES.ELECTRICITYMETER.aggregateLogs(this.logs);
                CHART_TYPES.ELECTRICITYMETER.allAttributesArray().forEach(attributeName => {
                    if (!log[attributeName]) {
                        log[attributeName] = 0;
                    }
                })
                return log;
            },
        }
    };
</script>
