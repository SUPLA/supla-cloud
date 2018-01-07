export function channelGroupPost(request, next) {
    if (request.url.startsWith('channel-groups')) {
        if (request.body) {
            if (request.body.channels) {
                request.body.channelIds = request.body.channels.map(c => c.id);
                delete request.body.channels;
            }
            if (request.body.function) {
                delete request.body.function;
            }
        }
    }
    next();
}
