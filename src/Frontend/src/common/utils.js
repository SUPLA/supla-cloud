export function safeJsonParse(possiblyJson) {
    try {
        return JSON.parse(possiblyJson);
    } catch (e) {
        return undefined;
    }
}
