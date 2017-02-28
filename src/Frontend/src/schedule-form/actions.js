import Vue from "vue";

export const updateTimeExpression = ({commit, dispatch}, timeExpression) => {
    commit('updateTimeExpression', timeExpression);
    dispatch('fetchNextRunDates');
};

export const updateDateStart = ({commit, dispatch}, date) => {
    commit('updateDateStart', date);
    dispatch('fetchNextRunDates');
};

export const updateDateEnd = ({commit, dispatch}, date) => {
    commit('updateDateEnd', date);
    dispatch('fetchNextRunDates');
};

export const fetchNextRunDates = ({commit, state, dispatch}) => {
    if (!state.fetchingNextRunDates) {
        if (!state.timeExpression) {
            commit('clearNextRunDates');
        } else {
            commit('fetchingNextRunDates');
            let query = {
                timeExpression: state.timeExpression,
                dateStart: state.dateStart,
                dateEnd: state.dateEnd
            };
            Vue.http.post('schedule/next-run-dates', query)
                .then(({body}) => {
                    commit('updateNextRunDates', body.nextRunDates);
                    if (query.timeExpression != state.timeExpression || query.dateStart != state.dateStart || query.dateEnd != state.dateEnd) {
                        dispatch('fetchNextRunDates');
                    }
                })
                .catch(() => commit('updateNextRunDates', []))
                .finally(() => commit('fetchingNextRunDates', false));

        }
    }
};

export const submit = ({commit, state}) => {
    commit('submit');
    let promise;
    if (state.scheduleId) {
        promise = Vue.http.put(`schedule/${state.scheduleId}`, state);
    } else {
        promise = Vue.http.post('schedule/create', state);
    }
    promise
        .then(({body: schedule}) => window.location.href = Vue.http.options.root + '/schedule/' + schedule.id)
        .catch(() => commit('submitFailed'));
};

export const loadScheduleToEdit = ({commit, dispatch}, schedule) => {
    commit('editSchedule', schedule);
    dispatch('fetchNextRunDates')
};
