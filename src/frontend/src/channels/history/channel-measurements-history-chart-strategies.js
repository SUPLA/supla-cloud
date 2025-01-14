import {measurementUnit} from "@/channels/channel-helpers";
import {formatGpmValue} from "@/common/filters";
import {i18n} from "@/locale";

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
    forChannel(channel, logsType) {
        let strategyDescriptor = channel.function.name;
        if (['voltageHistory', 'currentHistory', 'powerActiveHistory'].includes(logsType)) {
            strategyDescriptor += logsType;
        }
        return this[strategyDescriptor];
    },
    THERMOMETER: {
        chartType: () => 'rangeArea',
        chartOptions() {
            return {
                fill: {opacity: [1, .25]},
                stroke: {curve: 'straight', width: [2, 0]},
                colors: ['#00d150', '#00d150'],
                legend: {show: false},
                tooltip: {
                    custom(ctx) {
                        const avg = ctx.w.globals.seriesRange[0][ctx.dataPointIndex].y[0].y1;
                        const format = (v) => ctx.w.config.yaxis[0].labels.formatter(v);
                        if (ctx.w.globals.seriesRange[1]) {
                            const min = ctx.w.globals.seriesRange[1][ctx.dataPointIndex].y[0].y1;
                            const max = ctx.w.globals.seriesRange[1][ctx.dataPointIndex].y[0].y2;
                            return `<div class="p-3">
                                 <strong>${i18n.global.t('Average')}:</strong> ${format(avg)}<br>
                                 <strong>${i18n.global.t('Min')}:</strong> ${format(min)}<br>
                                 <strong>${i18n.global.t('Max')}:</strong> ${format(max)}
                             </div>`;
                        } else {
                            return `<div class="p-3"> ${format(avg)}</div>`;
                        }
                    }
                },
            };
        },
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
                const rangeSeries = allLogs
                    .filter((item) => item.min !== null)
                    .map((item) => ({x: item.date_timestamp * 1000, y: [item.min, item.max]}));
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
            return {
                ...logs[0],
                temperature: isNaN(averageTemp) ? null : averageTemp,
                min: isNaN(averageTemp) ? null : Math.min.apply(null, temperatures),
                max: isNaN(averageTemp) ? null : Math.max.apply(null, temperatures),
            };
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
        chartType: () => 'rangeArea',
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
                humidity: isNaN(averageHumidity) ? null : averageHumidity,
                temperature: isNaN(averageTemperature) ? null : averageTemperature,
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
        chartType: () => 'rangeArea',
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
                const rangeSeries = allLogs
                    .filter((item) => item.min !== null)
                    .map((item) => ({x: item.date_timestamp * 1000, y: [item.min, item.max]}));
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
            return {
                ...logs[0],
                humidity: isNaN(averageHumidity) ? null : averageHumidity,
                min: isNaN(averageHumidity) ? null : Math.min.apply(null, humidities),
                max: isNaN(averageHumidity) ? null : Math.max.apply(null, humidities),
            };
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
        chartType: () => 'bar',
        chartOptions: () => ({
            legend: {show: false},
            tooltip: {followCursor: true, theme: 'no-series-label'},
        }),
        series: function (allLogs) {
            const calculatedValues = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item.calculated_value}));
            return [{name: this.$t('Value'), data: calculatedValues}];
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
                let skipThisLog = false;
                if (log.counter >= previousLog.counter * .9) { // .9 for reset misdetections
                    if (log.counter >= previousLog.counter) {
                        log.calculated_value -= previousLog.calculated_value;
                        log.counter -= previousLog.counter;
                    } else {
                        log.calculated_value = 0;
                        log.counter = 0;
                        skipThisLog = true;
                    }
                } else {
                    log.counterReset = true;
                }
                adjustedLogs.push(log);
                if (!skipThisLog) {
                    previousLog = logs[i];
                }
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
        yaxes() {
            return [
                {
                    seriesName: this.$t('Value'),
                    title: {text: this.$t('Value')},
                    labels: {formatter: (v) => v !== null ? `${(+v).toFixed(2)} ${measurementUnit(this.channel)}` : '?'},
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, counter: null, calculated_value: null}),
    },
    ELECTRICITYMETER: {
        chartType: () => 'bar',
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
                options.colors = ['#ff7373', '#aaa', '#00d150', '#aaa'];
                options.tooltip = {
                    custom(ctx) {
                        const value = ctx.series[0][ctx.dataPointIndex] || ctx.series[2][ctx.dataPointIndex];
                        const formatted = ctx.w.config.yaxis[0].labels.formatter(value);
                        return `<div class="p-3">${formatted}</div>`;
                    }
                };
                options.legend = {show: false};
            } else {
                options.legend = {show: true};
                options.tooltip = {custom: undefined};
                options.colors = ['#00d150', '#008ffb', '#ff851b'];
            }
            return options;
        },
        series: function (allLogs) {
            const enabledPhases = this.channel.config.enabledPhases || [1, 2, 3];
            if (this.chartMode === 'fae_rae') {
                const series = [
                    {name: `${this.$t('Forward active energy')}`, data: []},
                    {name: `${this.$t('Forward active energy')}`, data: []},
                    {name: `${this.$t('Reverse active energy')}`, data: []},
                    {name: `${this.$t('Reverse active energy')}`, data: []},
                ];
                allLogs.forEach((item) => {
                    const balance = item.fae_rae_balance;
                    series[0].data.push({x: item.date_timestamp * 1000, y: balance > 0 ? balance : 0});
                    series[1].data.push({x: item.date_timestamp * 1000, y: item.fae_total - (balance > 0 ? balance : 0)});
                    series[2].data.push({x: item.date_timestamp * 1000, y: balance < 0 ? balance : 0});
                    series[3].data.push({x: item.date_timestamp * 1000, y: -item.rae_total - (balance < 0 ? balance : 0)});
                });
                return series;
            } else if (this.chartMode === 'fae_rae_vector') {
                const series = [
                    {name: `${this.$t('Forward active energy balance')}`, data: []},
                    {name: `${this.$t('Forward active energy')}`, data: []},
                    {name: `${this.$t('Reverse active energy balance')}`, data: []},
                    {name: `${this.$t('Reverse active energy')}`, data: []},
                ];
                allLogs.forEach((item) => {
                    const balance = item.fae_balanced - item.rae_balanced;
                    series[0].data.push({x: item.date_timestamp * 1000, y: balance > 0 ? balance : 0});
                    series[1].data.push({x: item.date_timestamp * 1000, y: item.fae_balanced - (balance > 0 ? balance : 0)});
                    series[2].data.push({x: item.date_timestamp * 1000, y: balance < 0 ? balance : 0});
                    series[3].data.push({x: item.date_timestamp * 1000, y: -item.rae_balanced - (balance < 0 ? balance : 0)});
                });
                return series;
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
                    labels: {
                        formatter: (v, dataPoint) => {
                            if (v === null) {
                                return '?';
                            } else {
                                let label = `${(+v).toFixed(5)} ${unit}`;
                                if (typeof dataPoint === 'object' && ['fae'].includes(this.chartMode)) {
                                    const {pricePerUnit, currency} = this.channel.config;
                                    if (pricePerUnit) {
                                        const cost = (v * pricePerUnit).toFixed(2);
                                        label += ` = ${cost} ${currency || 'PLN'}`;
                                    }
                                }
                                return label;
                            }
                        }
                    },
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
                    if (Number.isFinite(log[attributeName])) {
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
                            log[attributeName] = 0;
                        } else {
                            const newState = log[attributeName];
                            log[attributeName] -= latestState[attributeName] || 0;
                            let skipThisLog = false;
                            if (log[attributeName] < -latestState[attributeName] * .1) {
                                log[attributeName] += latestState[attributeName];
                                log.counterReset = true;
                            } else if (log[attributeName] < 0) {
                                log[attributeName] = 0;
                                skipThisLog = true;
                            }
                            if (!skipThisLog) {
                                latestState[attributeName] = newState;
                            }
                        }
                    } else {
                        log[attributeName] = 0;
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
            fae_total: 0, rae_total: 0, fae_rae_balance: 0,
            fae_balanced: null, rae_balanced: null,
        }),
    },
    'ELECTRICITYMETERvoltageHistory': {
        chartType: () => 'line',
        chartOptions() {
            const enabledPhases = this.channel.config.enabledPhases || [1, 2, 3];
            return {
                stroke: {
                    curve: 'straight',
                    width: 2,
                    dashArray: [0, 2, 2, 0, 2, 2, 0, 2, 2]
                },
                tooltip: {
                    custom(ctx) {
                        const format = (v) => ctx.w.config.yaxis[0].labels.formatter(v);
                        let tooltip = '<div class="p-3">';
                        enabledPhases.forEach((phaseNo, idx) => {
                            const avg = ctx.w.globals.series[idx * 3][ctx.dataPointIndex];
                            const min = ctx.w.globals.series[idx * 3 + 1][ctx.dataPointIndex];
                            const max = ctx.w.globals.series[idx * 3 + 2][ctx.dataPointIndex];
                            const phaseLabel = `Phase ${phaseNo}`;
                            tooltip += `<div>
                                <strong>${i18n.global.t(phaseLabel)}:</strong>
                                ${i18n.global.t('Average')}: ${format(avg)},
                                ${i18n.global.t('Min')}: ${format(min)},
                                ${i18n.global.t('Max')}: ${format(max)}
                            </div>`;
                        })
                        tooltip += '</div>';
                        return tooltip;
                    }
                },
            };
        },
        series: function (allLogs) {
            const enabledPhases = this.channel.config.enabledPhases || [1, 2, 3];
            const series = [];
            const colors = ['#00d150', '#008ffb', '#ff851b'];
            enabledPhases.forEach((phaseNo) => {
                // i18n: ['Phase 1', 'Phase 2', 'Phase 3']
                const phaseLabel = `Phase ${phaseNo}`;
                series.push({
                    name: `${this.$t(phaseLabel)} (${this.$t('average')})`,
                    color: colors[phaseNo - 1],
                    data: allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item[`phase${phaseNo}_avg`]})),
                });
                series.push({
                    name: `${this.$t(phaseLabel)} (${this.$t('min')})`,
                    color: colors[phaseNo - 1],
                    data: allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item[`phase${phaseNo}_min`]})),
                });
                series.push({
                    name: `${this.$t(phaseLabel)} (${this.$t('max')})`,
                    color: colors[phaseNo - 1],
                    data: allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item[`phase${phaseNo}_max`]})),
                });
            });
            return series;
        },
        mergeLogsWithTheSameTimestamp: (logs) => {
            const timestamps = {};
            const mergedLogs = [];
            logs.forEach(log => {
                if (timestamps[log.date_timestamp] === undefined) {
                    timestamps[log.date_timestamp] = mergedLogs.length;
                    mergedLogs.push({date_timestamp: log.date_timestamp});
                }
                mergedLogs[timestamps[log.date_timestamp]][`phase${log.phaseNo}_min`] = log.min;
                mergedLogs[timestamps[log.date_timestamp]][`phase${log.phaseNo}_max`] = log.max;
                mergedLogs[timestamps[log.date_timestamp]][`phase${log.phaseNo}_avg`] = log.avg;
            });
            return mergedLogs;
        },
        fixLog: (log) => log,
        adjustLogs: (logs) => logs,
        interpolateGaps: (logs) => logs,
        aggregateLogs: (logs) => {
            const aggregatedLog = {...logs[0]};
            [1, 2, 3].map(phaseNo => {
                const avgs = logs.map(log => log[`phase${phaseNo}_avg`]).filter(t => t || t === 0);
                const average = avgs.reduce((a, b) => a + b, 0) / avgs.length;
                if (!isNaN(average)) {
                    const mins = logs.map(log => log[`phase${phaseNo}_min`]).filter(t => t || t === 0);
                    const maxs = logs.map(log => log[`phase${phaseNo}_max`]).filter(t => t || t === 0);
                    aggregatedLog[`phase${phaseNo}_avg`] = average;
                    aggregatedLog[`phase${phaseNo}_min`] = Math.min.apply(null, mins);
                    aggregatedLog[`phase${phaseNo}_max`] = Math.max.apply(null, maxs);
                }
            });
            return aggregatedLog;
        },
        yaxes: function () {
            return [
                {
                    seriesName: this.$t('Voltage'),
                    title: {text: this.$t("Voltage")},
                    labels: {formatter: (v) => !isNaN(v) ? `${(+v).toFixed(2)} V` : '?'},
                }
            ];
        },
        emptyLog: () => ({
            date_timestamp: null,
            phase1_min: null, phase1_max: null, phase1_avg: null,
            phase2_min: null, phase2_max: null, phase2_avg: null,
            phase3_min: null, phase3_max: null, phase3_avg: null,
        }),
    },
    GENERAL_PURPOSE_MEASUREMENT: {
        chartType: (channel) => ({'CANDLE': 'candlestick', 'LINEAR': 'rangeArea'}[channel?.config?.chartType || ''] || 'bar'),
        chartOptions() {
            const options = {
                fill: {opacity: [1, .25]},
                stroke: {curve: 'straight', width: [2, 0]},
                colors: ['#00d150', '#00d150'],
                legend: {show: false},
            };
            if (this.channel?.config?.chartType === 'CANDLE') {
                options.tooltip = {
                    custom(ctx) {
                        const o = ctx.w.globals.seriesCandleO[0][ctx.dataPointIndex];
                        const h = ctx.w.globals.seriesCandleH[0][ctx.dataPointIndex];
                        const l = ctx.w.globals.seriesCandleL[0][ctx.dataPointIndex];
                        const c = ctx.w.globals.seriesCandleC[0][ctx.dataPointIndex];
                        const format = (v) => ctx.w.config.yaxis[0].labels.formatter(v);
                        return `<div class="p-3">
                             <strong>${i18n.global.t('Open value')}:</strong> ${format(o)}<br>
                             <strong>${i18n.global.t('High value')}:</strong> ${format(h)}<br>
                             <strong>${i18n.global.t('Low value')}:</strong> ${format(l)}<br>
                             <strong>${i18n.global.t('Close value')}:</strong> ${format(c)}
                         </div>`;
                    }
                };
            }
            if (this.channel?.config?.chartType === 'LINEAR') {
                options.tooltip = {
                    custom(ctx) {
                        const avg = ctx.w.globals.seriesRange[0][ctx.dataPointIndex].y[0].y1;
                        const min = ctx.w.globals.seriesRange[1][ctx.dataPointIndex].y[0].y1;
                        const max = ctx.w.globals.seriesRange[1][ctx.dataPointIndex].y[0].y2;
                        const format = (v) => ctx.w.config.yaxis[0].labels.formatter(v);
                        return `<div class="p-3">
                             <strong>${i18n.global.t('Average')}:</strong> ${format(avg)}<br>
                             <strong>${i18n.global.t('Min')}:</strong> ${format(min)}<br>
                             <strong>${i18n.global.t('Max')}:</strong> ${format(max)}
                         </div>`;
                    }
                };
            }
            return options;
        },
        series: function (allLogs) {
            let baseSeries;
            if (this.channel?.config?.chartType === 'CANDLE') {
                baseSeries = allLogs.map((item) => ({
                    x: item.date_timestamp * 1000,
                    y: [item.open_value, item.max_value, item.min_value, item.close_value],
                }));
            } else {
                baseSeries = allLogs.map((item) => ({
                    x: item.date_timestamp * 1000,
                    y: item.avg_value,
                }));
            }
            const series = [
                {
                    name: `${this.$t('Value')}`,
                    data: baseSeries,
                },
            ];
            if (this.channel?.config?.chartType === 'LINEAR') {
                const rangeSeries = allLogs
                    .filter((item) => item.min !== null)
                    .map((item) => ({x: item.date_timestamp * 1000, y: [item.min_value, item.max_value]}))
                    // TODO remove filter after fix https://github.com/apexcharts/apexcharts.js/issues/3821
                    // it should correctly display ranges on null logs
                    .filter(item => item.y[0] !== null);
                series.push({
                    name: `${this.$t('Value')} - ${this.$t('range')}`,
                    type: 'rangeArea',
                    data: rangeSeries
                });
                series[0].name = `${this.$t('Value')} - ${this.$t('average')}`;
            }
            return series;
        },
        fixLog: (log) => log,
        adjustLogs: (logs) => logs,
        interpolateGaps: (logs) => logs,
        aggregateLogs: (logs) => {
            const avgs = logs.map(log => log.avg_value).filter(t => t || t === 0);
            const average = avgs.reduce((a, b) => a + b, 0) / avgs.length;
            const mins = logs.map(log => log.min_value).filter(t => t || t === 0);
            const maxs = logs.map(log => log.max_value).filter(t => t || t === 0);
            const opens = logs.map(log => log.open_value).filter(t => t || t === 0);
            const closes = logs.map(log => log.close_value).filter(t => t || t === 0);
            return {
                ...logs[0],
                avg_value: isNaN(average) ? null : average,
                min_value: isNaN(average) ? null : Math.min.apply(null, mins),
                max_value: isNaN(average) ? null : Math.max.apply(null, maxs),
                open_value: isNaN(average) ? null : opens[0],
                close_value: isNaN(average) ? null : closes.pop(),
            };
        },
        yaxes() {
            return [
                {
                    seriesName: this.$t('Value'),
                    title: {text: this.$t("Value")},
                    labels: {formatter: (v) => v !== null ? formatGpmValue(v, this.channel.config) : '?'},
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, avg_value: null, open_value: null, close_value: null, max_value: null, min_value: null}),
    },
    GENERAL_PURPOSE_METER: {
        chartType: (channel) => channel?.config?.chartType === 'LINEAR' ? 'line' : 'bar',
        chartOptions: () => ({
            legend: {show: false},
            colors: ['#01c2fb'],
            tooltip: {followCursor: true, theme: 'no-series-label'},
        }),
        series: function (allLogs) {
            const calculatedValues = allLogs.map((item) => ({x: item.date_timestamp * 1000, y: item.counterReset ? null : item.value}));
            return [{name: this.$t('Value'), data: calculatedValues}];
        },
        fixLog: (log) => {
            if (log.value !== undefined && log.value !== null) {
                log.value = +(+log.value).toFixed(5);
            }
            return log;
        },
        aggregateLogs: (logs) => {
            const aggregatedLog = {
                ...(CHART_TYPES.GENERAL_PURPOSE_METER.emptyLog()),
                date_timestamp: logs[0].date_timestamp,
                date: logs[0].date
            };
            logs.forEach(log => {
                if (log.value) {
                    aggregatedLog.value += log.value;
                    if (log.counterReset) {
                        aggregatedLog.counterReset = true;
                    }
                }
            });
            return aggregatedLog;
        },
        adjustLogs: (logs, channel) => {
            let previousLog = logs[0];
            const adjustedLogs = [logs[0]];
            const counterType = channel.config.counterType;
            for (let i = 1; i < logs.length; i++) {
                const log = {...logs[i]};
                if (counterType === 'ALWAYS_INCREMENT') {
                    if (log.value >= previousLog.value - Math.abs(0.1 * previousLog.value)) { // 0.1 for reset misdetections
                        if (log.value >= previousLog.value) {
                            log.value -= previousLog.value;
                        } else {
                            log.value = 0;
                        }
                    } else {
                        log.counterReset = true;
                    }
                } else if (counterType === 'ALWAYS_DECREMENT') {
                    if (log.value <= previousLog.value + Math.abs(0.1 * previousLog.value)) { // 0.1 for reset misdetections
                        if (log.value <= previousLog.value) {
                            log.value -= previousLog.value;
                        } else {
                            log.value = 0;
                        }
                    } else {
                        log.counterReset = true;
                    }
                } else {
                    log.value -= previousLog.value;
                }
                adjustedLogs.push(log);
                previousLog = logs[i];
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
        interpolateGaps: (logs, channel) => {
            let firstNullLog = undefined;
            let lastNonNullLog = undefined;
            for (let currentNonNullLog = 0; currentNonNullLog < logs.length; currentNonNullLog++) {
                const currentValue = logs[currentNonNullLog].value;
                if (currentValue === null && firstNullLog === undefined) {
                    firstNullLog = currentNonNullLog;
                } else if (currentValue !== null && firstNullLog !== undefined && lastNonNullLog !== undefined) {
                    const logsToFill = currentNonNullLog - firstNullLog;
                    const lastKnownValue = logs[lastNonNullLog].value;
                    const normalizedStep = channel.config.fillMissingData ? (currentValue - lastKnownValue) / (logsToFill + 1) : 0;
                    for (let i = 0; i < logsToFill; i++) {
                        logs[i + firstNullLog].value = lastKnownValue + normalizedStep * (i + 1);
                    }
                    firstNullLog = undefined;
                }
                if (currentValue !== null) {
                    lastNonNullLog = currentNonNullLog;
                }
            }
            return logs;
        },
        yaxes() {
            return [
                {
                    seriesName: this.$t('Value'),
                    title: {text: this.$t('Value')},
                    labels: {formatter: (v) => v !== null ? formatGpmValue(v, this.channel.config) : '?'},
                }
            ];
        },
        emptyLog: () => ({date_timestamp: null, value: null}),
    },
};

CHART_TYPES.IC_HEATMETER = CHART_TYPES.IC_GASMETER;
CHART_TYPES.IC_WATERMETER = CHART_TYPES.IC_GASMETER;
CHART_TYPES.IC_ELECTRICITYMETER = CHART_TYPES.IC_GASMETER;
CHART_TYPES['ELECTRICITYMETERcurrentHistory'] = {
    ...CHART_TYPES.ELECTRICITYMETERvoltageHistory,
    yaxes: function () {
        return [
            {
                seriesName: this.$t('Current'),
                title: {text: this.$t("Current")},
                labels: {formatter: (v) => !isNaN(v) ? `${(+v).toFixed(2)} A` : '?'},
            }
        ];
    },
};
CHART_TYPES['ELECTRICITYMETERpowerActiveHistory'] = {
    ...CHART_TYPES.ELECTRICITYMETERvoltageHistory,
    yaxes: function () {
        return [
            {
                seriesName: this.$t('Power active'),
                title: {text: this.$t("Power active")},
                labels: {formatter: (v) => !isNaN(v) ? `${(+v).toFixed(2)} W` : '?'},
            }
        ];
    },
};
