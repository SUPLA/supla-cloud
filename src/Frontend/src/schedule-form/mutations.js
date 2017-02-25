export const changeScheduleMode = (state, newScheduleMode) => {
    state.scheduleMode = newScheduleMode;
    state.nextRunDates = [];
    state.timeExpression = '';
};

export const updateTimeExpression = (state, timeExpression) => {
    state.timeExpression = timeExpression;
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
