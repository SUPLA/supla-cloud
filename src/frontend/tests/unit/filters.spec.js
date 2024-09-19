import {prettyMilliseconds} from "@/common/filters";

describe('filters', () => {
    describe('prettyMilliseconds', () => {
        const tests = [
            [100, '100 ms'],
            [1000, '1 sec.'],
            [60000, '1 min.'],
            [60000 * 5, '5 min.'],
            [3600000 - 60000, '59 min.'],
            [3600000, '1 hour'],
            [3600000 * 2, '2 hours'],
            [3600000 * 24, '1 day'],
            [3600000 * 24 * 2, '2 days'],
            [1500, '1.5 sec.'],
            [1567, '1.6 sec.'],
            [61000, '1 min. 1 sec.'],
            [160000, '2 min. 40 sec.'],
        ];

        it.each(tests)('formats GPM value %p -> %p', (ms, expectedLabel) => {
            const label = prettyMilliseconds(ms);
            expect(label).toEqual(expectedLabel);
        });
    });
})
