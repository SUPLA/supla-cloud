export const changeScheduleMode = (state, newScheduleMode) => {
    state.scheduleMode = newScheduleMode;
    state.nextRunDates = [];
    state.timeExpression = '';
};

export const updateTimeExpression = (state, timeExpression) => {
    state.timeExpression = timeExpression;
};

export const updateDateStart = (state, date) => {
    state.dateStart = date ? date.format() : '';
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
