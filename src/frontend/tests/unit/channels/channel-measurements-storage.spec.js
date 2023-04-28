import {IndexedDbMeasurementLogsStorage} from "@/channels/history/channel-measurements-storage";
import ChannelFunction from "@/common/enums/channel-function";
import "fake-indexeddb/auto";
import {deepCopy} from "@/common/utils";

describe('Channel measurements storage', () => {
    describe('storeLogs', function () {
        const storage = new IndexedDbMeasurementLogsStorage({
            id: 12,
            functionId: ChannelFunction.THERMOMETER,
            function: {name: 'THERMOMETER'},
        });

        beforeEach(async () => await storage.connect());

        it('stores logs', async () => {
            await storage.storeLogs([
                {date_timestamp: 123, temperature: 12}
            ]);
            const logs = await storage.fetchDenseLogs(0, 200);
            expect(logs).toHaveLength(1);
            expect(logs[0].date_timestamp).toEqual(123);
            expect(logs[0].temperature).toEqual(12);
            expect(await storage.getOldestLog()).toEqual(await storage.getNewestLog());
        });
    });

    describe('EM logs storage', () => {
        const storage = new IndexedDbMeasurementLogsStorage({
            id: 13,
            functionId: ChannelFunction.ELECTRICITYMETER,
            function: {name: 'ELECTRICITYMETER'},
        });

        beforeEach(async () => await storage.connect());

        it('stores logs partially', async () => {
            const logs = [
                {date_timestamp: 0, phase1_fae: 100}, // 0
                {date_timestamp: 600, phase1_fae: 200},
                // {date_timestamp: 1200, phase1_fae: 300}, // missing log
                {date_timestamp: 1800, phase1_fae: 400}, // 2
                {date_timestamp: 2400, phase1_fae: 500},
                {date_timestamp: 3000, phase1_fae: 600}, // 4
                {date_timestamp: 3600, phase1_fae: 700},
                {date_timestamp: 4200, phase1_fae: 800}, // 6
                {date_timestamp: 4800, phase1_fae: 900},
            ];
            // first page - newest logs
            await storage.storeLogs(deepCopy(logs.slice(5, 8)));
            let storedLogs = await storage.fetchDenseLogs(0, 30000);
            expect(storedLogs).toHaveLength(2); // first log is not stored
            expect(storedLogs[0].phase1_fae).toEqual(0.001);
            expect(storedLogs[0].date_timestamp).toEqual(4200);
            expect(storedLogs[1].phase1_fae).toEqual(0.001);
            // second page - older logs
            await storage.storeLogs(deepCopy(logs.slice(3, 6)));
            storedLogs = await storage.fetchDenseLogs(0, 30000);
            expect(storedLogs).toHaveLength(4);
            expect(storedLogs.map(l => l.date_timestamp)).toEqual([3000, 3600, 4200, 4800]);
            expect(storedLogs.map(l => l.phase1_fae)).toEqual([0.001, 0.001, 0.001, 0.001]);
            // third page - missing log
            await storage.storeLogs(deepCopy(logs.slice(1, 4)));
            storedLogs = await storage.fetchDenseLogs(0, 30000);
            expect(storedLogs).toHaveLength(7);
            expect(storedLogs.map(l => l.date_timestamp)).toEqual([1200, 1800, 2400, 3000, 3600, 4200, 4800]);
            expect(storedLogs.map(l => l.phase1_fae)).toEqual([0.001, 0.001, 0.001, 0.001, 0.001, 0.001, 0.001]);
            // one but last page
            await storage.storeLogs(deepCopy(logs.slice(0, 2)));
            storedLogs = await storage.fetchDenseLogs(0, 30000);
            expect(storedLogs).toHaveLength(8);
            expect(storedLogs.map(l => l.date_timestamp)).toEqual([600, 1200, 1800, 2400, 3000, 3600, 4200, 4800]);
            expect(storedLogs.map(l => l.phase1_fae)).toEqual([0.001, 0.001, 0.001, 0.001, 0.001, 0.001, 0.001, 0.001]);
            // last page
            await storage.storeLogs(deepCopy(logs.slice(0, 1)));
            storedLogs = await storage.fetchDenseLogs(0, 30000);
            expect(storedLogs).toHaveLength(9);
            expect(storedLogs.map(l => l.date_timestamp)).toEqual([0, 600, 1200, 1800, 2400, 3000, 3600, 4200, 4800]);
            expect(storedLogs.map(l => l.phase1_fae)).toEqual([0.001, 0.001, 0.001, 0.001, 0.001, 0.001, 0.001, 0.001, 0.001]);
        });

        it('fixes gaps correcty for every missing log', async () => {
            const logs = [
                {date_timestamp: 0, phase1_fae: 100}, // 0
                {date_timestamp: 600, phase1_fae: 200},
                {date_timestamp: 1200, phase1_fae: 300}, // missing log
                {date_timestamp: 1800, phase1_fae: 400}, // 2
                {date_timestamp: 2400, phase1_fae: 500},
                {date_timestamp: 3000, phase1_fae: 600}, // 4
                {date_timestamp: 3600, phase1_fae: 700},
                {date_timestamp: 4200, phase1_fae: 800}, // 6
                {date_timestamp: 4800, phase1_fae: 900},
            ];

            for (let missingLogsFrom = 2; missingLogsFrom < logs.length - 1; missingLogsFrom++) {
                await (await storage.db).clear('logs');
                const logsToStore = deepCopy(logs);
                if (missingLogsFrom > 0) {
                    logsToStore.splice(missingLogsFrom, 1);
                }
                for (let pageOffset = logsToStore.length - 2; pageOffset >= -1; pageOffset -= 2) {
                    pageOffset = Math.max(0, pageOffset);
                    await storage.storeLogs(deepCopy(logs.slice(pageOffset, pageOffset + 3)));
                }
                await storage.storeLogs(deepCopy(logs.slice(0, 1)));
                let storedLogs = await storage.fetchDenseLogs(0, 30000);
                expect(storedLogs).toHaveLength(9);
                expect(storedLogs.map(l => l.date_timestamp)).toEqual([0, 600, 1200, 1800, 2400, 3000, 3600, 4200, 4800]);
                expect(storedLogs.map(l => l.phase1_fae)).toEqual([0.001, 0.001, 0.001, 0.001, 0.001, 0.001, 0.001, 0.001, 0.001]);
            }

        });
    });
})
