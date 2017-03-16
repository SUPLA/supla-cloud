import Vue from "vue";
import ScheduleList from "./schedule-list.vue";
import Vuetable from "vuetable-2/src/components/Vuetable.vue";

Vue.component('vuetable', Vuetable);

new Vue({
    el: '#schedule-list',
    render: h => h(ScheduleList)
})
