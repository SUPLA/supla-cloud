import Vue from "vue";
import Vuex from "vuex";
import ScheduleForm from "./schedule-form.vue";
import {actions, mutations} from "./schedule-form-store";

new Vue({
    el: '#schedule-form',
    store: new Vuex.Store({
        state: {
            caption: '',
            scheduleMode: 'once',
            timeExpression: '',
            dateStart: moment().format(),
            dateEnd: '',
            fetchingNextRunDates: false,
            nextRunDates: [],
            channel: undefined,
            action: undefined,
            actionParam: undefined,
            submitting: false,
            scheduleId: +($('#schedule-form').attr('schedule-id') || 0) || undefined,
            schedule: undefined,
        },
        mutations: mutations,
        actions: actions,
        strict: process.env.NODE_ENV !== 'production'
    }),
    render: h => h(ScheduleForm)
});
