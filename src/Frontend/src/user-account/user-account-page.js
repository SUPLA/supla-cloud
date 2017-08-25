import Vue from "vue";
import TimezonePicker from "./timezone-picker.vue";
import {i18n} from "../translations";

new Vue({
    el: '#user-account',
    i18n,
    components: {TimezonePicker}
});
