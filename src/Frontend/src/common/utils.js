export function safeJsonParse(possiblyJson) {
    try {
        return JSON.parse(possiblyJson);
    } catch (e) {
        return undefined;
    }
}

export function generatePassword(length) {
    let text = "";
    let possible = "abcdefghijklmnopqrstuvwxyz0123456789";
    for (let i = 0; i < length; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}
