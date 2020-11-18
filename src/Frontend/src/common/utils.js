export function safeJsonParse(possiblyJson) {
    try {
        return JSON.parse(possiblyJson);
    } catch (e) {
        return undefined;
    }
}

export function generatePassword(length, strong = false) {
    let text = "";
    const groups = ['abcdefghijkmnopqrstuvwxyz', '0123456789', 'ABCDEFGHIJKLMNPRSTUWXYZ', '!@#$%^&*_+=;:<>,.?/'];
    for (let i = 0; i < length; i++) {
        const possible = groups[Math.floor(Math.random() * (strong ? 4 : 2))];
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}
