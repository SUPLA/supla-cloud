import {openDB} from "idb/with-async-ittr";
import {DateTime} from "luxon";
import {CHART_TYPES, fillGaps} from "@/channels/history/channel-measurements-history-chart-strategies";

export class IndexedDbMeasurementLogsStorage {
    constructor(channel) {
        this.channel = channel;
        this.chartStrategy = CHART_TYPES[this.channel.function.name];
        this.db = openDB(`channel_measurement_logs_${this.channel.id}`, 2, {
            upgrade(db) {
                if (!db.objectStoreNames.contains("logs")) {
                    const os = db.createObjectStore("logs", {keyPath: 'date_timestamp'});
                    os.createIndex("date", "date", {unique: true});
                }
            },
        });
    }

    adjustLogsBeforeStorage(logs) {
        logs = logs.map(log => {
            log.date_timestamp = +log.date_timestamp;
            log.date = DateTime.fromSeconds(log.date_timestamp).toJSDate();
            return log;
        });
        logs = this.chartStrategy.adjustLogs(logs);
        return logs.map(log => this.chartStrategy.fixLog(log));
    }

    async fetchSparseLogs() {
        const oldestLog = await this.getOldestLog();
        if (!oldestLog) {
            return [];
        }
        const newestLog = await this.getNewestLog();
        const availableStrategies = this.getAvailableAggregationStrategies(newestLog.date_timestamp - oldestLog.date_timestamp);
        const sparseLogs = await this.fetchDenseLogs(0, newestLog.date_timestamp + 1, availableStrategies[availableStrategies.length - 1]);
        return sparseLogs;
    }

    getAvailableAggregationStrategies(timestampRange) {
        const strategies = [];
        if (timestampRange < 86400 * 7) {
            strategies.push('all');
        }
        if (timestampRange > 3600 * 6 && timestampRange < 86400 * 7) {
            strategies.push('hour');
        }
        if (timestampRange > 86400 * 5 && timestampRange < 86400 * 365) {
            strategies.push('day');
        }
        if (timestampRange > 86400 * 60) {
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

    async fetchDenseLogs(afterTimestamp, beforeTimestamp, aggregationMethod) {
        const fromDate = DateTime.fromSeconds(afterTimestamp).toJSDate();
        const toDate = DateTime.fromSeconds(beforeTimestamp).toJSDate();
        const range = IDBKeyRange.bound(fromDate, toDate);
        const logs = await (await this.db).getAllFromIndex('logs', 'date', range);
        const keyFunc = {
            hour: (log) => `${log.date.getFullYear()}_${log.date.getMonth()}_${log.date.getDate()}_${log.date.getHours()}`,
            day: (log) => `${log.date.getFullYear()}_${log.date.getMonth()}_${log.date.getDate()}`,
            month: (log) => `${log.date.getFullYear()}_${log.date.getMonth()}`,
        }[aggregationMethod];
        if (keyFunc) {
            const aggregatedLogsKeys = {};
            const aggregatedLogs = [];
            console.time('aggregating');
            logs.forEach(log => {
                const key = keyFunc(log);
                if (aggregatedLogsKeys[key] === undefined) {
                    aggregatedLogsKeys[key] = aggregatedLogs.length;
                    aggregatedLogs.push([]);
                }
                aggregatedLogs[aggregatedLogsKeys[key]].push(log);
            });
            console.timeEnd('aggregating');
            const finalLogs = aggregatedLogs.map(this.chartStrategy.aggregateLogs);
            return finalLogs;
        } else {
            return logs;
        }
    }

    async init(vue) {
        return vue.$http.get(`channels/${this.channel.id}/measurement-logs?order=DESC&limit=1000`)
            .then(async ({body: logItems}) => {
                if (logItems.length) {
                    logItems.reverse();
                    const existingLog = await (await this.db).get('logs', logItems[0].date_timestamp);
                    if (!existingLog) {
                        await (await this.db).clear('logs');
                    }
                    return await this.storeLogs(logItems);
                }
            });
    }

    async fetchOlderLogs(vue, progressCallback, somethingDownloaded = false) {
        const oldestLog = await this.getOldestLog();
        if (oldestLog) {
            const beforeTimestamp = +oldestLog.date_timestamp - 300;
            return vue.$http.get(`channels/${this.channel.id}/measurement-logs?order=DESC&limit=2500&beforeTimestamp=${beforeTimestamp}`)
                .then(async ({body: logItems, headers}) => {
                    if (logItems.length) {
                        const totalCount = +headers.get('X-Total-Count');
                        const savedCount = await (await this.db).count('logs');
                        logItems.reverse();
                        await this.storeLogs(logItems);
                        progressCallback(savedCount * 100 / totalCount);
                        return this.fetchOlderLogs(vue, progressCallback, true);
                    }
                    return somethingDownloaded;
                });
        }
    }

    async storeLogs(logs) {
        logs = fillGaps(logs, 600, this.chartStrategy.emptyLog());
        logs = this.chartStrategy.interpolateGaps(logs);
        const tx = (await this.db).transaction('logs', 'readwrite');
        logs = this.adjustLogsBeforeStorage(logs);
        logs.forEach(async (log) => {
            await tx.store.put(log);
        });
        await tx.done;
    }
}
