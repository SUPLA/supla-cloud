import Vue from 'vue'
import Vuex from 'vuex'
import ScheduleForm from './schedule-form.vue'
import '../translations'
import {mutations} from './mutations'

Vue.use(Vuex);

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
