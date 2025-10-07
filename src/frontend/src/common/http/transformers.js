import {deepCopy} from "@/common/utils";

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
