import {deepCopy} from "@/common/utils";

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

export function iodeviceTransformer(request, next) {
    if (request.url.startsWith('iodevices')) {
        if (request.body && request.body.id) {
            const toSend = {...request.body};
            if (toSend.location) {
                toSend.locationId = toSend.location.id;
                delete toSend.location;
            }
            request.body = toSend;
        }
    }
    next();
    return function (response) {
        if (response.body?.config) {
            response.body.configBefore = deepCopy(response.body.config);
        }
    }
}

export function scheduleTransformer(request, next) {
    if (request.url.startsWith('schedules')) {
        if (request.body && request.body) {
            const toSend = {...request.body};
            if (toSend.subject) {
                toSend.subjectId = toSend.subject.id;
                toSend.subjectType = toSend.subject.ownSubjectType;
                delete toSend.subject;
            }
            if (toSend.action) {
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
            const toSend = {...request.body};
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
