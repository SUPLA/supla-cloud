import {CHART_TYPES, fillGaps} from "@/channels/channel-measurements-history-chart-strategies";

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
                500 * 1000,
                {date_timestamp: null, temperature: null}
            );
            expect(filled).toHaveLength(5);
            expect(filled[1]).toEqual({date_timestamp: 1500, temperature: 25.25});
            expect(filled[2]).toEqual({date_timestamp: 2000, temperature: null});
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
                500 * 1000,
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
                500 * 1000,
                {date_timestamp: null, temperature: null}
            );
            expect(filled).toHaveLength(11);
            expect(filled[1]).toEqual({date_timestamp: 1450, temperature: 25.25});
            expect(filled[2]).toEqual({date_timestamp: 1950, temperature: null});
            expect(filled[6]).toEqual({date_timestamp: 3950, temperature: null});
            expect(filled[9]).toEqual({date_timestamp: 5100, temperature: 22.25});
        });
    });

    describe('ELECTRICITYMETER', function () {
        const strategy = CHART_TYPES.ELECTRICITYMETER;

        describe('fixLog', function () {
            it('does nothing to no log', () => {
                expect(strategy.fixLog({})).toEqual({});
            });

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
                });
            });
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
})
