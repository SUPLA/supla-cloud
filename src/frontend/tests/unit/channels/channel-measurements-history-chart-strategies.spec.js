import {CHART_TYPES, fillGaps} from "@/channels/history/channel-measurements-history-chart-strategies";

describe('Channel measurement history data strategies', () => {
    describe('fillGaps', function () {
        it('does not do anything with empty array', () => {
            expect(fillGaps([], 10, {})).toEqual([]);
        });

        it('does not do anything with array with two values', () => {
            expect(fillGaps([{}, {}], 10, {})).toEqual([{}, {}]);
        });

        it('fills gap when missing', () => {
            const filled = fillGaps(
                [
                    {"date_timestamp": 1000, "temperature": 21.375},
                    {"date_timestamp": 1500, "temperature": 25.25},
                    {"date_timestamp": 2500, "temperature": 22.25},
                    {"date_timestamp": 3000, "temperature": 21.8125},
                ],
                500,
                {date_timestamp: null, temperature: null}
            );
            expect(filled).toHaveLength(5);
            expect(filled[1]).toEqual({date_timestamp: 1500, temperature: 25.25});
            expect(filled[2]).toEqual({date_timestamp: 2000, temperature: null, interpolated: true});
            expect(filled[3]).toEqual({date_timestamp: 2500, temperature: 22.25});
        });

        it('does not fill gaps when not precise interval', () => {
            const filled = fillGaps(
                [
                    {"date_timestamp": 1000, "temperature": 21.375},
                    {"date_timestamp": 1550, "temperature": 25.25},
                    {"date_timestamp": 1950, "temperature": 22.25},
                    {"date_timestamp": 2600, "temperature": 21.8125},
                ],
                500,
                {date_timestamp: null, temperature: null}
            );
            expect(filled).toHaveLength(4);
        });

        it('fills big gap with logs', () => {
            const filled = fillGaps(
                [
                    {"date_timestamp": 1000, "temperature": 21.375},
                    {"date_timestamp": 1450, "temperature": 25.25},
                    {"date_timestamp": 5100, "temperature": 22.25},
                    {"date_timestamp": 5555, "temperature": 21.8125},
                ],
                500,
                {date_timestamp: null, temperature: null}
            );
            expect(filled).toHaveLength(11);
            expect(filled[1]).toEqual({date_timestamp: 1450, temperature: 25.25});
            expect(filled[2]).toEqual({date_timestamp: 1950, temperature: null, interpolated: true});
            expect(filled[6]).toEqual({date_timestamp: 3950, temperature: null, interpolated: true});
            expect(filled[9]).toEqual({date_timestamp: 5100, temperature: 22.25});
        });
    });

    describe('ELECTRICITYMETER', function () {
        const strategy = CHART_TYPES.ELECTRICITYMETER;

        it('does nothing to empty log', () => {
            expect(strategy.fixLog(strategy.emptyLog())).toEqual(strategy.emptyLog());
        });

        it('fixes a full log', () => {
            const logToFix = {
                date_timestamp: null,
                phase1_fae: 100000, phase2_fae: 200000, phase3_fae: 300000,
                phase1_rae: 400000, phase2_rae: 500000, phase3_rae: 600000,
                phase1_fre: 700000, phase2_fre: 800000, phase3_fre: 900000,
                phase1_rre: 1000000, phase2_rre: 1100000, phase3_rre: 1200000,
            };
            expect(strategy.fixLog(logToFix)).toEqual({
                date_timestamp: null,
                phase1_fae: 1, phase2_fae: 2, phase3_fae: 3,
                phase1_rae: 4, phase2_rae: 5, phase3_rae: 6,
                phase1_fre: 7, phase2_fre: 8, phase3_fre: 9,
                phase1_rre: 10, phase2_rre: 11, phase3_rre: 12,
                fae_total: 6, rae_total: 15, fae_rae_balance: -9,
            });
        });

        it('fixes a partial log', () => {
            const logToFix = {
                date_timestamp: null,
                phase1_fae: null, phase2_fae: 200000, phase3_fae: null,
                phase1_rae: 400000, phase2_rae: null, phase3_rae: 600000,
                phase1_fre: null, phase2_fre: 800000, phase3_fre: 900000,
                phase1_rre: 1000000, phase2_rre: 1100000, phase3_rre: 1200000,
            };
            expect(strategy.fixLog(logToFix)).toEqual({
                date_timestamp: null,
                phase1_fae: null, phase2_fae: 2, phase3_fae: null,
                phase1_rae: 4, phase2_rae: null, phase3_rae: 6,
                phase1_fre: null, phase2_fre: 8, phase3_fre: 9,
                phase1_rre: 10, phase2_rre: 11, phase3_rre: 12,
                fae_total: 2, rae_total: 10, fae_rae_balance: -8,
            });
        });

        it('interpolates gaps for EM logs', () => {
            const logs = require('./measurement-logs/em_logs_case1.json');
            const filled = fillGaps(logs, 600, CHART_TYPES.ELECTRICITYMETER.emptyLog());
            expect(filled).toHaveLength(logs.length + 3);
            const interpolated = strategy.interpolateGaps(filled);
            expect(interpolated[1].phase3_rre).toEqual(logs[1].phase3_rre);
            // logs[1] = 32358298
            // --- gap ---
            // logs[2] = 32363098
            // difference = 32363098 - 32358298 = 4800, so:
            // interpolated[2] = 32358298 + 2400 = 32360698
            // interpolated[3] = logs[2]
            expect(interpolated[2].phase3_rre).not.toEqual(logs[2].phase3_rre);
            expect(interpolated[2].phase3_rre).toEqual(32360698);
            expect(interpolated[3].phase3_rre).toEqual(logs[2].phase3_rre);
            expect(interpolated[4].phase3_rre).toEqual(logs[3].phase3_rre);
        });

        it('adjusts logs and store deltas for charts', () => {
            const logs = [
                {date_timestamp: 0, phase1_fae: 1},
                {date_timestamp: 1, phase1_fae: 2},
                {date_timestamp: 2, phase1_fae: 4},
                {date_timestamp: 3, phase1_fae: 4},
            ];
            const adjustedLogs = strategy.adjustLogs(logs);
            const adjustedPhase1Fae = adjustedLogs.map(l => l.phase1_fae);
            expect(adjustedPhase1Fae).toEqual([1, 1, 2, 0]);
        });

        it('can cumulate data back for export', () => {
            const logs = [
                {date_timestamp: 0, phase1_fae: 1},
                {date_timestamp: 1, phase1_fae: 1},
                {date_timestamp: 2, phase1_fae: 2},
                {date_timestamp: 3, phase1_fae: 0},
            ];
            const cumulatedLogs = strategy.cumulateLogs(logs);
            const cumulatedPhase1Fae = cumulatedLogs.map(l => l.phase1_fae);
            expect(cumulatedPhase1Fae).toEqual([1, 2, 4, 4]);
        });

        it('detects counter resets', () => {
            const logs = [
                {date_timestamp: 0, phase1_fae: 1},
                {date_timestamp: 1, phase1_fae: 2},
                {date_timestamp: 2, phase1_fae: 4},
                {date_timestamp: 3, phase1_fae: 4},
                {date_timestamp: 4, phase1_fae: 1},
                {date_timestamp: 5, phase1_fae: 7},
            ];
            const adjustedLogs = strategy.adjustLogs(logs);
            const adjustedPhase1Fae = adjustedLogs.map(l => l.phase1_fae);
            expect(adjustedPhase1Fae).toEqual([1, 1, 2, 0, 1, 6]);
            expect(adjustedLogs[4].counterReset).toBeTruthy();
        });

        it('cumulates logs back with counter reset', () => {
            const logs = [
                {date_timestamp: 0, phase1_fae: 1},
                {date_timestamp: 1, phase1_fae: 2},
                {date_timestamp: 2, phase1_fae: 4},
                {date_timestamp: 3, phase1_fae: 4},
                {date_timestamp: 4, phase1_fae: 1, counterReset: true},
                {date_timestamp: 5, phase1_fae: 7},
            ];
            const cumulatedLogs = strategy.cumulateLogs(strategy.adjustLogs(logs));
            expect(cumulatedLogs.map(l => l.phase1_fae)).toEqual(logs.map(l => l.phase1_fae));
            expect(cumulatedLogs.map(l => l.counterReset)).toEqual(logs.map(l => l.counterReset));
        });

        it('adjusts null values', () => {
            const logs = [
                {date_timestamp: 0, phase1_fae: 1},
                {date_timestamp: 1, phase1_fae: 2},
                {date_timestamp: 2, phase1_fae: null},
                {date_timestamp: 3, phase1_fae: 4},
            ];
            const adjustedLogs = strategy.adjustLogs(logs);
            const adjustedPhase1Fae = adjustedLogs.map(l => l.phase1_fae);
            expect(adjustedPhase1Fae).toEqual([1, 1, 0, 2]);
        });

        it('adjusts all null values', () => {
            const logs = [
                {date_timestamp: 0, phase1_fae: 1},
                {date_timestamp: 1, phase1_fae: 2},
                {date_timestamp: 2, phase1_fae: null},
                {date_timestamp: 3, phase1_fae: 4},
            ];
            const adjustedLogs = strategy.adjustLogs(logs);
            const adjustedPhase1Fae = adjustedLogs.map(l => l.phase2_fae);
            expect(adjustedPhase1Fae).toEqual([undefined, 0, 0, 0]);
        });

        describe('yaxes', function () {
            const vueMock = {
                $t: a => a,
                chartMode: 'fae',
                channel: {},
            };

            it('chooses a label', () => {
                const logs = [{phase1_fae: 1, phase2_fae: 1, phase3_fae: 1}];
                const yaxes = strategy.yaxes.call(vueMock, logs);
                expect(yaxes).toHaveLength(1);
                expect(yaxes[0].title.text).toEqual('Forward active energy');
            });

            it('calculates max for one log with rounded max', () => {
                const logs = [{phase1_fae: 1, phase2_fae: 1, phase3_fae: 1}];
                const yaxes = strategy.yaxes.call(vueMock, logs);
                expect(yaxes[0].max).toEqual(3);
            });

            it('calculates max for one log with rounded max ceil', () => {
                const logs = [{phase1_fae: 1, phase2_fae: 1.01, phase3_fae: 1}];
                const yaxes = strategy.yaxes.call(vueMock, logs);
                expect(yaxes[0].max).toEqual(4);
            });

            it('calculates max for one log with rounded max less than one', () => {
                const logs = [{phase1_fae: 0.01, phase2_fae: 0.02, phase3_fae: 0.03}];
                const yaxes = strategy.yaxes.call(vueMock, logs);
                expect(yaxes[0].max).toEqual(0.1);
            });

            it('calculates max for one log with rounded max/2', () => {
                const logs = [{phase1_fae: 0.001, phase2_fae: 0.001, phase3_fae: 0.001}];
                const yaxes = strategy.yaxes.call(vueMock, logs);
                expect(yaxes[0].max).toEqual(0.005);
            });

            it('calculates max for one log with rounded max/5', () => {
                const logs = [{phase1_fae: 0.0001, phase2_fae: 0.001, phase3_fae: 0.0001}];
                const yaxes = strategy.yaxes.call(vueMock, logs);
                expect(yaxes[0].max).toEqual(0.002);
            });

            it('calculates max for one log with many logs', () => {
                const logs = [
                    {phase1_fae: 0.001, phase2_fae: 0.001, phase3_fae: 0.001},
                    {phase1_fae: 0.001, phase2_fae: 0.03, phase3_fae: 0.001},
                    {phase1_fae: 0.001, phase2_fae: 0.012, phase3_fae: 0.001},
                ];
                const yaxes = strategy.yaxes.call(vueMock, logs);
                expect(yaxes[0].max).toEqual(0.05);
            });
        });
    });

    describe('IC_GASMETER', function () {
        const strategy = CHART_TYPES.IC_GASMETER;

        it('aggregates simple logs', () => {
            const logs = [
                {date_timestamp: 1, counter: 1, calculated_value: 2},
                {date_timestamp: 2, counter: 3, calculated_value: 6},
                {date_timestamp: 3, counter: 1, calculated_value: 2},
            ];
            const aggregatedLog = strategy.aggregateLogs(logs);
            expect(aggregatedLog.counter).toEqual(5);
            expect(aggregatedLog.calculated_value).toEqual(10);
        });

        it('aggregates logs with counterReset flag', () => {
            const logs = [
                {date_timestamp: 1, counter: 1, calculated_value: 2},
                {date_timestamp: 2, counter: 3, calculated_value: 6, counterReset: true},
                {date_timestamp: 3, counter: 1, calculated_value: 2},
            ];
            const aggregatedLog = strategy.aggregateLogs(logs);
            expect(aggregatedLog.counterReset).toBeTruthy();
        });

        it('adjusts logs', () => {
            const logs = [
                {date_timestamp: 1, counter: 1, calculated_value: 2},
                {date_timestamp: 2, counter: 3, calculated_value: 6},
                {date_timestamp: 3, counter: 4, calculated_value: 8},
            ];
            const adjustedLogs = strategy.adjustLogs(logs);
            expect(adjustedLogs.map(l => l.counter)).toEqual([1, 2, 1]);
            expect(adjustedLogs.map(l => l.calculated_value)).toEqual([2, 4, 2]);
        });

        it('interpolates logs', () => {
            const logs = [
                {date_timestamp: 0, counter: 1, calculated_value: 2},
                {date_timestamp: 100, counter: 3, calculated_value: 6},
                {date_timestamp: 200, counter: null, calculated_value: null},
                {date_timestamp: 300, counter: 5, calculated_value: 10},
            ];
            const adjustedLogs = strategy.interpolateGaps(logs);
            expect(adjustedLogs[2].calculated_value).toEqual(8);
            expect(adjustedLogs[2].counter).toEqual(4);
        });

        it('does not interpolate before counter reset logs', () => {
            const logs = [
                {date_timestamp: 0, counter: 1, calculated_value: 2},
                {date_timestamp: 100, counter: 3, calculated_value: 6},
                {date_timestamp: 200, counter: null, calculated_value: null},
                {date_timestamp: 300, counter: 0, calculated_value: 0},
            ];
            const adjustedLogs = strategy.interpolateGaps(logs);
            expect(adjustedLogs[2].calculated_value).toBeNull();
            expect(adjustedLogs[2].counter).toBeNull();
        });

        it('detects counter resets on adjusting', () => {
            const logs = [
                {date_timestamp: 1, counter: 1, calculated_value: 2},
                {date_timestamp: 2, counter: 3, calculated_value: 6},
                {date_timestamp: 3, counter: 4, calculated_value: 8},
                {date_timestamp: 3, counter: 1, calculated_value: 2},
                {date_timestamp: 3, counter: 3, calculated_value: 6},
            ];
            const adjustedLogs = strategy.adjustLogs(logs);
            expect(adjustedLogs.map(l => l.counter)).toEqual([1, 2, 1, 1, 2]);
            expect(adjustedLogs[3].counterReset).toBeTruthy();
        });
    });
})
