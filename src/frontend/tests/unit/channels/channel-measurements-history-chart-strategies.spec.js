import {fillGaps} from "@/channels/channel-measurements-history-chart-strategies";

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
})
