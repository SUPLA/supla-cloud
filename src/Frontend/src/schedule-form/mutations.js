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

export const fetchingNextRunDates = (state) => {
    state.fetchingNextRunDates = true;
};

export const updateNextRunDates = (state, nextRunDates) => {
    state.nextRunDates = nextRunDates;
    state.fetchingNextRunDates = false;
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
    state.scheduleMode = schedule.mode.value;
    state.timeExpression = schedule.timeExpression;
    state.dateStart = schedule.dateStart;
    state.dateEnd = schedule.dateEnd;
    state.channel = schedule.channel.id;
    state.action = schedule.action.value;
    try {
        state.actionParam = JSON.parse(schedule.actionParam);
    } catch (e) {
    }
    state.caption = schedule.caption;
};
