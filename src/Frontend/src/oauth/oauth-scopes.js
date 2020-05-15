export const availableScopes = [
    {
        prefix: 'account',
        suffixes: ['r', 'rw'],
        label: 'Account' // i18n
    },
    {
        prefix: 'channels',
        suffixes: ['r', 'rw', 'ea'],
        label: 'Channels' // i18n
    },
    {
        prefix: 'channelgroups',
        suffixes: ['r', 'rw', 'ea'],
        label: 'Channel groups' // i18n
    },
    {
        prefix: 'directlinks',
        suffixes: ['r', 'rw'],
        label: 'Direct links' // i18n
    },
    {
        prefix: 'iodevices',
        suffixes: ['r', 'rw'],
        label: 'I/O Devices' // i18n
    },
    {
        prefix: 'clientapps',
        suffixes: ['r', 'rw'],
        label: 'Client’s Apps' // i18n
    },
    {
        prefix: 'accessids',
        suffixes: ['r', 'rw'],
        label: 'Access Identifiers' // i18n
    },
    {
        prefix: 'locations',
        suffixes: ['r', 'rw'],
        label: 'Locations' // i18n
    },
    {
        prefix: 'schedules',
        suffixes: ['r', 'rw'],
        label: 'Schedules' // i18n
    },
    {
        prefix: 'offline',
        suffixes: ['access'],
        label: 'Offline access' // i18n
    },
    {
        prefix: 'state',
        suffixes: ['webhook'],
        label: 'State webhook' // i18n
    },
];

export function scopeId(scope, suffix) {
    return `${scope.prefix}${suffix ? '_' + suffix : ''}`;
}

export const scopeSuffixLabels = {
    r: 'Read', // i18n
    rw: 'Modification', // i18n
    ea: 'Action execution', // i18n
    access: 'Account access when you are unavailable', // i18n
    webhook: 'Be notified when your channels change state', // i18n
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
        return !scope.match(/.+_r$/) || arrayOfScopes.indexOf(scope + 'w') === -1;
    });
}

export function addImplicitScopes(arrayOfScopes) {
    const rwScopes = arrayOfScopes.filter(scope => scope.match(/.+_rw$/));
    rwScopes.forEach(rwScope => {
        const rScope = rwScope.substr(0, rwScope.length - 1);
        if (arrayOfScopes.indexOf(rScope) === -1) {
            arrayOfScopes.push(rScope);
        }
    });
    return arrayOfScopes;
}
