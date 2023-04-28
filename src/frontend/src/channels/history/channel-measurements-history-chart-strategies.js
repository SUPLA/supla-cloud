import {measurementUnit} from "@/channels/channel-helpers";

export function fillGaps(logs, expectedInterval, defaultLog) {
    if (logs.length < 2) {
        return logs;
    }
    let lastTimestamp = 0;
    const filledLogs = [];
    for (const log of logs) {
        const currentTimestamp = log.date_timestamp;
        if (lastTimestamp && (currentTimestamp - lastTimestamp) > expectedInterval * 1.5) {
            for (let missingTimestamp = lastTimestamp + expectedInterval; missingTimestamp < currentTimestamp; missingTimestamp += expectedInterval) {
                filledLogs.push({...defaultLog, date_timestamp: missingTimestamp, interpolated: true});
            }
        }
        filledLogs.push(log);
        lastTimestamp = currentTimestamp;
    }
    return filledLogs;
}

export const CHART_TYPES = {
    THERMOMETER: {
        chartType: 'rangeArea',
        chartOptions: () => ({
            fill: {opacity: [1, .25]},
            stroke: {curve: 'straight', width: [2, 0]},
            colors: ['#00d150', '#00d150'],
            legend: {show: false}
        }),
        series: function (allLogs) {
            const temperatureSeries = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item.temperature}));
            const series = [
                {
                    name: `${this.$t('Temperature')}`,
                    type: 'line',
                    data: temperatureSeries
                },
            ];
            if (allLogs[0].min !== undefined) {
                const rangeSeries = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: [item.min, item.max]}));
                series.push({
                    name: `${this.$t('Temperature')} - ${this.$t('range')}`,
                    type: 'rangeArea',
                    data: rangeSeries
                });
                series[0].name = `${this.$t('Temperature')} - ${this.$t('average')}`;
            }
            return series;
        },
        fixLog: (log) => {
            if (log.temperature !== undefined && log.temperature !== null) {
                log.temperature = log.temperature >= -273 ? +log.temperature : null;
            }
            return log;
        },
        adjustLogs: (logs) => logs,
        interpolateGaps: (logs) => logs,
        aggregateLogs: (logs) => {
            const temperatures = logs.map(log => log.temperature).filter(t => t || t === 0);
            const averageTemp = temperatures.reduce((a, b) => a + b, 0) / temperatures.length;
            return {...logs[0], temperature: averageTemp, min: Math.min.apply(null, temperatures), max: Math.max.apply(null, temperatures)};
        },
        yaxes: function () {
            return [
                {
                    seriesName: this.$t('Temperature'),
                    title: {text: this.$t("Temperature")},
                    labels: {formatter: (v) => v !== null ? `${(+v).toFixed(2)}°C` : '?'},
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, temperature: null}),
    },
    HUMIDITYANDTEMPERATURE: {
        chartType: 'rangeArea',
        chartOptions() {
            return {
                fill: {opacity: [1, 1, .25, .25]},
                stroke: {curve: 'straight', width: [2, 2, 0, 0]},
                colors: ['#00d150', '#008ffb', '#00d150', '#008ffb'],
                legend: {customLegendItems: [this.$t('Temperature'), this.$t('Humidity')]}
            }
        },
        series: function (allLogs) {
            const temperatureSeries = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item.temperature}));
            const humiditySeries = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item.humidity}));
            const series = [
                {name: `${this.$t('Temperature')}`, type: 'line', data: temperatureSeries},
                {name: `${this.$t('Humidity')}`, type: 'line', data: humiditySeries},
            ];
            // if (allLogs[0].minTemperature !== undefined) {
            //     const tempRange = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: [item.minTemperature, item.maxTemperature]}));
            //     series.push({
            //         name: `${channelTitle(this.channel, this)} (${this.$t('Temperature')} - ${this.$t('range')})`,
            //         type: 'rangeArea',
            //         data: tempRange
            //     });
            //     series[0].name = `${channelTitle(this.channel, this)} (${this.$t('Temperature')} - ${this.$t('average')})`;
            //     const humRange = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: [item.minHumidity, item.maxHumidity]}));
            //     series.push({
            //         name: `${channelTitle(this.channel, this)} (${this.$t('Humidity')} - ${this.$t('range')})`,
            //         type: 'rangeArea',
            //         data: humRange
            //     });
            //     series[1].name = `${channelTitle(this.channel, this)} (${this.$t('Humidity')} - ${this.$t('average')})`;
            // }
            return series;
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
        aggregateLogs: (logs) => {
            const humidities = logs.map(log => log.humidity).filter(t => t || t === 0);
            const temperatures = logs.map(log => log.temperature).filter(t => t || t === 0);
            const averageHumidity = humidities.reduce((a, b) => a + b, 0) / humidities.length;
            const averageTemperature = temperatures.reduce((a, b) => a + b, 0) / temperatures.length;
            return {
                ...logs[0],
                humidity: averageHumidity,
                temperature: averageTemperature,
                minHumidity: Math.min.apply(null, humidities),
                maxHumidity: Math.max.apply(null, humidities),
                minTemperature: Math.min.apply(null, temperatures),
                maxTemperature: Math.max.apply(null, temperatures)
            };
        },
        yaxes: function () {
            return [
                {
                    seriesName: `${this.$t('Temperature')}`,
                    title: {text: this.$t("Temperature")},
                    labels: {formatter: (v) => v !== null ? `${(+v).toFixed(2)}°C` : '?'},
                },
                {
                    seriesName: this.$t('Humidity'),
                    opposite: true,
                    title: {text: this.$t('Humidity')},
                    labels: {formatter: (v) => v !== null ? `${(+v).toFixed(1)}%` : '?'},
                    min: 0,
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, temperature: null, humidity: null}),
    },
    HUMIDITY: {
        chartType: 'rangeArea',
        chartOptions: () => ({
            fill: {opacity: [1, .25]},
            stroke: {curve: 'straight', width: [2, 0]},
            colors: ['#008ffb', '#008ffb'],
            legend: {show: false}
        }),
        series: function (allLogs) {
            const humiditySeries = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item.humidity}));
            const series = [
                {name: `${this.$t('Humidity')}`, type: 'line', data: humiditySeries},
            ];
            if (allLogs[0].min !== undefined) {
                const rangeSeries = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: [item.min, item.max]}));
                series.push({
                    name: `${this.$t('Humidity')} - ${this.$t('range')}`,
                    type: 'rangeArea',
                    data: rangeSeries
                });
                series[0].name = `${this.$t('Humidity')} - ${this.$t('average')}`;
            }
            return series;
        },
        fixLog: (log) => {
            if (log.humidity !== undefined && log.humidity !== null) {
                log.humidity = log.humidity >= 0 ? +log.humidity : null;
            }
            return log;
        },
        adjustLogs: (logs) => logs,
        interpolateGaps: (logs) => logs,
        aggregateLogs: (logs) => {
            const humidities = logs.map(log => log.humidity).filter(t => t || t === 0);
            const averageHumidity = humidities.reduce((a, b) => a + b, 0) / humidities.length;
            return {...logs[0], humidity: averageHumidity, min: Math.min.apply(null, humidities), max: Math.max.apply(null, humidities)};
        },
        yaxes: function () {
            return [
                {
                    seriesName: this.$t('Humidity'),
                    opposite: true,
                    title: {text: this.$t('Humidity')},
                    labels: {formatter: (v) => v !== null ? `${(+v).toFixed(1)}%` : '?'},
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, temperature: null, humidity: null}),
    },
    IC_GASMETER: {
        chartType: 'bar',
        chartOptions: () => ({}),
        series: function (allLogs) {
            const calculatedValues = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item.calculated_value}));
            return [{name: this.$t('Calculated value'), data: calculatedValues}];
        },
        fixLog: (log) => {
            if (log.calculated_value !== undefined && log.calculated_value !== null) {
                log.calculated_value = +(+log.calculated_value).toFixed(5);
            }
            return log;
        },
        aggregateLogs: (logs) => {
            const aggregatedLog = {
                ...(CHART_TYPES.IC_GASMETER.emptyLog()),
                date_timestamp: logs[0].date_timestamp,
                date: logs[0].date
            };
            logs.forEach(log => {
                if (log.counter) {
                    aggregatedLog.counter += log.counter;
                    aggregatedLog.calculated_value += log.calculated_value;
                    if (log.counterReset) {
                        aggregatedLog.counterReset = true;
                    }
                }
            });
            return aggregatedLog;
        },
        adjustLogs: (logs) => {
            let previousLog = logs[0];
            const adjustedLogs = [logs[0]];
            for (let i = 1; i < logs.length; i++) {
                const log = {...logs[i]};
                if (log.counter >= previousLog.counter) {
                    log.calculated_value -= previousLog.calculated_value;
                    log.counter -= previousLog.counter;
                } else {
                    log.counterReset = true;
                }
                adjustedLogs.push(log);
                previousLog = logs[i];
            }
            return adjustedLogs;
        },
        cumulateLogs: (logs) => {
            const adjustedLogs = [logs[0]];
            for (let i = 1; i < logs.length; i++) {
                let log = {...logs[i]};
                if (!log.counterReset && log.counter !== null) {
                    log.counter += adjustedLogs[i - 1].counter || 0;
                    log.calculated_value += adjustedLogs[i - 1].calculated_value || 0;
                }
                adjustedLogs.push(log);
            }
            return adjustedLogs;
        },
        getAnnotations: function (logs) {
            return logs.filter(log => log.counterReset).map(log => ({
                x: log.date_timestamp * 1000,
                borderColor: '#f00',
                label: {
                    borderColor: '#f00',
                    style: {color: '#f00',},
                    text: this.$t('Counter reset'),
                }
            }));
        },
        interpolateGaps: (logs) => {
            let firstNullLog = undefined;
            let lastNonNullLog = undefined;
            for (let currentNonNullLog = 0; currentNonNullLog < logs.length; currentNonNullLog++) {
                const currentValue = logs[currentNonNullLog].calculated_value;
                const currentCounter = logs[currentNonNullLog].counter;
                if (currentValue === null && firstNullLog === undefined) {
                    firstNullLog = currentNonNullLog;
                } else if (currentValue !== null && firstNullLog !== undefined && lastNonNullLog !== undefined) {
                    const logsToFill = currentNonNullLog - firstNullLog;
                    const lastKnownValue = logs[lastNonNullLog].calculated_value;
                    const lastKnownCounter = logs[lastNonNullLog].counter;
                    const normalizedStep = (currentValue - lastKnownValue) / (logsToFill + 1);
                    const normalizedCounter = (currentCounter - lastKnownCounter) / (logsToFill + 1);
                    if (normalizedCounter >= 0) {
                        for (let i = 0; i < logsToFill; i++) {
                            logs[i + firstNullLog].calculated_value = lastKnownValue + normalizedStep * (i + 1);
                            logs[i + firstNullLog].counter = Math.floor(lastKnownCounter + normalizedCounter * (i + 1));
                        }
                    }
                    firstNullLog = undefined;
                }
                if (currentValue !== null) {
                    lastNonNullLog = currentNonNullLog;
                }
            }
            return logs;
        },
        yaxes: function () {
            return [
                {
                    seriesName: this.$t('Calculated value'),
                    title: {text: this.$t("Calculated value")},
                    labels: {formatter: (v) => v !== null ? `${(+v).toFixed(2)} ${measurementUnit(this.channel)}` : '?'},
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, counter: null, calculated_value: null}),
    },
    ELECTRICITYMETER: {
        chartType: 'bar',
        allAttributesArray() {
            return ['fae', 'rae', 'fre', 'rre']
                .map((suffix) => {
                    return [1, 2, 3].map(phaseNo => `phase${phaseNo}_${suffix}`);
                })
                .concat(['fae_total', 'rae_total', 'fae_rae_balance'])
                .concat(['fae_balanced', 'rae_balanced'])
                .flat();
        },
        chartOptions() {
            const options = {chart: {stacked: true}};
            if (['fae_rae_vector', 'fae_rae'].includes(this.chartMode)) {
                options.colors = ['#ff7373', '#00d150'];
            } else {
                options.colors = ['#00d150', '#008ffb', '#ff851b'];
            }
            return options;
        },
        series: function (allLogs) {
            const enabledPhases = this.channel.config.enabledPhases || [1, 2, 3];
            if (this.chartMode === 'fae_rae') {
                return [
                    {
                        name: `${this.$t('Forward active energy')}`,
                        data: allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item.fae_total})),
                    },
                    {
                        name: `${this.$t('Reverse active energy')}`,
                        data: allLogs.map((item) => ({
                            x: item.date_timestamp * 1000, y: -item.rae_total, goals: [
                                {
                                    name: this.$t('Balance'),
                                    value: item.fae_rae_balance,
                                    strokeHeight: 6,
                                    strokeWidth: 0,
                                    strokeLineCap: 'round',
                                    strokeColor: item.fae_rae_balance < 0 ? '#005600' : '#f00',
                                }
                            ]
                        })),
                    },
                ];
            } else if (this.chartMode === 'fae_rae_vector') {
                return [
                    {
                        name: `${this.$t('Forward active energy (vector balance)')}`,
                        data: allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item.fae_balanced})),
                    },
                    {
                        name: `${this.$t('Reverse active energy (vector balance)')}`,
                        data: allLogs.map((item) => ({
                            x: item.date_timestamp * 1000, y: -item.rae_balanced, goals: [
                                {
                                    name: this.$t('Balance'),
                                    value: item.fae_balanced - item.rae_balanced,
                                    strokeHeight: 6,
                                    strokeWidth: 0,
                                    strokeLineCap: 'round',
                                    strokeColor: item.fae_balanced - item.rae_balanced < 0 ? '#005600' : '#f00',
                                }
                            ]
                        })),
                    },
                ]
            } else {
                return enabledPhases.map((phaseNo) => {
                    // i18n: ['Phase 1', 'Phase 2', 'Phase 3']
                    const phaseLabel = `Phase ${phaseNo}`;
                    return {
                        name: `${this.$t(phaseLabel)}`,
                        data: allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item[`phase${phaseNo}_${this.chartMode}`]})),
                    };
                });
            }
        },
        yaxes: function (logs) {
            const values = logs
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
            const unit = ['fre', 'rre'].includes(this.chartMode) ? 'kvarh' : 'kWh';
            return [
                {
                    seriesName: label,
                    title: {text: label},
                    labels: {formatter: (v) => v !== null ? `${(+v).toFixed(5)} ${unit}` : '?'},
                    // min: 0,
                    max: this.chartMode === 'fae_rae' ? undefined : maxRounded,
                }
            ];
        },
        fixLog: (log) => {
            CHART_TYPES.ELECTRICITYMETER.allAttributesArray().forEach((attributeName) => {
                if (log[attributeName] !== undefined && log[attributeName] !== null) {
                    log[attributeName] = +(+log[attributeName] * 0.00001).toFixed(5);
                }
            });
            log.fae_total = [1, 2, 3].map(phaseNo => log[`phase${phaseNo}_fae`]).reduce((a, b) => a + (b || 0), 0);
            log.rae_total = [1, 2, 3].map(phaseNo => log[`phase${phaseNo}_rae`]).reduce((a, b) => a + (b || 0), 0);
            log.fae_rae_balance = +(log.fae_total - log.rae_total).toFixed(5);
            return log;
        },
        aggregateLogs: (logs) => {
            const aggregatedLog = {
                ...(CHART_TYPES.ELECTRICITYMETER.emptyLog()),
                date_timestamp: logs[0].date_timestamp,
                date: logs[0].date
            };
            logs.forEach(log => {
                CHART_TYPES.ELECTRICITYMETER.allAttributesArray().forEach((attributeName) => {
                    if (log[attributeName]) {
                        aggregatedLog[attributeName] = +aggregatedLog[attributeName] + log[attributeName];
                    }
                    if (log.counterReset) {
                        aggregatedLog.counterReset = true;
                    }
                });
            });
            return aggregatedLog;
        },
        adjustLogs: (logs) => {
            if (!logs || logs.length < 2) {
                return logs;
            }
            let latestState = {...logs[0]};
            const adjustedLogs = [logs[0]];
            for (let i = 1; i < logs.length; i++) {
                let log = {...logs[i]};
                CHART_TYPES.ELECTRICITYMETER.allAttributesArray().forEach((attributeName) => {
                    if (Object.prototype.hasOwnProperty.call(log, attributeName)) {
                        if (log[attributeName] === null) {
                            if (latestState[attributeName] !== null) {
                                log[attributeName] = 0;
                            }
                        } else {
                            const newState = log[attributeName];
                            log[attributeName] -= latestState[attributeName] || 0;
                            if (log[attributeName] < 0) {
                                log[attributeName] += latestState[attributeName];
                                log.counterReset = true;
                            }
                            latestState[attributeName] = newState;
                        }
                    }
                });
                adjustedLogs.push(log);
            }
            return adjustedLogs;
        },
        cumulateLogs: (logs) => {
            const adjustedLogs = [logs[0]];
            for (let i = 1; i < logs.length; i++) {
                let log = {...logs[i]};
                CHART_TYPES.ELECTRICITYMETER.allAttributesArray().forEach((attributeName) => {
                    if (Object.prototype.hasOwnProperty.call(log, attributeName)) {
                        if (log[attributeName] === null) {
                            log[attributeName] = adjustedLogs[i - 1][attributeName];
                        }
                        if (!log.counterReset) {
                            log[attributeName] += adjustedLogs[i - 1][attributeName] || log[attributeName];
                        }
                    }
                });
                adjustedLogs.push(log);
            }
            return adjustedLogs;
        },
        getAnnotations: function (logs) {
            return logs.filter(log => log.counterReset).map(log => ({
                x: log.date_timestamp * 1000,
                borderColor: '#f00',
                label: {
                    borderColor: '#f00',
                    style: {color: '#f00',},
                    text: this.$t('Counter reset'),
                }
            }));
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
                        if (normalizedStep >= 0) {
                            for (let i = 0; i < logsToFill; i++) {
                                logs[i + firstNullLog][attribute] = lastKnownValue + normalizedStep * (i + 1);
                            }
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
        emptyLog: () => ({
            date_timestamp: null,
            phase1_fae: null, phase2_fae: null, phase3_fae: null,
            phase1_rae: null, phase2_rae: null, phase3_rae: null,
            phase1_fre: null, phase2_fre: null, phase3_fre: null,
            phase1_rre: null, phase2_rre: null, phase3_rre: null,
            fae_total: null, rae_total: null, fae_rae_balance: null,
            fae_balanced: null, rae_balanced: null,
        }),
    },
};

CHART_TYPES.IC_HEATMETER = CHART_TYPES.IC_GASMETER;
CHART_TYPES.IC_WATERMETER = CHART_TYPES.IC_GASMETER;
CHART_TYPES.IC_ELECTRICITYMETER = CHART_TYPES.IC_GASMETER;
