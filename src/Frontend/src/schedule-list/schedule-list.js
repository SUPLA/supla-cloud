import Vue from "vue";
import ScheduleList from "./schedule-list.vue";
import Vuetable from "vuetable-2/src/components/Vuetable.vue";
import VuetablePagination from "vuetable-2/src/components/VuetablePagination.vue";
import VuetablePaginationDropdown from "vuetable-2/src/components/VuetablePaginationDropdown.vue";

Vue.component('vuetable', Vuetable);
Vue.component('vuetable-pagination', VuetablePagination)
Vue.component('vuetable-pagination-dropdown', VuetablePaginationDropdown)

new Vue({
    el: '#schedule-list',
    render: h => h(ScheduleList)
})
