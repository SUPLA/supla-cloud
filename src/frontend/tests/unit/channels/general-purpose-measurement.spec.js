import {formatGpmValue} from "@/common/filters";

describe('GeneralPurposeMeasurement', () => {
    describe('formatting GPM value', () => {
        const tests = [
            ['20', 20.345, null],
            ['20', 20.345, {}],
            ['---', null, {}],
            ['20.3', 20.345, {valuePrecision: 1}],
            ['20.35', 20.345, {valuePrecision: 2}],
            ['20.35 kWh', 20.345, {valuePrecision: 2, unitAfterValue: 'kWh'}],
            ['---', null, {valuePrecision: 2, unitAfterValue: 'kWh'}],
            ['$ 20.35 kWh', 20.345, {valuePrecision: 2, unitAfterValue: 'kWh', unitBeforeValue: '$'}],
            ['$ 20.35kWh', 20.345, {valuePrecision: 2, unitAfterValue: 'kWh', unitBeforeValue: '$', noSpaceAfterValue: true}],
            ['$20.35 kWh', 20.345, {valuePrecision: 2, unitAfterValue: 'kWh', unitBeforeValue: '$', noSpaceBeforeValue: true}],
        ];

        it.each(tests)('formats GPM value to %p', (expectedValue, value, config) => {
            const formattedValue = formatGpmValue(value, config);
            expect(formattedValue).toEqual(expectedValue);
        });
    });
})
