import Vue from 'vue'
import Vuex from 'vuex'
import ScheduleForm from './schedule-form.vue'
import * as mutations from './mutations'
import * as actions from './actions'

new Vue({
    el: '#schedule-form',
    store: new Vuex.Store({
        state: {
            scheduleMode: 'once',
            timeExpression: '',
            dateStart: moment().format(),
            dateEnd: '',
            fetchingNextRunDates: false,
            nextRunDates: [],
            channel: undefined,
            action: undefined,
            actionParam: undefined,
            submitting: false
        },
        mutations: mutations,
        actions: actions,
        strict: process.env.NODE_ENV !== 'production'
    }),
    render: h => h(ScheduleForm)
})
