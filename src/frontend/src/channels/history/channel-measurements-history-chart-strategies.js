import {channelTitle} from "@/common/filters";
import {measurementUnit} from "@/channels/channel-helpers";

export function fillGaps(logs, expectedInterval, defaultLog) {
    if (logs.length < 2) {
        return logs;
    }
    expectedInterval /= 1000;
    let lastTimestamp = 0;
    const filledLogs = [];
    for (const log of logs) {
        const currentTimestamp = log.date_timestamp;
        if (lastTimestamp && (currentTimestamp - lastTimestamp) > expectedInterval * 1.5) {
            for (let missingTimestamp = lastTimestamp + expectedInterval; missingTimestamp < currentTimestamp; missingTimestamp += expectedInterval) {
                filledLogs.push({...defaultLog, date_timestamp: missingTimestamp});
            }
        }
        filledLogs.push(log);
        lastTimestamp = currentTimestamp;
    }
    return filledLogs;
}

export const CHART_TYPES = {
    THERMOMETER: {
        chartType: 'line',
        chartOptions: () => ({}),
        series: function (allLogs) {
            const temperatureSeries = allLogs.map((item) => [item.date_timestamp * 1000, item.temperature]);
            return [{name: `${channelTitle(this.channel, this)} (${this.$t('Temperature')})`, data: temperatureSeries}];
        },
        fixLog: (log) => {
            if (log.temperature !== undefined && log.temperature !== null) {
                log.temperature = log.temperature >= -273 ? +log.temperature : null;
            }
            return log;
        },
        adjustLogs: (logs) => logs,
        interpolateGaps: (logs) => logs,
        yaxes: function (logs) {
            const temperatures = logs.map(log => log.temperature).filter(t => t !== null);
            return [
                {
                    seriesName: `${channelTitle(this.channel, this)} (${this.$t('Temperature')})`,
                    title: {text: this.$t("Temperature")},
                    labels: {formatter: (v) => `${(+v).toFixed(2)}°C`},
                    min: Math.floor(Math.min.apply(this, temperatures)),
                    max: Math.ceil(Math.max.apply(this, temperatures)),
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, temperature: null}),
    },
    HUMIDITYANDTEMPERATURE: {
        chartType: 'line',
        chartOptions: () => ({}),
        series: function (allLogs) {
            const temperatureSeries = allLogs.map((item) => [item.date_timestamp * 1000, item.temperature]);
            const humiditySeries = allLogs.map((item) => [item.date_timestamp * 1000, item.humidity]);
            return [
                {name: `${channelTitle(this.channel, this)} (${this.$t('Temperature')})`, data: temperatureSeries},
                {name: `${channelTitle(this.channel, this)} (${this.$t('Humidity')})`, data: humiditySeries},
            ];
        },
        fixLog: (log) => {
            if (log.temperature !== undefined && log.temperature !== null) {
                log.temperature = log.temperature >= -273 ? +log.temperature : null;
            }
            if (log.humidity !== undefined && log.humidity !== null) {
                log.humidity = log.humidity >= 0 ? +log.humidity : null;
            }
            return log;
        },
        adjustLogs: (logs) => logs,
        interpolateGaps: (logs) => logs,
        yaxes: function (logs) {
            const temperatures = logs.map(log => log.temperature).filter(t => t !== null);
            const humidities = logs.map(log => log.humidity).filter(h => h !== null);
            return [
                {
                    seriesName: `${channelTitle(this.channel, this)} (${this.$t('Temperature')})`,
                    title: {text: this.$t("Temperature")},
                    labels: {formatter: (v) => `${(+v).toFixed(2)}°C`},
                    min: Math.floor(Math.min.apply(this, temperatures)),
                    max: Math.ceil(Math.max.apply(this, temperatures)),
                },
                {
                    seriesName: `${channelTitle(this.channel, this)} (${this.$t('Humidity')})`,
                    opposite: true,
                    title: {text: this.$t('Humidity')},
                    labels: {formatter: (v) => `${(+v).toFixed(1)}%`},
                    min: Math.floor(Math.max(0, Math.min.apply(this, humidities))),
                    max: Math.ceil(Math.min(100, Math.max.apply(this, humidities) + 1)),
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, temperature: null, humidity: null}),
    },
    HUMIDITY: {
        chartType: 'line',
        chartOptions: () => ({}),
        series: function (allLogs) {
            const humiditySeries = allLogs.map((item) => [item.date_timestamp * 1000, item.humidity]);
            return [
                {name: `${channelTitle(this.channel, this)} (${this.$t('Humidity')})`, data: humiditySeries},
            ];
        },
        fixLog: (log) => {
            if (log.humidity !== undefined && log.humidity !== null) {
                log.humidity = log.humidity >= 0 ? +log.humidity : null;
            }
            return log;
        },
        adjustLogs: (logs) => logs,
        interpolateGaps: (logs) => logs,
        yaxes: function (logs) {
            const humidities = logs.map(log => log.humidity).filter(h => h !== null);
            return [
                {
                    seriesName: `${channelTitle(this.channel, this)} (${this.$t('Humidity')})`,
                    opposite: true,
                    title: {text: this.$t('Humidity')},
                    labels: {formatter: (v) => `${(+v).toFixed(1)}%`},
                    min: Math.floor(Math.max(0, Math.min.apply(this, humidities))),
                    max: Math.ceil(Math.min(100, Math.max.apply(this, humidities) + 1)),
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, temperature: null, humidity: null}),
    },
    IC_GASMETER: {
        chartType: 'bar',
        chartOptions: () => ({}),
        series: function (allLogs) {
            const calculatedValues = allLogs.map((item) => [item.date_timestamp * 1000, item.calculated_value]);
            return [{name: `${channelTitle(this.channel, this)} (${this.$t('Calculated value')})`, data: calculatedValues}];
        },
        fixLog: (log) => {
            if (log.calculated_value !== undefined && log.calculated_value !== null) {
                log.calculated_value = +log.calculated_value;
            }
            return log;
        },
        adjustLogs: (logs) => {
            let previousLog = logs[0];
            const adjustedLogs = [];
            for (let i = 1; i < logs.length; i++) {
                const log = {...logs[i]};
                log.calculated_value -= previousLog.calculated_value;
                log.counter -= previousLog.counter;
                adjustedLogs.push(log);
                previousLog = logs[i];
            }
            return adjustedLogs;
        },
        interpolateGaps: (logs) => {
            let firstNullLog = undefined;
            let lastNonNullLog = undefined;
            for (let currentNonNullLog = 0; currentNonNullLog < logs.length; currentNonNullLog++) {
                const currentValue = logs[currentNonNullLog].calculated_value;
                if (currentValue === null && firstNullLog === undefined) {
                    firstNullLog = currentNonNullLog;
                } else if (currentValue !== null && firstNullLog !== undefined && lastNonNullLog !== undefined) {
                    const logsToFill = currentNonNullLog - firstNullLog;
                    const lastKnownValue = logs[lastNonNullLog].calculated_value;
                    const normalizedStep = (currentValue - lastKnownValue) / (logsToFill + 1);
                    for (let i = 0; i < logsToFill; i++) {
                        logs[i + firstNullLog].calculated_value = lastKnownValue + normalizedStep * (i + 1);
                        // logs[i].interpolated = true; may be useful
                    }
                    firstNullLog = undefined;
                }
                if (currentValue !== null) {
                    lastNonNullLog = currentNonNullLog;
                }
            }
            return logs;
        },
        yaxes: function (logs) {
            const values = this.adjustLogs(logs).map(log => log.calculated_value).filter(t => t !== null);
            const maxMeasurement = Math.max.apply(this, values);
            return [
                {
                    seriesName: `${channelTitle(this.channel, this)} (${this.$t('Calculated value')})`,
                    title: {text: this.$t("Calculated value")},
                    labels: {formatter: (v) => `${(+v).toFixed(2)} ${measurementUnit(this.channel)}`},
                    min: 0,
                    max: maxMeasurement + Math.min(0.1, maxMeasurement * 0.05),
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, counter: null, calculated_value: null}),
    },
    ELECTRICITYMETER: {
        chartType: 'bar',
        allAttributesArray() {
            return ['fae', 'rae', 'fre', 'rre'].map((suffix) => {
                return [1, 2, 3].map(phaseNo => `phase${phaseNo}_${suffix}`);
            }).flat();
        },
        chartOptions: () => ({
            chart: {stacked: true},
        }),
        series: function (allLogs) {
            const enabledPhases = this.channel.config.enabledPhases || [];
            return enabledPhases.map((phaseNo) => {
                // i18n: ['Phase 1', 'Phase 2', 'Phase 3']
                const phaseLabel = `Phase ${phaseNo}`;
                return {
                    name: `${channelTitle(this.channel, this)} (${this.$t(phaseLabel)})`,
                    data: allLogs.map((item) => [item.date_timestamp * 1000, item[`phase${phaseNo}_${this.chartMode}`]]),
                };
            });
        },
        fixLog: (log) => {
            CHART_TYPES.ELECTRICITYMETER.allAttributesArray().forEach((attributeName) => {
                if (log[attributeName] !== undefined && log[attributeName] !== null) {
                    log[attributeName] = +(+log[attributeName] * 0.00001).toFixed(5);
                }
            });
            return log;
        },
        adjustLogs: (logs) => {
            if (!logs || logs.length < 2) {
                return logs;
            }
            let previousLog = logs[0];
            const adjustedLogs = [];
            for (let i = 1; i < logs.length; i++) {
                const log = {...logs[i]};
                CHART_TYPES.ELECTRICITYMETER.allAttributesArray().forEach((attributeName) => {
                    if (log[attributeName] === null) {
                        log[attributeName] = previousLog[attributeName];
                    }
                    log[attributeName] -= previousLog[attributeName] || log[attributeName];
                });
                adjustedLogs.push(log);
                previousLog = logs[i];
            }
            return adjustedLogs;
        },
        interpolateGaps: (logs) => {
            let firstNullLog = undefined;
            let lastNonNullLog = undefined;
            for (let currentNonNullLog = 0; currentNonNullLog < logs.length; currentNonNullLog++) {
                const currentValue = logs[currentNonNullLog].phase1_fae;
                if (currentValue === null && firstNullLog === undefined) {
                    firstNullLog = currentNonNullLog;
                } else if (currentValue !== null && firstNullLog !== undefined && lastNonNullLog !== undefined) {
                    CHART_TYPES.ELECTRICITYMETER.allAttributesArray().forEach((attribute) => {
                        const currentValue = logs[currentNonNullLog][attribute];
                        const logsToFill = currentNonNullLog - firstNullLog;
                        const lastKnownValue = logs[lastNonNullLog][attribute];
                        const normalizedStep = (currentValue - lastKnownValue) / (logsToFill + 1);
                        for (let i = 0; i < logsToFill; i++) {
                            logs[i + firstNullLog][attribute] = lastKnownValue + normalizedStep * (i + 1);
                            // logs[i].interpolated = true; may be useful
                        }
                    });
                    firstNullLog = undefined;
                }
                if (currentValue !== null) {
                    lastNonNullLog = currentNonNullLog;
                }
            }
            return logs;
        },
        yaxes: function (logs) {
            const values = CHART_TYPES.ELECTRICITYMETER.adjustLogs(logs)
                .map(log => log['phase1_' + this.chartMode] + log['phase2_' + this.chartMode] + log['phase3_' + this.chartMode])
                .filter(t => t > 0);
            const maxMeasurement = Math.max.apply(this, values);
            let roundLevel = 1;
            while (roundLevel < 4 && maxMeasurement * Math.pow(10, roundLevel) < 1) {
                ++roundLevel;
            }
            let maxRounded = Math.ceil(maxMeasurement * Math.pow(10, roundLevel - 1)) / Math.pow(10, roundLevel - 1);
            if (maxRounded / 5 > maxMeasurement) {
                maxRounded /= 5;
            } else if (maxRounded / 2 > maxMeasurement) {
                maxRounded /= 2;
            }
            const label = {
                fae: this.$t("Forward active energy"),
                rae: this.$t("Reverse active energy"),
                fre: this.$t("Forward reactive energy"),
                rre: this.$t("Reverse reactive energy"),
            }[this.chartMode];
            return [
                {
                    seriesName: `${channelTitle(this.channel, this)} (${label})`,
                    title: {text: label},
                    labels: {formatter: (v) => `${(+v).toFixed(5)} ${measurementUnit(this.channel)}`},
                    min: 0,
                    max: maxRounded,
                }
            ];
        },
        emptyLog: () => ({
            date_timestamp: null,
            phase1_fae: null, phase2_fae: null, phase3_fae: null,
            phase1_rae: null, phase2_rae: null, phase3_rae: null,
            phase1_fre: null, phase2_fre: null, phase3_fre: null,
            phase1_rre: null, phase2_rre: null, phase3_rre: null,
        }),
    },
};

CHART_TYPES.IC_HEATMETER = CHART_TYPES.IC_GASMETER;
CHART_TYPES.IC_WATERMETER = CHART_TYPES.IC_GASMETER;
CHART_TYPES.IC_ELECTRICITYMETER = CHART_TYPES.IC_GASMETER;
