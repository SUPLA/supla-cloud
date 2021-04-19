export function measurementUnit(channel) {
    if (channel.textParam2) {
        return channel.textParam2;
    }
    switch (channel.function.name) {
        case 'HEATMETER':
            return 'GJ';
        case 'ELECTRICITYMETER':
            return 'kWh';
        default:
            return 'm³';
    }
}
