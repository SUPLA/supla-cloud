import Vue from "vue";

export const updateTimeExpression = ({commit, dispatch}, timeExpression) => {
    commit('updateTimeExpression', timeExpression);
    dispatch('fetchNextRunDates');
};

export const fetchNextRunDates = ({commit, state, dispatch}) => {
    if (!state.fetchingNextRunDates) {
        commit('fetchingNextRunDates');
        let query = {
            timeExpression: state.timeExpression,
            dateStart: '',
            dateEnd: ''
        };
        Vue.http.post('schedule/next-run-dates', query).then(({body}) => {
            commit('updateNextRunDates', body.nextRunDates);
            commit('fetchingNextRunDates', false);
            if (query.timeExpression != state.timeExpression) {
                dispatch('fetchNextRunDates');
            }
        });
    }
}
