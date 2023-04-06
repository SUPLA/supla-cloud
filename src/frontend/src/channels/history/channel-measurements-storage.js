import {openDB} from "idb/with-async-ittr";
import {DateTime} from "luxon";

export class IndexedDbMeasurementLogsStorage {
    constructor(channel) {
        this.channel = channel;
        this.db = openDB(`channel_measurement_logs_${this.channel.id}`, 1, {
            upgrade(db) {
                if (!db.objectStoreNames.contains("logs")) {
                    const os = db.createObjectStore("logs", {keyPath: 'date_timestamp'});
                    os.createIndex("date", "date", {unique: true});
                }
            },
        });
    }

    async fetchSparseLogs(numberOfLogs) {
        const totalCount = await (await this.db).count('logs');
        console.log(totalCount);
        const step = Math.max(1, Math.floor(totalCount / numberOfLogs));
        console.log(step);
        const store = (await this.db).transaction('logs').store;
        const logs = [];
        for await(const cursor of store.index('date').iterate(null)) {
            logs.push(cursor.value);
            cursor.advance(step);
        }
        for await(const cursor of store.index('date').iterate(null, 'prev')) {
            if (logs.indexOf(cursor.value) === -1) {
                logs.push(cursor.value);
            }
            break;
        }
        return logs;
    }

    async getLastLog() {
        const index = (await this.db).transaction('logs').store.index('date');
        const cursor = await index.openCursor(null, 'prev');
        return cursor?.value;
    }

    async fetchDenseLogs(afterTimestamp, beforeTimestamp) {
        const fromDate = DateTime.fromSeconds(afterTimestamp).toJSDate();
        const toDate = DateTime.fromSeconds(beforeTimestamp).toJSDate();
        const range = IDBKeyRange.bound(fromDate, toDate);
        return (await this.db).getAllFromIndex('logs', 'date', range);
    }

    async init(vue) {
        const lastLog = await this.getLastLog();
        const afterTimestamp = (+lastLog?.date_timestamp || 0) + 1;
        return vue.$http.get(`channels/${this.channel.id}/measurement-logs?order=ASC&afterTimestamp=${afterTimestamp}`)
            .then(async ({body: logItems}) => {
                if (logItems.length) {
                    const tx = (await this.db).transaction('logs', 'readwrite');
                    logItems.forEach(async (log) => {
                        log.date = DateTime.fromSeconds(log.date_timestamp).toJSDate();
                        await tx.store.add(log);
                    });
                    await tx.done;
                    return this.init(vue);
                }
            });
    }
}
