import Vue from "vue";
import {i18n} from "../translations";
import IdleLogout from "./idle-logout.vue";

new Vue({
    el: '.main-content',
    i18n,
    components: {IdleLogout}
});
