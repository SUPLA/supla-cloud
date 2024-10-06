import {escapeI18n} from "@/locale";

describe('locale', () => {
    describe('escapeI18n', () => {
        const tests = [
            ['test', 'test'],
            ['test@fracz', "test{'@'}fracz"],
            ['test{', "test{'{'}"],
            ['crazy | message {} that want@s to earn $$$', "crazy {'|'} message {'{'}{'}'} that want{'@'}s to earn {'$'}{'$'}{'$'}"],
        ];

        it.each(tests)('escapes translation %p -> %p', (translation, expectedEscaped) => {
            const escaped = escapeI18n(translation);
            expect(escaped).toEqual(expectedEscaped);
        });
    });
})
