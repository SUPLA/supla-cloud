export const changeScheduleMode = (state, newScheduleMode) => {
    state.scheduleMode = newScheduleMode;
    state.nextRunDates = [];
    state.timeExpression = '';
};

export const updateTimeExpression = (state, timeExpression) => {
    state.timeExpression = timeExpression;
};

export const updateCaption = (state, caption) => {
    state.caption = caption;
};

export const updateDateStart = (state, date) => {
    state.dateStart = date ? date.format() : moment().format();
};

export const updateDateEnd = (state, date) => {
    state.dateEnd = date ? date.format() : '';
};

export const fetchingNextRunDates = (state, fetching = true) => {
    state.fetchingNextRunDates = fetching;
};

export const updateNextRunDates = (state, nextRunDates) => {
    state.nextRunDates = nextRunDates;
};

export const clearNextRunDates = (state) => {
    state.nextRunDates = [];
};

export const updateChannel = (state, channel) => {
    state.channel = channel;
    state.action = undefined;
    state.actionParam = undefined;
};

export const updateAction = (state, action) => {
    state.action = action;
    state.actionParam = undefined;
};

export const updateActionParam = (state, actionParam) => {
    state.actionParam = actionParam;
};

export const submit = (state) => {
    state.submitting = true;
};

export const submitFailed = (state) => {
    state.submitting = false;
};

export const editSchedule = (state, schedule) => {
    state.scheduleMode = schedule.mode;
    state.timeExpression = schedule.timeExpression;
    state.dateStart = schedule.dateStart;
    state.dateEnd = schedule.dateEnd;
    state.channel = schedule.channel.id;
    state.action = schedule.action;
    try {
        state.actionParam = JSON.parse(schedule.actionParam);
    } catch (e) {
    }
    state.caption = schedule.caption;
};
