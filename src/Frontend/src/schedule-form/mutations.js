export const mutations = {
    changeScheduleMode (state, newScheduleMode) {
        state.scheduleMode = newScheduleMode;
    },
    updateTimeExpression(state, timeExpression) {
        state.timeExpression = timeExpression;
    }
}
