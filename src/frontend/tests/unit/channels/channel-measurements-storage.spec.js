import {IndexedDbMeasurementLogsStorage} from "@/channels/history/channel-measurements-storage";
import ChannelFunction from "@/common/enums/channel-function";
import "fake-indexeddb/auto";

describe('Channel measurements storage', () => {
    describe('storeLogs', function () {
        const storage = new IndexedDbMeasurementLogsStorage({
            id: 12,
            functionId: ChannelFunction.THERMOMETER,
            function: {name: 'THERMOMETER'},
        });

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
})
