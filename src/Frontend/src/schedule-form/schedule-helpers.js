export function roundTo5(int) {
    return Math.round(Math.floor(int / 5) * 5);
}

export function roundTime(time) {
    var parts = time.split(':');
    if (parts.length != 2) {
        return '0:0';
    }
    parts[1] = Math.min(Math.round(parts[1] / 5) * 5, 55);
    if (parts[1] < 10) parts[1] = '0' + parts[1];
    return parts.join(':');
}
