import Vue from "vue";

export function channelGroupTransformer(request, next) {
    if (request.url.startsWith('channel-groups')) {
        if (request.body) {
            if (request.body.channels) {
                request.body.channelIds = request.body.channels.map(c => c.id);
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
