import Vue from "vue";

export function channelGroupTransformer(request, next) {
    if (request.url.startsWith('channel-groups')) {
        if (request.body) {
            if (request.body.channels) {
                request.body.channelsIds = request.body.channels.map(c => c.id);
                delete request.body.channels;
            }
            if (request.body.function) {
                delete request.body.function;
            }
            if (request.body.location) {
                request.body.locationId = request.body.location.id;
                delete request.body.location;
            }
        }
    }
    next();
}

export function channelTransformer(request, next) {
    if (request.url.startsWith('channels')) {
        if (request.body && request.body.id) {
            const toSend = Vue.util.extend({}, request.body);
            delete toSend.supportedFunctions;
            if (toSend.function) {
                toSend.functionId = toSend.function.id;
                delete toSend.function;
            }
            if (toSend.type) {
                toSend.typeId = toSend.type.id;
                delete toSend.type;
            }
            if (toSend.location) {
                toSend.locationId = toSend.location.id;
                delete toSend.location;
            }
            if (toSend.iodevice) {
                toSend.iodeviceId = toSend.iodevice.id;
                delete toSend.iodevice;
            }
            request.body = toSend;
        }
    }
    next();
}

export function locationTransformer(request, next) {
    if (request.url.startsWith('locations')) {
        if (request.body && request.body.id) {
            const toSend = Vue.util.extend({}, request.body);
            delete toSend.channelGroups;
            delete toSend.ioDevices;
            if (toSend.accessIds) {
                toSend.accessIdsIds = toSend.accessIds.map(aid => aid.id);
                delete toSend.accessIds;
            }
            request.body = toSend;
        }
    }
    next();
}

export function accessIdTransformer(request, next) {
    if (request.url.startsWith('accessids')) {
        if (request.body && request.body.id) {
            const toSend = Vue.util.extend({}, request.body);
            if (toSend.locations) {
                toSend.locationsIds = toSend.locations.map(loc => loc.id);
                delete toSend.locations;
            }
            if (toSend.clientApps) {
                toSend.clientAppsIds = toSend.clientApps.map(app => app.id);
                delete toSend.clientApps;
            }
            request.body = toSend;
        }
    }
    next();
}

export function clientAppTransformer(request, next) {
    if (request.url.startsWith('client-apps')) {
        if (request.body && request.body.id) {
            const toSend = Vue.util.extend({}, request.body);
            if (toSend.accessId) {
                toSend.accessIdId = toSend.accessId.id;
                delete toSend.accessId;
            }
            request.body = toSend;
        }
    }
    next();
}

export function iodeviceTransformer(request, next) {
    if (request.url.startsWith('iodevices')) {
        if (request.body && request.body.id) {
            const toSend = Vue.util.extend({}, request.body);
            if (toSend.location) {
                toSend.locationId = toSend.location.id;
                delete toSend.location;
            }
            request.body = toSend;
        }
    }
    next();
}

export function scheduleTransformer(request, next) {
    if (request.url.startsWith('schedules')) {
        if (request.body && request.body) {
            const toSend = Vue.util.extend({}, request.body);
            if (toSend.subject) {
                toSend.subjectId = toSend.subject.id;
                toSend.subjectType = toSend.subject.subjectType;
                delete toSend.subject;
            }
            if (toSend.action) {
                toSend.actionId = toSend.action.id;
                delete toSend.action;
            }
            request.body = toSend;
        }
    }
    next();
}

export function sceneTransformer(request, next) {
    if (request.url.startsWith('scenes')) {
        if (request.body) {
            const toSend = Vue.util.extend({}, request.body);
            if (toSend.location) {
                toSend.locationId = toSend.location.id;
                delete toSend.location;
            }
            if (toSend.operations) {
                toSend.operations.forEach(operation => {
                    if (operation.id) {
                        delete operation.id;
                    }
                    if (operation.subject) {
                        operation.subjectId = operation.subject.id;
                        delete operation.subject;
                    }
                    if (operation.action) {
                        operation.actionId = operation.action.id;
                        delete operation.action;
                    }
                });
            }
            request.body = toSend;
        }
    }
    next();
}
