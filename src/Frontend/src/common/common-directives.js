import Vue from "vue";

Vue.directive('fade-out-link', {
    inserted: function (el) {
        $(el).click(() => $('.main-content').fadeOut(300));
    }
})
