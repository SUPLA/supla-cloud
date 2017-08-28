import Vue from "vue";
import {i18n} from "../translations";
const IdleLogout = () => import("./idle-logout.vue");

new Vue({
    el: '.main-content',
    i18n,
    components: {IdleLogout}
});
