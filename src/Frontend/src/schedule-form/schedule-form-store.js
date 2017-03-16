import Vue from "vue";

export const mutations = {
    changeScheduleMode (state, newScheduleMode) {
        state.scheduleMode = newScheduleMode;
        state.nextRunDates = [];
        state.timeExpression = '';
    },
    updateTimeExpression(state, timeExpression) {
        state.timeExpression = timeExpression;
    },
    updateCaption(state, caption) {
        state.caption = caption;
    },
    updateDateStart(state, date) {
        state.dateStart = date ? date.format() : moment().format();
    },
    updateDateEnd(state, date) {
        state.dateEnd = date ? date.format() : '';
    },
    fetchingNextRunDates(state) {
        state.fetchingNextRunDates = true;
    },
    updateNextRunDates(state, nextRunDates) {
        state.nextRunDates = nextRunDates;
        state.fetchingNextRunDates = false;
    },
    clearNextRunDates(state) {
        state.nextRunDates = [];
    },
    updateChannel  (state, channel) {
        state.channel = channel;
        state.action = undefined;
        state.actionParam = undefined;
    },
    updateAction  (state, action) {
        state.action = action;
        state.actionParam = undefined;
    },
    updateActionParam  (state, actionParam) {
        state.actionParam = actionParam;
    },
    submit (state) {
        state.submitting = true;
    },
    submitFailed(state) {
        state.submitting = false;
    },
    editSchedule(state, schedule) {
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
    }
};

export const actions = {
    updateTimeExpression({commit, dispatch}, timeExpression) {
        commit('updateTimeExpression', timeExpression);
        dispatch('fetchNextRunDates');
    },

    updateDateStart({commit, dispatch}, date) {
        commit('updateDateStart', date);
        dispatch('fetchNextRunDates');
    },

    updateDateEnd({commit, dispatch}, date) {
        commit('updateDateEnd', date);
        dispatch('fetchNextRunDates');
    },

    fetchNextRunDates({commit, state, dispatch}) {
        if (!state.fetchingNextRunDates) {
            if (!state.timeExpression) {
                commit('clearNextRunDates');
            } else {
                commit('fetchingNextRunDates');
                let query = {
                    scheduleMode: state.scheduleMode,
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
                    .catch(() => commit('updateNextRunDates', []));

            }
        }
    },

    submit({commit, state}) {
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
    },

    loadScheduleToEdit({commit, dispatch}, schedule) {
        commit('editSchedule', schedule);
        dispatch('fetchNextRunDates')
    },
};
