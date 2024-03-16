import {openDB} from "idb";
import {DateTime} from "luxon";
import {CHART_TYPES, fillGaps} from "@/channels/history/channel-measurements-history-chart-strategies";

export class IndexedDbMeasurementLogsStorage {
    constructor(channel, logsType) {
        this.channel = channel;
        this.logsType = logsType;
        this.chartStrategy = CHART_TYPES.forChannel(this.channel, logsType);
    }

    async connect() {
        if (window.indexedDB) {
            try {
                const dbName = [
                    'channel_measurement_logs',
                    this.channel.id,
                    this.getLogsType(),
                    this.channel.config?.counterType || 'default',
                    this.channel.config?.fillMissingData === false ? 'not_filled' : 'filled',
                ];
                this.db = await openDB(dbName.join('_'), 8, {
                    async upgrade(db) {
                        if (db.objectStoreNames.contains('logs')) {
                            await db.deleteObjectStore('logs');
                        }
                        if (db.objectStoreNames.contains('logs_raw')) {
                            await db.deleteObjectStore('logs_raw');
                        }
                        const logs = db.createObjectStore("logs", {keyPath: 'date_timestamp'});
                        logs.createIndex("date", "date", {unique: true});
                        const logsRaw = db.createObjectStore("logs_raw", {keyPath: 'date_timestamp'});
                        logsRaw.createIndex("date", "date", {unique: true});
                    }
                });
            } catch (e) {
                console.warn(e); // eslint-disable-line no-console
                this.db = undefined;
                this.hasSupport = false;
            }
        }
    }

    async checkSupport() {
        if (!this.db) {
            return this.hasSupport = false;
        }
        try {
            await (await this.db).count('logs');
            return this.hasSupport = true;
        } catch (e) {
            return this.hasSupport = false;
        }
    }

    adjustLogsBeforeStorage(logs) {
        logs = logs.map(log => {
            log.date_timestamp = +log.date_timestamp;
            log.date = DateTime.fromSeconds(log.date_timestamp).toJSDate();
            return log;
        });
        logs = this.chartStrategy.adjustLogs(logs, this.channel);
        return logs.map(log => this.chartStrategy.fixLog(log));
    }

    getLogsType() {
        const customLogsTypes = ['voltageHistory', 'currentHistory', 'powerActiveHistory'];
        if (customLogsTypes.includes(this.logsType)) {
            return this.logsType;
        } else {
            return 'default';
        }
    }

    getAvailableAggregationStrategies(timestampRange) {
        const strategies = [];
        const interval = this.getExpectedInterval();
        if (timestampRange < 86400 * interval / 90) {
            strategies.push('minute');
        }
        if (timestampRange > 3600 * 6 && timestampRange < 86400 * 21) {
            strategies.push('hour');
        }
        if (timestampRange > 86400 * 3 && timestampRange < 86400 * 400) {
            strategies.push('day');
        }
        if (timestampRange > 86400 * 40) {
            strategies.push('month');
        }
        return strategies;
    }

    async getNewestLog() {
        const index = (await this.db).transaction('logs').store.index('date');
        const cursor = await index.openCursor(null, 'prev');
        return cursor?.value;
    }

    async getOldestLog() {
        const index = (await this.db).transaction('logs').store.index('date');
        const cursor = await index.openCursor(null);
        return cursor?.value;
    }

    async fetchDenseLogs(afterTimestamp, beforeTimestamp, aggregationMethod = 'minute') {
        const fromDate = DateTime.fromSeconds(afterTimestamp).startOf(aggregationMethod).toJSDate();
        const toDate = DateTime.fromSeconds(beforeTimestamp).endOf(aggregationMethod).toJSDate();
        const range = IDBKeyRange.bound(fromDate, toDate);
        const logs = await (await this.db).getAllFromIndex('logs', 'date', afterTimestamp > 0 ? range : undefined);
        const keyFunc = {
            hour: (log) => `${log.date.getFullYear()}_${log.date.getMonth()}_${log.date.getDate()}_${log.date.getHours()}`,
            day: (log) => `${log.date.getFullYear()}_${log.date.getMonth()}_${log.date.getDate()}`,
            month: (log) => `${log.date.getFullYear()}_${log.date.getMonth()}`,
        }[aggregationMethod];
        if (keyFunc) {
            const aggregatedLogsKeys = {};
            const aggregatedLogs = [];
            logs.forEach(log => {
                const key = keyFunc(log);
                if (aggregatedLogsKeys[key] === undefined) {
                    aggregatedLogsKeys[key] = aggregatedLogs.length;
                    aggregatedLogs.push([]);
                }
                aggregatedLogs[aggregatedLogsKeys[key]].push(log);
            });
            const finalLogs = aggregatedLogs
                .map(this.chartStrategy.aggregateLogs)
                .map(log => {
                    const theDate = DateTime.fromJSDate(log.date);
                    log.date = theDate.startOf(aggregationMethod).toJSDate();
                    log.date_timestamp = Math.floor(log.date.getTime() / 1000);
                    log.date_timestamp_to = theDate.endOf(aggregationMethod).toSeconds();
                    return log;
                });
            return finalLogs;
        } else {
            return logs;
        }
    }

    async init(vue) {
        return vue.$http.get(`channels/${this.channel.id}/measurement-logs?order=DESC&limit=1000&logsType=${this.getLogsType()}`)
            .then(async ({body: logItems}) => {
                if (logItems.length) {
                    logItems.reverse();
                    if (this.hasSupport) {
                        const existingLog = await (await this.db).get('logs', logItems[0].date_timestamp);
                        if (!existingLog) {
                            await (await this.db).clear('logs');
                        }
                        await this.storeLogs(logItems);
                    }
                }
                if (logItems.length < 10 || !this.hasSupport) {
                    this.isReady = true;
                }
                return logItems;
            });
    }

    async fetchOlderLogs(vue, progressCallback, somethingDownloaded = false) {
        const oldestLog = await this.getOldestLog();
        if (oldestLog) {
            const beforeTimestamp = +oldestLog.date_timestamp - 300;
            return vue.$http.get(`channels/${this.channel.id}/measurement-logs?order=DESC&limit=2500&beforeTimestamp=${beforeTimestamp}&logsType=${this.getLogsType()}`)
                .then(async ({body: logItems, headers}) => {
                    if (logItems.length) {
                        const totalCount = +headers.get('X-Total-Count');
                        const savedCount = await (await this.db).count('logs');
                        logItems.reverse();
                        await this.storeLogs(logItems);
                        progressCallback(savedCount * 100 / totalCount);
                        return this.fetchOlderLogs(vue, progressCallback, true);
                    } else {
                        this.isReady = true;
                    }
                    return somethingDownloaded;
                });
        }
    }

    getExpectedInterval() {
        const customLogsTypes = ['voltageHistory', 'currentHistory', 'powerActiveHistory'];
        if (customLogsTypes.includes(this.logsType)) {
            return 180;
        } else {
            return 600;
        }
    }

    async storeLogs(logs) {
        if (this.chartStrategy.mergeLogsWithTheSameTimestamp) {
            logs = this.chartStrategy.mergeLogsWithTheSameTimestamp(logs);
        }
        let adjustedLogs = fillGaps(logs, this.getExpectedInterval(), this.chartStrategy.emptyLog());
        adjustedLogs = this.chartStrategy.interpolateGaps(adjustedLogs, this.channel);
        adjustedLogs = this.adjustLogsBeforeStorage(adjustedLogs);
        // the following if-s mitigate risk of bad-filled gaps when fetching logs by pages
        if (adjustedLogs.length > 100) {
            adjustedLogs.splice(0, 15);
        }
        if (adjustedLogs.length > 1) {
            adjustedLogs.shift();
        }
        const tx = (await this.db).transaction('logs', 'readwrite');
        adjustedLogs.forEach(async (log) => {
            await tx.store.put(log);
        });
        await tx.done;
        const txRaw = (await this.db).transaction('logs_raw', 'readwrite');
        logs.map(log => this.chartStrategy.fixLog(log)).forEach(async (log) => {
            await txRaw.store.put(log);
        });
        await txRaw.done;
    }
}
