import Vue from 'vue'
import Vuex from 'vuex'
import ScheduleForm from './schedule-form.vue'
import {mutations} from './mutations'

new Vue({
    el: '#schedule-form',
    store: new Vuex.Store({
        state: {
            scheduleMode: 'once'
        },
        mutations: mutations
    }),
    render: h => h(ScheduleForm)
})
