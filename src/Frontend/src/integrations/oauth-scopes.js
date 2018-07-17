export const availableScopes = [
    {prefix: 'account', suffixes: ['r', 'rw'], label: 'Account'},
    {prefix: 'channels', suffixes: ['r', 'rw', 'ea'], label: 'Channels'},
    {prefix: 'channelgroups', suffixes: ['r', 'rw', 'ea'], label: 'Channel groups'},
    {prefix: 'iodevices', suffixes: ['r', 'rw'], label: 'I/O Devices'},
    {prefix: 'clientapps', suffixes: ['r', 'rw'], label: 'Client\'s Apps'},
    {prefix: 'accessids', suffixes: ['r', 'rw'], label: 'Access Identifiers'},
    {prefix: 'locations', suffixes: ['r', 'rw'], label: 'Locations'},
    {prefix: 'schedules', suffixes: ['r', 'rw'], label: 'Schedules'},
];

export function scopeId(scope, suffix) {
    return `${scope.prefix}_${suffix}`;
}

export const scopeSuffixLabels = {
    'r': 'Read',
    'rw': 'Modify',
    'ea': 'Execute action',
};

const SCOPE_DELIMITER = ' ';

export function arrayOfScopes(stringOfScopes) {
    return stringOfScopes.split(SCOPE_DELIMITER);
}

export function stringOfScopes(arrayOfScopes) {
    return arrayOfScopes.join(SCOPE_DELIMITER);
}

export function removeImplicitScopes(arrayOfScopes) {
    return arrayOfScopes.filter(scope => {
        return !scope.match(/_r$/) || arrayOfScopes.indexOf(scope + 'w') === -1;
    });
}

export function addImplicitScopes(arrayOfScopes) {
    const rwScopes = arrayOfScopes.filter(scope => !scope.match(/_rw$/));
    rwScopes.forEach(rwScope => {
        const rScope = rwScope.substr(0, rwScope.length - 1);
        if (arrayOfScopes.indexOf(rScope) === -1) {
            arrayOfScopes.push(rScope);
        }
    });
    return arrayOfScopes;
}
