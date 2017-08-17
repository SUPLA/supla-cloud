import Vue from "vue";
import ScheduleList from "./schedule-list.vue";
import Vuetable from "vuetable-2/src/components/Vuetable.vue";
import {i18n} from "../../translations";

Vue.component('vuetable', Vuetable);

new Vue({
    el: '#schedule-list',
    i18n,
    render: h => h(ScheduleList)
});
