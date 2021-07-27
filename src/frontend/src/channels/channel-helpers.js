export function measurementUnit(channel) {
    if (channel.config && channel.config.unit) {
        return channel.config.unit;
    }
    switch (channel.function.name) {
        case 'IC_HEATMETER':
            return 'GJ';
        case 'ELECTRICITYMETER':
        case 'IC_ELECTRICITYMETER':
            return 'kWh';
        default:
            return 'm³';
    }
}
